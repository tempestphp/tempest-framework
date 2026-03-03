function __tempest_project_directory
    set -l command $argv[1]
    set -l dir (command dirname -- "$command")

    if string match -q 'vendor/bin/tempest' -- "$command"
        or string match -q '*/vendor/bin/tempest' -- "$command"
        set dir "$dir/../.."
    end

    realpath -- "$dir" 2>/dev/null
end

function __tempest_is_tempest_command
    set -l tokens (commandline -xpc)

    test (count $tokens) -gt 0
    or return 1

    set -l command_name (command basename -- "$tokens[1]")

    if test "$command_name" = php
        test (count $tokens) -ge 2
        or return 1

        set command_name (command basename -- "$tokens[2]")
    end

    test "$command_name" = tempest
end

function __tempest_completions
    set -l tokens (commandline -xpc)
    set -l current_token (commandline -ct)

    test (count $tokens) -gt 0
    or return

    set -l command "$tokens[1]"

    if test (command basename -- "$command") = php
        test (count $tokens) -ge 2
        or return

        set command "$tokens[2]"
    end

    set -l project_directory (__tempest_project_directory "$command")
    or return

    set -l helper "$project_directory/vendor/bin/tempest-complete"
    set -l metadata "$project_directory/.tempest/completion/commands.json"

    test -x "$helper"
    or return

    test -f "$metadata"
    or return

    if test -z "$current_token"
        set -a tokens ''
    else
        set -a tokens "$current_token"
    end

    set -l current_index (math (count $tokens) - 1)

    set -l output ($helper "$metadata" "$current_index" $tokens 2>/dev/null)
    or return

    set -l tab (printf '\t')

    for line in $output
        test -n "$line"
        or continue

        if string match -q "*$tab*" -- "$line"
            set -l parts (string split -m 1 "$tab" -- "$line")
            set -l value "$parts[1]"
            set -l description "$parts[2]"

            if test -n "$value"
                set -l escaped_value (string escape --style=regex -- "$value")
                set -l duplicate_match_pattern (string join '' '^' "$escaped_value" '[[:space:]][[:space:]]+')
                set -l duplicate_replace_pattern (string join '' '^' "$escaped_value" '[[:space:]]+')

                if string match -rq "$duplicate_match_pattern" -- "$description"
                    set description (string replace -r "$duplicate_replace_pattern" '' -- "$description")
                end

                echo -- "$value$tab$description"
                continue
            end
        end

        echo -- "$line"
    end
end

complete -c tempest -f -a '(__tempest_completions)'
complete -c php -n '__tempest_is_tempest_command' -f -a '(__tempest_completions)'
