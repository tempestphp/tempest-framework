# Tempest Framework Bash Completion
# Supports: ./tempest, tempest, php tempest, php vendor/bin/tempest, etc.

_tempest() {
    local cur tempest_cmd shift_count input_args output IFS

    # Initialize current word (use bash-completion if available)
    if declare -F _init_completion >/dev/null 2>&1; then
        _init_completion || return
    else
        cur="${COMP_WORDS[COMP_CWORD]}"
    fi

    # Detect invocation pattern and build command
    if [[ "${COMP_WORDS[0]}" == "php" ]]; then
        tempest_cmd="${COMP_WORDS[0]} ${COMP_WORDS[1]}"
        shift_count=2
    else
        tempest_cmd="${COMP_WORDS[0]}"
        shift_count=1
    fi

    # Build _complete arguments, skipping "php" prefix if present
    input_args="--current=$((COMP_CWORD - shift_count + 1))"
    for ((i = shift_count - 1; i < ${#COMP_WORDS[@]}; i++)); do
        input_args+=" --input=\"${COMP_WORDS[i]}\""
    done

    # Execute completion command
    output=$(eval "$tempest_cmd _complete $input_args" 2>/dev/null) || return 0
    [[ -z "$output" ]] && return 0

    # Parse and filter completions
    IFS=$'\n'
    COMPREPLY=($(compgen -W "$output" -- "$cur"))

    # Suppress trailing space for flags expecting values (bash 4.0+)
    if [[ ${#COMPREPLY[@]} -eq 1 && "${COMPREPLY[0]}" == *= ]] && type compopt &>/dev/null; then
        compopt -o nospace
    fi
}

complete -F _tempest ./tempest
complete -F _tempest tempest
complete -F _tempest php
