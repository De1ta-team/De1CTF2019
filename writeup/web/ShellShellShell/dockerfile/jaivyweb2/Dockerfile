FROM ubuntu:16.04

RUN sed -i "s/http:\/\/archive.ubuntu.com/http:\/\/mirrors.aliyun.com/g" /etc/apt/sources.list
RUN apt-get update && apt-get install -y \
    php \
    apache2 \
    libapache2-mod-php \
    lsof

RUN rm /var/www/html/index.html
ADD ./source/ /var/www/html/

COPY ./flag.txt /etc/flag_is_He4e_89587236.txt
COPY ./php.ini /etc/php/7.0/apache2/php.ini
COPY ./apache2.conf /etc/apache2/apache2.conf
RUN chmod 744 /etc/php/7.0/apache2/php.ini
RUN chmod 744 /etc/apache2/apache2.conf

WORKDIR /var/www/html/

RUN mkdir /var/sandbox/
RUN chmod -R 777 /var/sandbox/
RUN chmod -R 755 /var/www/html/
RUN chmod 744 /etc/flag_is_He4e_89587236.txt
EXPOSE 80

