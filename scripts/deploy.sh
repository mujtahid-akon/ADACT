#!/usr/bin/env bash

#
# Running this file will delete every relevant info
# that is needed to be flushed before deployment
#

#
# Get current directory
# @source https://stackoverflow.com/questions/59895/getting-the-source-directory-of-a-bash-script-from-within
#
function current_dir(){
    local SOURCE="${BASH_SOURCE[0]}"
    while [[ -h "$SOURCE" ]]; do # resolve $SOURCE until the file is no longer a symlink
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
MYSQL_DB="adact"
MYSQL_USER="root"
MYSQL_PASS="PASSWORD"

VERSION=$1
DIR=$(current_dir)

#
# Run tests
#
python3 "$DIR/../tests/basic_test.py"
if [[ $? -ne 0 ]]; then
    exit 1
fi
python3 "$DIR/../tests/new_project_test.py"
if [[ $? -ne 0 ]]; then
    exit 1
fi

#
# Remove files & directories
#
find "$DIR/../tmp/" \! -name 'readme.md' -delete 2> /dev/null
find "$DIR/../Projects/" \! -name 'readme.md' -delete 2> /dev/null

#
# Flash mysql data
#
mysql --user=${MYSQL_USER} --password=${MYSQL_PASS} --database=${MYSQL_DB} 2> /dev/null << END
DELETE FROM active_sessions;
DELETE FROM login_attempts;
DELETE FROM pending_projects;
DELETE FROM last_projects;
DELETE FROM projects;
DELETE FROM uploaded_files
END

#
# Dump mysql database
#
if [[ $# -eq 1 ]]; then
    mysqldump --user=${MYSQL_USER} --password=${MYSQL_PASS} ${MYSQL_DB} > "${DIR}/../sql/${MYSQL_DB}_v${VERSION}.sql" 2> /dev/null
fi

#
# Switch to the default configurations
#
mv -f "${DIR}/../Config.php" "${DIR}/../PrivateConfig.php"
cp -f "${DIR}/../DefaultConfig.php" "${DIR}/../Config.php"

#
# Empty log files
#
echo -n > "$DIR/../logs/debug.log"
echo -n > "$DIR/../logs/process.log"

exit 0
