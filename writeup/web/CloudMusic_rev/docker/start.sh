#!/bin/bash

sleep 1

pkill apache2

docker-php-ext-install ffi

chmod 600 /flag
chmod 700 /start.sh
chown -R root:root /var/www/html
chmod -R 775 /var/www/html
chown www-data:www-data /var/www/html/uploads/firmware
chown www-data:www-data /var/www/html/uploads/music
mkdir /var/www/html/config
chmod 777 /var/www/html/config
chmod +t /var/www/html/config
chown root:root /var/www/html/config
chmod +s /usr/bin/tac

sed -i 's/Options Indexes FollowSymLinks/Options None/' /etc/apache2/apache2.conf
cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini
echo 'date.timezone = PRC' >> /usr/local/etc/php/php.ini
echo 'session.upload_progress.enabled = Off' >> /usr/local/etc/php/php.ini
echo 'allow_url_fopen = Off' >> /usr/local/etc/php/php.ini
echo 'open_basedir = /var/www/html:/tmp' >> /usr/local/etc/php/php.ini
echo 'ffi.enable = true' >> /usr/local/etc/php/php.ini
echo 'display_errors = Off' >> /usr/local/etc/php/php.ini
echo 'disable_functions = pcntl_alarm,pcntl_fork,pcntl_waitpid,pcntl_wait,pcntl_wifexited,pcntl_wifstopped,pcntl_wifsignaled,pcntl_wifcontinued,pcntl_wexitstatus,pcntl_wtermsig,pcntl_wstopsig,pcntl_signal,pcntl_signal_get_handler,pcntl_signal_dispatch,pcntl_get_last_error,pcntl_strerror,pcntl_sigprocmask,pcntl_sigwaitinfo,pcntl_sigtimedwait,pcntl_exec,pcntl_getpriority,pcntl_setpriority,pcntl_async_signals,dl,exec,system,passthru,popen,proc_open,shell_exec,mail,imap_open,imap_mail,getenv,setenv,putenv,apache_setenv,symlink,link,popepassthru,syslog,readlink,openlog,ini_restore,ini_alter,proc_get_status,chown,chgrp,chroot,pfsockopen,stream_socket_server,error_log' >> /usr/local/etc/php/php.ini

session_path=`head -n 16 /dev/urandom | md5sum | head -c 16`
mkdir /tmp/$session_path
chmod 777 /tmp/$session_path
chmod +t /tmp/$session_path
chown root:root /tmp/$session_path
echo "session.save_path = /tmp/$session_path/" >> /usr/local/etc/php/php.ini
session_path=''

apache2-foreground
