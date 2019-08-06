#!/bin/bash
if [ "$ALLOW_OVERRIDE" = "**False**" ]; then
    unset ALLOW_OVERRIDE
else
    sed -i "s/AllowOverride None/AllowOverride All/g" /etc/apache2/apache2.conf
    a2enmod rewrite
fi


### 
echo " bash mysql_peizhi.sh"
service mysql start
sleep 5
mysql -uroot -proot < /tmp/sql.sql
mysql -uroot -proot -e "update mysql.user set password=password('rootpassword') where user='root';FLUSH PRIVILEGES;"
rm -rf /tmp/*

# feiyuqi_fangzhi
sed -i "s/;session.upload_progress.enabled = On/session.upload_progress.enabled = Off/g" /etc/php5/cli/php.ini
sed -i "s/;session.upload_progress.enabled = On/session.upload_progress.enabled = Off/g" /etc/php5/apache2/php.ini

cd /etc/php5/apache2/conf.d/
rm 20-xdebug.ini
rm 20-memcached.ini
rm 20-memcache.ini

service apache2 start
sleep 2
service mysql restart
sleep 2












while [[ 1 ]]; do
	sleep 200
	#statements
done


