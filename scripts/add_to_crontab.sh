#!/usr/bin/env bash

#
# Get cron tab lists
#
lists=$(crontab -l)

if [ $? -ne '0' ]; then
    lists=""
else
    #
    # Remove all the cron tabs
    #
    crontab -r
fi

#
# Add scheduler to it
#
scheduler="* * * * * php ${PWD}/exec/scheduler.php"

#
# Check if scheduler already exists
#
if [[ ${lists} == *${scheduler}* ]]; then
  echo "Scheduler is already in the Cron Job list. Nothing to do."
  exit 0
fi

#
# Append to the current list
#
lists="${scheduler}\n${lists}"

#
# Save to temporary file
#
file="/tmp/tmp_scheduler.txt"

echo -e "${lists}" > ${file}

crontab ${file}

echo "Scheduler is added to the Cron Job list"
exit 0;