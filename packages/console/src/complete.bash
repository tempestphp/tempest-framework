bind 'set show-all-if-ambiguous on' 2>/dev/null

_tempest() {
    local cur cmd requestComp out comp i w
    local -a words
    COMPREPLY=()

    cur="${COMP_LINE:0:$COMP_POINT}" cur="${cur##* }"
    cmd="${COMP_WORDS[0]}"

    read -ra words <<< "${COMP_LINE:0:$COMP_POINT}"
    [[ "${COMP_LINE:$((COMP_POINT-1)):1}" == " " ]] && words+=("")
    local wordCount=${#words[@]}

    if [[ "$cmd" == "php" ]]; then
        [[ "${words[1]:-}" != "tempest" ]] && return
        requestComp="php tempest _complete --current=$((wordCount-2))"
        words=("${words[@]:1}")
    elif [[ "$cmd" == "./tempest" || "$cmd" == *"/tempest" ]]; then
        requestComp="$cmd _complete --current=$((wordCount-1))"
    else
        return
    fi

    for w in "${words[@]}"; do
        [[ -n "$w" ]] && i="${i}--input=\"${w}\" "
    done

    out=$(eval "$requestComp ${i:---input=\" \"}" 2>/dev/null)

    while IFS= read -r comp; do
        [[ -z "$comp" ]] && continue
        comp="${comp%%	*}"
        if [[ -z "$cur" ]]; then
            COMPREPLY+=("$comp")
        elif [[ "$cur" == *":"* ]]; then
            [[ "$comp" == "${cur%:*}:"* ]] && COMPREPLY+=("${comp#"${cur%:*}:"}")
        else
            [[ "$comp" == "$cur"* ]] && COMPREPLY+=("$comp")
        fi
    done <<< "$out"
}

complete -F _tempest ./tempest php
