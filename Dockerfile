# Usar imagen oficial de PHP con Apache
FROM php:8.2-apache

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql zip sockets

# Habilitar mod_rewrite de Apache para URLs amigables
RUN a2enmod rewrite

# Configurar el directorio de trabajo
WORKDIR /var/www/html

# Copiar el código del proyecto al contenedor
COPY . .

# Asegurar que existan las carpetas de escritura y ajustar permisos (Inercia Cero)
RUN mkdir -p /var/www/html/public/uploads /var/www/html/storage/cache \
    && chown -R www-data:www-data /var/www/html/public/uploads /var/www/html/storage/cache \
    && chmod -R 775 /var/www/html/public/uploads /var/www/html/storage/cache

# Exponer el puerto 80
EXPOSE 80

# El comando por defecto es apache2-foreground (ya definido en la imagen base)
