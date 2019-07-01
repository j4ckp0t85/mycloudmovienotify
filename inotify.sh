#!/bin/sh
### BEGIN INIT INFO
# Provides:          filenotifier
# Required-Start:    $remote_fs $syslog
# Required-Stop:     $remote_fs $syslog
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: Something
# Description:       Something else
### END INIT INFO

inotifywait -m --recursive --excludei "[^mk].$|[^v]$" /mnt/shares/watched_dir -e create -e moved_to | while read path action file; do php5 tmdb.php "$file" $path; mutt -e "set content_type=text/html" -s "WD seedbox sync alert" -F /home/root/.muttrc -- "dest_email@gmail.com, dest2_email@gmail.com" < film.html ;done