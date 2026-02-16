_tempest_project_directory() {
    local command="$1"
    local command_directory

    command_directory="$(dirname "$command")"

    if [[ "$command" == "vendor/bin/tempest" || "$command" == */vendor/bin/tempest ]]; then
        cd "$command_directory/../.." 2>/dev/null && pwd -P
        return
    fi

    cd "$command_directory" 2>/dev/null && pwd -P
}

COMP_WORDBREAKS="${COMP_WORDBREAKS//:}"

_tempest_php_passthrough() {
    local fallback_function

    [[ -n "${__tempest_php_original_completion:-}" ]] || return 1

    if [[ "$__tempest_php_original_completion" =~ -F[[:space:]]+([^[:space:]]+) ]]; then
        fallback_function="${BASH_REMATCH[1]}"

        if [[ "$fallback_function" != "_tempest" ]] && declare -F "$fallback_function" >/dev/null 2>&1; then
            "$fallback_function"
            return $?
        fi
    fi

    return 1
}

_tempest_has_compopt() {
    type compopt >/dev/null 2>&1
}

_tempest_disable_default_completion() {
    _tempest_has_compopt || return 0

    compopt +o default +o bashdefault
}

_tempest_enable_nospace_for_assignment_candidates() {
    local candidate

    _tempest_has_compopt || return 0

    for candidate in "${COMPREPLY[@]}"; do
        if [[ "$candidate" == *= ]]; then
            compopt -o nospace
            break
        fi
    done
}

_tempest_resolve_command() {
    local -a line_words=("$@")
    local command command_name passthrough_status

    if [[ "${line_words[0]}" == "php" ]]; then
        if (( ${#line_words[@]} < 2 )) || [[ -z "${line_words[1]}" ]]; then
            _tempest_php_passthrough
            passthrough_status=$?
            REPLY="$passthrough_status"
            return 3
        fi

        command="${line_words[1]}"
        command_name="${command##*/}"

        if [[ "$command_name" != "tempest" ]]; then
            _tempest_php_passthrough
            passthrough_status=$?
            REPLY="$passthrough_status"
            return 3
        fi
    else
        command="${line_words[0]}"
        command_name="${command##*/}"
    fi

    [[ "$command_name" == "tempest" ]] || return 2

    REPLY="$command"
    return 0
}

_tempest_segment_prefix() {
    local current_word="$1"
    local current_segment="$2"

    REPLY=''

    # COMP_WORDS may point at only the segment being completed around '=' or ':'.
    if [[ "$current_word" == *[=:]* ]] && [[ "$current_segment" != "$current_word" ]]; then
        if [[ -z "$current_segment" || "$current_segment" == "=" ]]; then
            REPLY="$current_word"
        elif [[ -n "$current_segment" ]]; then
            REPLY="${current_word%"$current_segment"}"
        fi
    fi
}

_tempest_collect_candidates() {
    local output="$1"
    local segment_prefix="$2"
    local candidate tab

    COMPREPLY=()

    tab=$'\t'

    while IFS= read -r candidate; do
        if [[ "$candidate" == *"$tab"* ]]; then
            candidate="${candidate%%$tab*}"
        fi

        if [[ -n "$segment_prefix" ]] && [[ "$candidate" == "$segment_prefix"* ]]; then
            candidate="${candidate#$segment_prefix}"
        fi

        [[ -z "$candidate" ]] && continue
        COMPREPLY+=("$candidate")
    done <<< "$output"
}

_tempest() {
    local command project_directory helper metadata output line_prefix current_index current_word current_segment segment_prefix status
    local -a line_words

    line_prefix="${COMP_LINE:0:COMP_POINT}"
    read -r -a line_words <<< "$line_prefix"

    if [[ "$line_prefix" == *[[:space:]] ]]; then
        line_words+=("")
    fi

    (( ${#line_words[@]} > 0 )) || return 0

    _tempest_resolve_command "${line_words[@]}"
    status=$?

    case $status in
        0)
            command="$REPLY"
            ;;
        3)
            return "$REPLY"
            ;;
        *)
            return 0
            ;;
    esac

    local COMP_WORDBREAKS="${COMP_WORDBREAKS//:}"

    project_directory="$(_tempest_project_directory "$command")" || return 0

    helper="$project_directory/vendor/bin/tempest-complete"
    metadata="$project_directory/.tempest/completion/commands.json"

    [[ -x "$helper" ]] || return 0
    [[ -f "$metadata" ]] || return 0

    _tempest_disable_default_completion

    current_index=$(( ${#line_words[@]} - 1 ))
    current_word="${line_words[$current_index]}"
    current_segment="${COMP_WORDS[COMP_CWORD]:-}"

    _tempest_segment_prefix "$current_word" "$current_segment"
    segment_prefix="$REPLY"

    output="$($helper "$metadata" "$current_index" "${line_words[@]}" 2>/dev/null)" || return 0
    [[ -z "$output" ]] && return 0

    _tempest_collect_candidates "$output" "$segment_prefix"
    _tempest_enable_nospace_for_assignment_candidates
}

if [[ -z "${__tempest_php_original_completion:-}" ]] && complete -p php >/dev/null 2>&1; then
    __tempest_php_original_completion="$(complete -p php 2>/dev/null)"

    if [[ "$__tempest_php_original_completion" == *"-F _tempest"* ]]; then
        __tempest_php_original_completion=''
    fi
fi

complete -o bashdefault -o default -F _tempest tempest
complete -o bashdefault -o default -F _tempest ./tempest
complete -o bashdefault -o default -F _tempest ./vendor/bin/tempest
complete -o bashdefault -o default -F _tempest vendor/bin/tempest
complete -o bashdefault -o default -F _tempest php
