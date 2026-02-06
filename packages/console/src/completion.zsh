#compdef -p '*/tempest' -p 'tempest' php

# Tempest Framework Zsh Completion

_tempest() {
    local current="${words[CURRENT]}" tempest_cmd shift_count output
    local -a args with_suffix without_suffix

    # Detect invocation: "php tempest ..." vs "./tempest ..."
    if [[ "${words[1]}" == "php" ]]; then
        tempest_cmd="${words[1]} ${words[2]}"
        shift_count=2
    else
        tempest_cmd="${words[1]}"
        shift_count=1
    fi

    # Build completion request arguments
    # Skip "php" from inputs but keep the tempest binary and args
    local skip=$((shift_count - 1))
    args=("--current=$((CURRENT - shift_count))")
    for word in "${words[@]:$skip}"; do
        args+=("--input=$word")
    done

    # Fetch completions from tempest
    output=$(eval "$tempest_cmd _complete ${args[*]}" 2>/dev/null) || return 0
    [[ -z "$output" ]] && return 0

    # Parse completions, separating by suffix behavior
    for line in "${(@f)output}"; do
        [[ -z "$line" ]] && continue
        if [[ "$line" == *= ]]; then
            without_suffix+=("$line")
        else
            with_suffix+=("${line//:/\\:}")
        fi
    done

    # Add completions: no trailing space for "=" options, use _describe for commands
    (( ${#without_suffix} )) && compadd -Q -S '' -- "${without_suffix[@]}"

    if (( ${#with_suffix} )); then
        if [[ "$current" == -* || "${with_suffix[1]}" == -* ]]; then
            compadd -Q -- "${with_suffix[@]}"
        else
            _describe -t commands 'tempest commands' with_suffix
        fi
    fi
}

compdef _tempest -p '*/tempest'
compdef _tempest -p 'tempest'
compdef _tempest php
