# syntax=docker/dockerfile:1.7
#
# Reusable Docker template for framework-less "public/ web root" PHP
# MVC apps (entry point: public/index.php, config/app.php one level
# up, Composer for vendor deps). Built for Coolify's Docker build
# pack (not Nixpacks) — single image, Nginx + PHP-FPM under
# supervisord, no docker-compose required.
#
# To reuse for another project: only the extension list and the
# APP_ENV-driven php.ini values below are likely to need tweaking.

ARG PHP_VERSION=8.3

# ── Stage 1: install Composer dependencies in isolation ──────────
# Keeps the vendor/ build (and its dev tools) out of the final
# runtime image entirely.
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock* ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --no-progress \
    --optimize-autoloader

# ── Stage 2: runtime image ───────────────────────────────────────
FROM php:${PHP_VERSION}-fpm-alpine AS runtime

# System packages: nginx + supervisord to run both processes in one
# container, plus the native libs PHP extensions below link against.
RUN apk add --no-cache \
        nginx \
        supervisor \
        curl \
        icu-libs \
        libpng \
        libjpeg-turbo \
        freetype \
        libzip \
        oniguruma \
    && apk add --no-cache --virtual .build-deps \
    icu-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        gd \
        zip \
        intl \
        exif \
        opcache \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/*

# ── PHP runtime config ────────────────────────────────────────────
# Mirrors the upload_max_filesize / post_max_size / max_execution_time
# values from public/.htaccess (Apache's mod_php equivalents), plus
# production-sensible opcache + error-log settings. APP_ENV/error
# display is still controlled by config/app.php via getenv('APP_ENV').
COPY <<-'EOF' /usr/local/etc/php/conf.d/zz-app.ini
upload_max_filesize = 10M
post_max_size = 12M
max_execution_time = 60
expose_php = Off
display_errors = Off
log_errors = On
error_log = /dev/stderr
opcache.enable = 1
opcache.validate_timestamps = 0
opcache.memory_consumption = 128
opcache.max_accelerated_files = 10000
EOF

# PHP-FPM: talk to Nginx over a unix socket, run as www-data.
COPY <<-'EOF' /usr/local/etc/php-fpm.d/zz-app.conf
[www]
listen = /run/php-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660
user = www-data
group = www-data
pm = dynamic
pm.max_children = 20
pm.start_servers = 4
pm.min_spare_servers = 2
pm.max_spare_servers = 8
clear_env = no
EOF

# Nginx: reusable config (see nginx.conf) — same rewrite behavior as
# the Apache .htaccess this app ships with.
COPY nginx.conf /etc/nginx/nginx.conf

# Supervisord: run php-fpm + nginx as PID 1's two children so the
# whole thing works as a single Coolify/Docker service.
COPY <<-'EOF' /etc/supervisord.conf
[supervisord]
nodaemon=true
user=root
logfile=/dev/stdout
logfile_maxbytes=0
pidfile=/run/supervisord.pid

[program:php-fpm]
command=php-fpm --nodaemonize
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

[program:nginx]
command=nginx -g "daemon off;"
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
EOF

WORKDIR /var/www/html

# App code, respecting .dockerignore (vendor/, .env, uploads
# contents, docs, etc. are all excluded there).
COPY . .
COPY --from=vendor /app/vendor ./vendor

# uploads/ and public/uploads/ must stay writable by the app at
# runtime (uploadFile() in app/helpers/functions.php writes here).
# On Coolify, mount a persistent volume at /var/www/html/public/uploads
# so uploaded files survive redeploys — the directory below just
# guarantees correct permissions whether or not a volume is mounted.
RUN mkdir -p /var/www/html/public/uploads \
    && chown -R www-data:www-data /var/www/html \
    && find /var/www/html/uploads /var/www/html/public/uploads -type d -exec chmod 775 {} \; \
    && find /var/www/html/uploads /var/www/html/public/uploads -type f -exec chmod 664 {} \;

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -fsS http://127.0.0.1/ -o /dev/null || exit 1

CMD ["supervisord", "-c", "/etc/supervisord.conf"]
