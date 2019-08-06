FROM andreisamuilik/php5.5.9-apache2.4-mysql5.5

COPY run.sh /run.sh
RUN chmod 755 /run.sh
RUN rm -rf /app
COPY app /app/

RUN chmod 755 -R /app
RUN chmod 777 /app/upload
RUN ls -al / | grep app
RUN ls -al /app
COPY sql.sql /tmp/
RUN chmod 777 -R /tmp

EXPOSE 80
CMD ["/run.sh"]


