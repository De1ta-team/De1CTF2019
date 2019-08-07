#!/bin/bash

sleep 1

if [ -e /inited ]
then
    service ssh start
    service mysql start
    service apache2 start
    while true; do echo running; sleep 3; done
fi

VOLUME_HOME="/var/lib/mysql"

if [ -e /etc/php/5.6/apache2/php.ini ]
then
    sed -ri -e "s/^upload_max_filesize.*/upload_max_filesize = ${PHP_UPLOAD_MAX_FILESIZE}/" \
        -e "s/^post_max_size.*/post_max_size = ${PHP_POST_MAX_SIZE}/" /etc/php/5.6/apache2/php.ini
else
    sed -ri -e "s/^upload_max_filesize.*/upload_max_filesize = ${PHP_UPLOAD_MAX_FILESIZE}/" \
        -e "s/^post_max_size.*/post_max_size = ${PHP_POST_MAX_SIZE}/" /etc/php/7.2/apache2/php.ini
fi


sed -i "s/export APACHE_RUN_GROUP=www-data/export APACHE_RUN_GROUP=staff/" /etc/apache2/envvars

if [ -n "$APACHE_ROOT" ];then
    rm -f /var/www/html && ln -s "/app/${APACHE_ROOT}" /var/www/html
fi

sed -i -e "s/cfg\['blowfish_secret'\] = ''/cfg['blowfish_secret'] = '`date | md5sum`'/" /var/www/phpmyadmin/config.inc.php

mkdir -p /var/run/mysqld

if [ -n "$VAGRANT_OSX_MODE" ];then
    usermod -u $DOCKER_USER_ID www-data
    groupmod -g $(($DOCKER_USER_GID + 10000)) $(getent group $DOCKER_USER_GID | cut -d: -f1)
    groupmod -g ${DOCKER_USER_GID} staff
    chmod -R 770 /var/lib/mysql
    chmod -R 770 /var/run/mysqld
    chown -R www-data:staff /var/lib/mysql
    chown -R www-data:staff /var/run/mysqld
else
    # Tweaks to give Apache/PHP write permissions to the app
    chown -R www-data:staff /var/www
    chown -R www-data:staff /app
    chown -R www-data:staff /var/lib/mysql
    chown -R www-data:staff /var/run/mysqld
    chmod -R 770 /var/lib/mysql
    chmod -R 770 /var/run/mysqld
fi

rm /var/run/mysqld/mysqld.sock

sed -i "s/bind-address.*/bind-address = 0.0.0.0/" /etc/mysql/my.cnf
sed -i "s/user.*/user = www-data/" /etc/mysql/my.cnf

if [[ ! -d $VOLUME_HOME/mysql ]]; then
    echo "=> An empty or uninitialized MySQL volume is detected in $VOLUME_HOME"
    echo "=> Installing MySQL ..."

    # Try the 'preferred' solution
    mysqld --initialize-insecure > /dev/null 2>&1

    # IF that didn't work
    if [ $? -ne 0 ]; then
        # Fall back to the 'depreciated' solution
        mysql_install_db > /dev/null 2>&1
    fi

    echo "=> Done!"
    /create_mysql_users.sh
else
    echo "=> Using an existing volume of MySQL"
fi

mv /app/sandbox /
chown -R www-data:www-data /var/lib/php*
chown -R root:root /app
chmod -R 775 /app
chown -R root:root /sandbox
chmod -R 775 /sandbox
chown www-data:www-data /sandbox/missiles

mkdir /var/run/mysqld
chown -R mysql:mysql /var/log/mysql
chown -R mysql:mysql /var/lib/mysql
chown -R mysql:mysql /var/lib/mysql-files
chown -R mysql:mysql /var/run/mysqld
chmod 777 /tmp

mysqld_safe &

sleep 5
mysql -uroot -e "CREATE DATABASE giftbox;"
mysql -uroot -e "CREATE USER 'de1ta'@'%' IDENTIFIED BY '5zesAUyE67IKOwPS';"
mysql -uroot -e "GRANT ALL PRIVILEGES ON giftbox.* TO 'de1ta'@'%';"
mysql -uroot -e "FLUSH PRIVILEGES;"
mysql -uroot -e "USE giftbox; SOURCE /tmp/users.sql;"
rm -rf /tmp/users.sql

echo 'date.timezone = PRC' >> /etc/php/7.3/apache2/php.ini
echo 'session.upload_progress.enabled = Off' >> /etc/php/7.3/apache2/php.ini
echo 'allow_url_fopen = Off' >> /etc/php/7.3/apache2/php.ini
echo 'open_basedir = /app:/sandbox' >> /etc/php/7.3/apache2/php.ini
echo 'display_errors = Off' >> /etc/php/7.3/apache2/php.ini
echo 'disable_functions = pcntl_alarm,pcntl_fork,pcntl_waitpid,pcntl_wait,pcntl_wifexited,pcntl_wifstopped,pcntl_wifsignaled,pcntl_wifcontinued,pcntl_wexitstatus,pcntl_wtermsig,pcntl_wstopsig,pcntl_signal,pcntl_signal_get_handler,pcntl_signal_dispatch,pcntl_get_last_error,pcntl_strerror,pcntl_sigprocmask,pcntl_sigwaitinfo,pcntl_sigtimedwait,pcntl_exec,pcntl_getpriority,pcntl_setpriority,pcntl_async_signals,dl,exec,system,passthru,popen,proc_open,shell_exec,mail,imap_open,imap_mail,getenv,setenv,putenv,apache_setenv,symlink,link,popepassthru,syslog,readlink,openlog,ini_restore,ini_alter,proc_get_status,chown,chgrp,chroot,pfsockopen,stream_socket_server,error_log' >> /etc/php/7.3/apache2/php.ini
rm -rf /app/index.php

touch /inited

source /etc/apache2/envvars
exec apache2 -D FOREGROUND

while true; do echo running; sleep 3; done