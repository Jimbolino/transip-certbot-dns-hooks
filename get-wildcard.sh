#!/usr/bin/env bash

# https://stackoverflow.com/a/246128/3432720
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

if [[ "$1" == "" ]]; then
    echo Run like this: "$0" domain.com
    exit 1
fi

certbot certonly --manual \
--preferred-challenges=dns \
--manual-auth-hook ${DIR}/auth-hook.php \
--manual-cleanup-hook ${DIR}/cleanup-hook.php \
-d "$1" -d "*.$1"
