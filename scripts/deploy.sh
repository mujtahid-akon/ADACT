#!/usr/bin/env bash

#
# Running this file will delete every relevant info
# that is needed to be flushed before deployment
#

if [ "$#" -ne 1 ]; then
    echo "SQL version string must be provided as argument!"
    exit 1
fi

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
MYSQL_DB="awords"
MYSQL_USER="root"
MYSQL_PASS="root"

VERSION=$1
DIR=$(current_dir)

#
# Remove files & directories
#
find "$DIR/../tmp/" \! -name 'readme.md' -delete
find "$DIR/../Projects/" \! -name 'readme.md' -delete

#
# Flash mysql data
#
mysql --user=${MYSQL_USER} --password=${MYSQL_PASS} --database=${MYSQL_DB} << END
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
mysqldump --user=${MYSQL_USER} --password=${MYSQL_PASS} ${MYSQL_DB} > "${DIR}/../sql/${MYSQL_DB}_v${VERSION}.sql"

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
