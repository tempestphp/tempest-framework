use std::collections::{BTreeMap, HashSet};
use std::fs;
use std::io::Write;
use std::path::Path;

use serde::Deserialize;

#[derive(Deserialize)]
struct CompletionMetadata {
    #[serde(default)]
    commands: BTreeMap<String, CommandMetadata>,
}

#[derive(Deserialize)]
struct CommandMetadata {
    #[serde(default)]
    hidden: bool,
    #[serde(default)]
    description: Option<String>,
    #[serde(default)]
    flags: Vec<FlagMetadata>,
}

#[derive(Deserialize)]
struct FlagMetadata {
    name: String,
    flag: String,
    #[serde(default)]
    aliases: Vec<String>,
    #[serde(default)]
    description: Option<String>,
    #[serde(default)]
    value_options: Vec<String>,
    repeatable: bool,
}

struct Completion {
    value: String,
    display: Option<String>,
}

impl Completion {
    fn plain(value: String) -> Self {
        Self {
            value,
            display: None,
        }
    }

    fn with_display(value: String, display: String) -> Self {
        Self {
            value,
            display: Some(display),
        }
    }

    fn into_output(self) -> String {
        match self.display {
            Some(display) => format!("{}\t{}", self.value, display),
            None => self.value,
        }
    }
}

fn main() {
    let mut args = std::env::args().skip(1);

    let Some(metadata_path) = args.next() else {
        return;
    };

    let Some(current_index) = args
        .next()
        .and_then(|current| current.parse::<usize>().ok())
    else {
        return;
    };

    let words = args.collect::<Vec<String>>();

    let Some((normalized_words, normalized_index)) = normalize_words(words, current_index) else {
        return;
    };

    let Ok(content) = fs::read_to_string(metadata_path) else {
        return;
    };

    let Ok(metadata) = serde_json::from_str::<CompletionMetadata>(&content) else {
        return;
    };

    let completions = complete(&metadata, &normalized_words, normalized_index);

    if completions.is_empty() {
        return;
    }

    let stdout = std::io::stdout();
    let mut out = stdout.lock();

    for (i, item) in completions.into_iter().enumerate() {
        if i > 0 {
            let _ = out.write_all(b"\n");
        }
        let _ = out.write_all(item.into_output().as_bytes());
    }
}

fn normalize_words(
    mut words: Vec<String>,
    mut current_index: usize,
) -> Option<(Vec<String>, usize)> {
    if words.is_empty() {
        return None;
    }

    if is_php_binary(&words[0]) {
        if words.len() < 2 || !is_tempest_invocation(&words[1]) {
            return None;
        }

        words.remove(0);
        current_index = current_index.saturating_sub(1);
    } else if !is_tempest_invocation(&words[0]) {
        return None;
    }

    if words.is_empty() {
        return None;
    }

    if current_index >= words.len() {
        words.push(String::new());
    }

    current_index = current_index.min(words.len().saturating_sub(1));

    Some((words, current_index))
}

fn complete(
    metadata: &CompletionMetadata,
    words: &[String],
    current_index: usize,
) -> Vec<Completion> {
    if words.is_empty() {
        return Vec::new();
    }

    let current = words.get(current_index).map(|s| s.as_str()).unwrap_or("");

    if current_index <= 1 {
        return complete_commands(metadata, current);
    }

    let command_name = &words[1];

    if command_name.starts_with('-') {
        return complete_commands(metadata, current);
    }

    let Some(command) = metadata.commands.get(command_name.as_str()) else {
        return Vec::new();
    };

    complete_flags(command, words, current_index, current)
}

fn complete_commands(metadata: &CompletionMetadata, current: &str) -> Vec<Completion> {
    if current.starts_with('-') {
        return Vec::new();
    }

    let mut max_name_length = 0;

    let completions: Vec<(&str, Option<String>)> = metadata
        .commands
        .iter()
        .filter(|(_, command)| !command.hidden)
        .filter(|(name, _)| name.starts_with(current))
        .map(|(name, command)| {
            max_name_length = max_name_length.max(name.len());
            (
                name.as_str(),
                sanitize_description(command.description.as_deref()),
            )
        })
        .collect();

    completions
        .into_iter()
        .map(|(name, description)| match description {
            Some(description) => Completion::with_display(
                name.to_owned(),
                format!("{:<width$}  {}", name, description, width = max_name_length),
            ),
            None => Completion::plain(name.to_owned()),
        })
        .collect()
}

fn complete_flags(
    command: &CommandMetadata,
    words: &[String],
    current_index: usize,
    current: &str,
) -> Vec<Completion> {
    if !current.is_empty() && !current.starts_with('-') {
        return Vec::new();
    }

    let used_flags = collect_used_flags(command, words, current_index);

    let mut max_label_length = 0;

    let completions: Vec<(String, String, Option<String>)> = command
        .flags
        .iter()
        .filter(|flag| flag.repeatable || !used_flags.contains(flag.name.as_str()))
        .filter_map(|flag| {
            let value = select_completion_value(flag, current)?;
            let label = build_flag_label(flag);
            let description = sanitize_description(flag.description.as_deref());
            max_label_length = max_label_length.max(label.len());
            Some((value, label, description))
        })
        .collect();

    completions
        .into_iter()
        .map(|(value, label, description)| {
            let display = match description {
                Some(description) => {
                    format!(
                        "{:<width$}  {}",
                        label,
                        description,
                        width = max_label_length,
                    )
                }
                None => label,
            };

            Completion::with_display(value, display)
        })
        .collect()
}

fn select_completion_value(flag: &FlagMetadata, current: &str) -> Option<String> {
    let matches = |c: &&str| c.starts_with(current);

    let result = if current.starts_with("--") {
        std::iter::once(flag.flag.as_str())
            .chain(
                flag.aliases
                    .iter()
                    .map(String::as_str)
                    .filter(|alias| alias.starts_with("--")),
            )
            .find(matches)
    } else if current.starts_with('-') {
        flag.aliases
            .iter()
            .map(String::as_str)
            .chain(std::iter::once(flag.flag.as_str()))
            .find(matches)
    } else {
        Some(flag.flag.as_str()).filter(matches)
    };

    result.map(ToOwned::to_owned)
}

fn build_flag_label(flag: &FlagMetadata) -> String {
    let mut label = flag.flag.clone();

    if flag.flag.ends_with('=') && !flag.value_options.is_empty() {
        label.push('<');
        label.push_str(&flag.value_options.join(","));
        label.push('>');
    }

    if !flag.aliases.is_empty() {
        label.push_str(" / ");
        label.push_str(&flag.aliases.join(" / "));
    }

    label
}

fn sanitize_description(description: Option<&str>) -> Option<String> {
    description.and_then(|description| {
        let mut words = description.split_whitespace();
        let first = words.next()?;

        let normalized = words.fold(first.to_owned(), |mut acc, word| {
            acc.push(' ');
            acc.push_str(word);
            acc
        });

        Some(normalized)
    })
}

fn collect_used_flags<'a>(
    command: &'a CommandMetadata,
    words: &[String],
    current_index: usize,
) -> HashSet<&'a str> {
    let mut used = HashSet::new();

    for (index, word) in words.iter().enumerate().skip(2) {
        if index == current_index {
            continue;
        }

        if word.starts_with("--") {
            if let Some(name) = normalize_long_flag(word)
                && let Some(flag_name) = resolve_flag_name(command, name)
            {
                used.insert(flag_name);
            }
        } else if word.starts_with('-') {
            let short_value = word
                .split_once('=')
                .map(|(name, _)| name)
                .unwrap_or(word)
                .trim_start_matches('-');

            if short_value.len() == 1 {
                if let Some(flag_name) = resolve_flag_name(command, short_value) {
                    used.insert(flag_name);
                }
            } else {
                for part in short_value.chars() {
                    let s = part.to_string();
                    if let Some(flag_name) = resolve_flag_name(command, &s) {
                        used.insert(flag_name);
                    }
                }
            }
        }
    }

    used
}

fn normalize_long_flag(value: &str) -> Option<&str> {
    let mut normalized = value.trim_start_matches("--");

    if let Some((name, _)) = normalized.split_once('=') {
        normalized = name;
    }

    if let Some(stripped) = normalized.strip_prefix("no-") {
        normalized = stripped;
    }

    if normalized.is_empty() {
        return None;
    }

    Some(normalized)
}

fn resolve_flag_name<'a>(command: &'a CommandMetadata, value: &str) -> Option<&'a str> {
    command
        .flags
        .iter()
        .find(|flag| {
            flag.name == value
                || flag
                    .aliases
                    .iter()
                    .any(|alias| alias.trim_start_matches('-') == value)
        })
        .map(|flag| flag.name.as_str())
}

fn is_php_binary(value: &str) -> bool {
    basename(value) == "php"
}

fn is_tempest_invocation(value: &str) -> bool {
    basename(value) == "tempest"
}

fn basename(value: &str) -> &str {
    Path::new(value)
        .file_name()
        .and_then(|name| name.to_str())
        .unwrap_or(value)
}
