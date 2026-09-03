FROM php:8.2-cli-alpine

RUN apk add --no-cache \
        curl \
        docker-cli \
        docker-cli-compose \
        mysql-client \
        wget

WORKDIR /opt/updater

COPY *.php ./

EXPOSE 8088

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD wget -qO- http://localhost:8088/health || exit 1

CMD ["php", "-S", "0.0.0.0:8088", "server.php"]
