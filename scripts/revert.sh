#!/usr/bin/env bash

#
# Revert back to old config
#

#
# Get current directory
# @source https://stackoverflow.com/questions/59895/getting-the-source-directory-of-a-bash-script-from-within
#
function current_dir(){
    local SOURCE="${BASH_SOURCE[0]}"
    while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
      local DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
      SOURCE="$(readlink "$SOURCE")"
      [[ ${SOURCE} != /* ]] && SOURCE="$DIR/$SOURCE" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
    done
    DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
    echo ${DIR}
}

#
# Main Program begins
#

#
# Configurations
#
DIR=$(current_dir)

#
# Switch back to the private configurations
#
mv -f "${DIR}/../PrivateConfig.php" "${DIR}/../Config.php"

#
# add to cron tab
#
bash "${DIR}/add_to_crontab.sh"
