#!/bin/bash

# This script allows us to dynamically retrieve the package namespace
# and directory from our composer file. This is then used by our GitHub
# action to publish the packages to their appropriate repositories and
# run tests.

SCRIPT_DIR="$(realpath "$(dirname "${BASH_SOURCE[0]}")")"
PACKAGES_DIRECTORY="$SCRIPT_DIR/../src/Tempest"

get_package_name_from_composer_file() {
    composer_file="$1"
    if [ ! -f "$composer_file" ]; then
        echo "Composer file not found: $composer_file" >&2
        exit 1
    fi

    name=$(jq -r '.name // empty' "$composer_file")

    if [ -z "$name" ]; then
        echo "The referenced package is invalid because it is missing a name: $composer_file" >&2
        exit 1
    fi

    echo "${name#tempest/}"
}

get_packages() {
    packages=()

    for directory in "$PACKAGES_DIRECTORY"/*/; do
        composer_file="$directory/composer.json"

        if [ ! -f "$composer_file" ]; then
            continue
        fi

        package_name=$(get_package_name_from_composer_file "$composer_file")
        packages+=("{\"directory\":\"$directory\",\"name\":\"$package_name\",\"package\":\"tempest/$package_name\",\"organization\":\"tempestphp\",\"repository\":\"tempest-$package_name\"}")
    done

    package_string=$(IFS=,; echo "${packages[*]}")

    echo "[$package_string]"
}

get_packages_with_tests() {
    packages=()

    for directory in "$PACKAGES_DIRECTORY"/*/; do
        composer_file="$directory/composer.json"
        test_file="$directory/phpunit.xml"
        test_dir="$directory/tests"

        if [ ! -f "$composer_file" ] || [ ! -f "$test_file" ] || [ ! -d "$test_dir" ]; then
            continue
        fi

        package_name=$(get_package_name_from_composer_file "$composer_file")
        packages+=("{\"directory\":\"$directory\",\"name\":\"$package_name\",\"package\":\"tempest/$package_name\",\"organization\":\"tempestphp\",\"repository\":\"tempest-$package_name\"}")
    done

    package_string=$(IFS=,; echo "${packages[*]}")

    echo "[$package_string]"
}