FROM mattrayner/lamp:latest-1804

EXPOSE 80

RUN rm -rf /app/*

COPY source /app

COPY users.sql /tmp

COPY flag.txt /flag

COPY start.sh /start.sh

RUN chmod +x /start.sh

WORKDIR /app

ENTRYPOINT ["/start.sh"]