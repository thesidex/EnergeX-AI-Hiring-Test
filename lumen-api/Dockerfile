# ---------- 1) Build vendor ----------
    FROM composer:2 AS vendor
    WORKDIR /app
    
    # Leverage layer caching
    COPY composer.json composer.lock ./
    RUN composer install --no-dev --no-interaction --prefer-dist --no-progress
    
    # ---------- 2) Runtime (PHP built-in server) ----------
    FROM php:8.2-cli-alpine
    
    WORKDIR /app
    
    # Install PDO MySQL (pdo is built-in; only need pdo_mysql)
    RUN docker-php-ext-install pdo_mysql
    
    # (Optional) Enable OPcache for better perf even in CLI server
    RUN docker-php-ext-install opcache \
     && { echo "opcache.enable=1"; echo "opcache.enable_cli=1"; } > /usr/local/etc/php/conf.d/opcache.ini
    
    # Copy vendor first (cache-friendly), then app
    COPY --from=vendor /app/vendor ./vendor
    COPY . .
    
    EXPOSE 8000
    # Use environment for timezone if you like: APP_TIMEZONE=UTC
    CMD ["php", "-S", "0.0.0.0:8000", "-t", "public", "public/index.php"]
    