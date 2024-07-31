FROM arm64v8/php:8.2-fpm

# 必要な拡張機能のインストール
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo_mysql zip

# Composerのインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# アプリケーションファイルのコピー
COPY src .

# Composerキャッシュディレクトリを利用して依存関係をインストール
RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-scripts

# Laravelのセットアップを手動で実行
RUN php artisan package:discover --ansi || true

# .envファイルをコピー
COPY .env .env

# キャッシュをクリアして設定をキャッシュする
RUN php artisan config:cache || true

# Laravelの開発サーバーを起動
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
