_tempest() {
    local lastParam flagPrefix requestComp out comp
    local -a completions

    words=("${=words[1,CURRENT]}") lastParam=${words[-1]}

    setopt local_options BASH_REMATCH
    if [[ "${lastParam}" =~ '-.*=' ]]; then
        flagPrefix="-P ${BASH_REMATCH}"
    fi

    local startIndex=1 cmd="${words[1]}"
    if [[ "$cmd" == "php" ]]; then
        [[ "${words[2]}" != "tempest" ]] && return
        requestComp="php tempest _complete --current=$((CURRENT-2))"
        startIndex=2
    elif [[ "$cmd" == "./tempest" || "$cmd" == *"/tempest" ]]; then
        requestComp="$cmd _complete --current=$((CURRENT-1))"
    else
        return
    fi

    local i=""
    for w in ${words[@]:$startIndex}; do
        w=$(printf -- '%b' "$w")
        quote="${w:0:1}"
        if [ "$quote" = \' ]; then
            w="${w%\'}"
            w="${w#\'}"
        elif [ "$quote" = \" ]; then
            w="${w%\"}"
            w="${w#\"}"
        fi
        if [ -n "$w" ]; then
            i="${i}--input=\"${w}\" "
        fi
    done

    if [ -z "${i}" ]; then
        requestComp="${requestComp} --input=\" \""
    else
        requestComp="${requestComp} ${i}"
    fi

    out=$(eval ${requestComp} 2>/dev/null)

    while IFS='\n' read -r comp; do
        if [ -n "$comp" ]; then
            comp=${comp//:/\\:}
            local tab=$(printf '\t')
            comp=${comp//$tab/:}
            completions+=${comp}
        fi
    done < <(printf "%s\n" "${out[@]}")

    eval _describe "completions" completions $flagPrefix
    return $?
}

compdef _tempest -p '*/tempest'
compdef _tempest -p 'tempest'
compdef _tempest php
