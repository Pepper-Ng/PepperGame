#!/bin/sh

role=${CONTAINER_ROLE:-none}
generated_app_key_file=/var/www/storage/app_key

normalize_app_key_value() {
    value="$1"

    case "$value" in
        '""'|"''"|'\\"\\"'|"\\'\\'" )
            value=""
            ;;
    esac

    case "$value" in
        \"*\")
            value=$(printf '%s' "$value" | sed 's/^"//; s/"$//')
            ;;
        \'*\')
            value=$(printf '%s' "$value" | sed "s/^'//; s/'$//")
            ;;
    esac

    case "$value" in
        *'=\\"\\"')
            value=$(printf '%s' "$value" | sed 's/\\"\\"$//')
            ;;
        *'=""')
            value=$(printf '%s' "$value" | sed 's/""$//')
            ;;
        *"=\\'\\'")
            value=$(printf '%s' "$value" | sed "s/\\\\'\\\\'$//")
            ;;
        *"=''" )
            value=$(printf '%s' "$value" | sed "s/''$//")
            ;;
    esac

    case "$value" in
        '""'|"''"|'\\"\\"'|"\\'\\'" )
            value=""
            ;;
    esac

    printf '%s' "$value"
}

write_env_var() {
    key="$1"
    value="$2"

    if [ -z "$value" ]; then
        printf '%s=\n' "$key" >> /var/www/.env
        return
    fi

    if [ "$key" = "APP_KEY" ]; then
        printf '%s=%s\n' "$key" "$value" >> /var/www/.env
        return
    fi

    escaped_value=$(printf '%s' "$value" | sed 's/\\/\\\\/g; s/"/\\"/g; s/\$/\\$/g')
    printf '%s="%s"\n' "$key" "$escaped_value" >> /var/www/.env
}

set_env_var_raw() {
    key="$1"
    value="$2"
    escaped_value=$(printf '%s' "$value" | sed 's/[\\&|]/\\&/g')

    if grep -q "^${key}=" /var/www/.env; then
        sed -i "s|^${key}=.*|${key}=${escaped_value}|" /var/www/.env
    else
        printf '%s=%s\n' "$key" "$value" >> /var/www/.env
    fi
}

read_dotenv_value() {
    key="$1"
    value=$(grep -E "^${key}=" .env | cut -d '=' -f2- | tr -d '\r')

    normalize_app_key_value "$value"
}

create_env_file_from_environment() {
    app_key_value=$(normalize_app_key_value "${APP_KEY:-}")

    if [ -z "$app_key_value" ] && [ -f "$generated_app_key_file" ]; then
        app_key_value=$(normalize_app_key_value "$(tr -d '\r\n' < "$generated_app_key_file")")
    fi

    : > /var/www/.env

    write_env_var "APP_NAME" "${APP_NAME:-OGameX}"
    write_env_var "APP_ENV" "${APP_ENV:-production}"
    write_env_var "APP_KEY" "$app_key_value"
    write_env_var "APP_DEBUG" "${APP_DEBUG:-false}"
    write_env_var "LOG_LEVEL" "${LOG_LEVEL:-error}"
    write_env_var "APP_URL" "${APP_URL:-https://localhost}"
    write_env_var "DEBUGBAR_ENABLED" "${DEBUGBAR_ENABLED:-false}"
    write_env_var "DEBUGBAR_REMOTE_SITES_PATH" "${DEBUGBAR_REMOTE_SITES_PATH:-/var/www}"
    write_env_var "DEBUGBAR_LOCAL_SITES_PATH" "${DEBUGBAR_LOCAL_SITES_PATH:-/var/www}"
    write_env_var "DISCORD_ALERT_WEBHOOK" "${DISCORD_ALERT_WEBHOOK:-}"
    write_env_var "DB_CONNECTION" "${DB_CONNECTION:-mysql}"
    write_env_var "DB_HOST" "${DB_HOST:-ogamex-db}"
    write_env_var "DB_PORT" "${DB_PORT:-3306}"
    write_env_var "DB_DATABASE" "${DB_DATABASE:-laravel}"
    write_env_var "DB_USERNAME" "${DB_USERNAME:-root}"
    write_env_var "DB_PASSWORD" "${DB_PASSWORD:-toor}"
    write_env_var "BROADCAST_CONNECTION" "${BROADCAST_CONNECTION:-log}"
    write_env_var "CACHE_STORE" "${CACHE_STORE:-file}"
    write_env_var "SESSION_DRIVER" "${SESSION_DRIVER:-database}"
    write_env_var "SESSION_LIFETIME" "${SESSION_LIFETIME:-720}"
    write_env_var "QUEUE_CONNECTION" "${QUEUE_CONNECTION:-database}"
    write_env_var "REDIS_HOST" "${REDIS_HOST:-127.0.0.1}"
    write_env_var "REDIS_PASSWORD" "${REDIS_PASSWORD:-null}"
    write_env_var "REDIS_PORT" "${REDIS_PORT:-6379}"
    write_env_var "MAIL_MAILER" "${MAIL_MAILER:-log}"
    write_env_var "MAIL_HOST" "${MAIL_HOST:-127.0.0.1}"
    write_env_var "MAIL_PORT" "${MAIL_PORT:-2525}"
    write_env_var "MAIL_USERNAME" "${MAIL_USERNAME:-}"
    write_env_var "MAIL_PASSWORD" "${MAIL_PASSWORD:-}"
    write_env_var "MAIL_ENCRYPTION" "${MAIL_ENCRYPTION:-null}"
    write_env_var "MAIL_FROM_ADDRESS" "${MAIL_FROM_ADDRESS:-hello@example.com}"
    write_env_var "MAIL_FROM_NAME" "${MAIL_FROM_NAME:-OGameX}"
    write_env_var "PUSHER_APP_ID" "${PUSHER_APP_ID:-}"
    write_env_var "PUSHER_APP_KEY" "${PUSHER_APP_KEY:-}"
    write_env_var "PUSHER_APP_SECRET" "${PUSHER_APP_SECRET:-}"
    write_env_var "PUSHER_HOST" "${PUSHER_HOST:-}"
    write_env_var "PUSHER_PORT" "${PUSHER_PORT:-443}"
    write_env_var "PUSHER_SCHEME" "${PUSHER_SCHEME:-https}"
    write_env_var "PUSHER_APP_CLUSTER" "${PUSHER_APP_CLUSTER:-mt1}"
    write_env_var "REVERB_APP_ID" "${REVERB_APP_ID:-ogamex}"
    write_env_var "REVERB_APP_KEY" "${REVERB_APP_KEY:-ogamex-key}"
    write_env_var "REVERB_APP_SECRET" "${REVERB_APP_SECRET:-ogamex-secret}"
    write_env_var "REVERB_HOST" "${REVERB_HOST:-localhost}"
    write_env_var "REVERB_PORT" "${REVERB_PORT:-443}"
    write_env_var "REVERB_SCHEME" "${REVERB_SCHEME:-https}"
    write_env_var "REVERB_SERVER_HOST" "${REVERB_SERVER_HOST:-0.0.0.0}"
    write_env_var "REVERB_SERVER_PORT" "${REVERB_SERVER_PORT:-8090}"

    echo ".env file not found, generated .env from container environment"
}

ensure_runtime_directories() {
    mkdir -p \
        /var/www/storage/app/public \
        /var/www/storage/debugbar \
        /var/www/storage/framework/cache/data \
        /var/www/storage/framework/sessions \
        /var/www/storage/framework/testing \
        /var/www/storage/framework/views \
        /var/www/storage/logs \
        /var/www/storage/rust-libs \
        /var/www/bootstrap/cache
}

ensure_runtime_ownership() {
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
}

clear_bootstrap_caches() {
    rm -f /var/www/bootstrap/cache/*.php
}

warm_production_caches() {
    if ! su -s /bin/sh -c "php artisan cache:clear" www-data; then
        echo "Warning: application cache clear failed; starting without warmed caches."
        clear_bootstrap_caches
        return 0
    fi

    if ! su -s /bin/sh -c "php artisan config:cache && php artisan route:cache && php artisan view:cache" www-data; then
        echo "Warning: production cache warmup failed; clearing bootstrap caches to avoid broken runtime cache files."
        clear_bootstrap_caches
    fi
}

canonicalize_app_key_env_line() {
    app_key=$(read_dotenv_value "APP_KEY")
    set_env_var_raw "APP_KEY" "$app_key"
}

if [ ! -f /var/www/.env ]; then
    if [ "${BOOTSTRAP_FROM_ENV:-0}" = "1" ]; then
        create_env_file_from_environment
    elif [ "${APP_ENV:-}" = "production" ] && [ -f /var/www/.env.example-prod ]; then
        cp /var/www/.env.example-prod /var/www/.env
        echo ".env file not found, copied .env.example-prod to .env"
    elif [ -f /var/www/.env.example ]; then
        cp /var/www/.env.example /var/www/.env
        echo ".env file not found, copied .env.example to .env"
    else
        echo "Error: .env and .env.example files not found. Please create an .env file." >&2
        exit 1
    fi
fi

# Extract environment information
is_production=false
if [ "${APP_ENV:-}" = "production" ] || grep -q "^APP_ENV=production" .env; then
    is_production=true
fi

# Configure Git to trust the working directory
git config --global --add safe.directory /var/www

if [ "$role" = "scheduler" ]; then
    while true; do
        php /var/www/artisan schedule:run --verbose --no-interaction
        sleep 60
    done
elif [ "$role" = "queue" ]; then
      php /var/www/artisan queue:work --verbose --no-interaction
elif [ "$role" = "reverb" ]; then
    php /var/www/artisan reverb:start --host="${REVERB_SERVER_HOST:-0.0.0.0}" --port="${REVERB_SERVER_PORT:-8090}"
elif [ "$role" = "app" ]; then
    ensure_runtime_directories
    ensure_runtime_ownership

    # Repair legacy quoted-empty or malformed APP_KEY values before Laravel boots.
    canonicalize_app_key_env_line

    # Remove stale bootstrap cache files before composer or artisan boot Laravel.
    clear_bootstrap_caches

    # Check APP_ENV and run appropriate composer install
    if [ "$is_production" = true ]; then
        echo "Production environment detected. Running composer install --no-dev..."
        composer install --no-dev
    else
        echo "Development environment detected. Running composer install..."
        composer install
    fi

    # Generate APP_KEY if not set or empty in the .env file
    app_key=$(read_dotenv_value "APP_KEY")
    if [ -z "$app_key" ]; then
        echo "APP_KEY is empty or not set. Generating a new key..."
        php artisan key:generate --force
        app_key=$(read_dotenv_value "APP_KEY")
        set_env_var_raw "APP_KEY" "$app_key"
    else
        echo "APP_KEY is set to: $app_key"
    fi

    if [ "${BOOTSTRAP_FROM_ENV:-0}" = "1" ] && [ -n "$app_key" ]; then
        printf '%s\n' "$app_key" > "$generated_app_key_file"
    fi

    # Compile rust modules
    chmod +x ./rust/compile.sh
    ./rust/compile.sh

    # Re-apply runtime ownership after build steps created files as root.
    ensure_runtime_ownership

    # Run migrations as www-data to ensure log files are created with correct ownership
    su -s /bin/sh -c "php artisan migrate --force" www-data

    # Only run caching in production (as www-data to ensure correct file ownership)
    if [ "$is_production" = true ]; then
        echo "Production environment: Caching configurations..."
        warm_production_caches
    fi

    exec php-fpm
else
    echo "Could not match the container role \"$role\""
    exit 1
fi