FROM php:8.2-cli

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl

# Instalar extensão PHP para MySQL
RUN docker-php-ext-install pdo_mysql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definir diretório
WORKDIR /app

# Copiar projeto
COPY . .

# Instalar dependências PHP
RUN composer install --no-dev --optimize-autoloader

# Permissões
RUN chmod -R 775 storage bootstrap/cache

# Expor porta
EXPOSE 8000

# Start Laravel
CMD php artisan serve --host=0.0.0.0 --port=8000