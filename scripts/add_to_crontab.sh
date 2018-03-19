#!/usr/bin/env bash

#
# Add user manually to the /etc/cron.allow (Linux)
# or /usr/lib/cron/cron.allow (Mac)
#
file="/tmp/scheduler_list"

if [ $# -eq 1 ]; then
    user=$1
else
    user=${USER}
fi

echo -n "Remember to add \"${user}\" manually to the "
if [ $(uname -s) == "Linux" ]; then
    echo "/etc/cron.allow"
else
    echo "/usr/lib/cron/cron.allow"
fi

#
# Get cron tab lists
#
sudo -u ${user} crontab -l 1>"${file}" 2>/dev/null
lists=$(cat "${file}")

#
# Add scheduler to it
#
scheduler="* * * * * php \"${PWD}\"/exec/scheduler.php"

#
# Check if scheduler already exists
#
if [[ ${lists} == *${scheduler}* ]]; then
  echo "Scheduler is already in the Cron Job list. Nothing to do."
  exit 0
fi

#
# Remove all the cron tabs
#
sudo -u ${user} crontab -r 2>/dev/null

#
# Append to the current list
#

echo "${scheduler}" >> ${file}

sudo -u ${user} crontab ${file}

echo "Scheduler is added to the Cron Job list"
exit 0;
