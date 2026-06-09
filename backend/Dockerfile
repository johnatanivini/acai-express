FROM php:8.2-fpm

# Instala dependências essenciais do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Limpa o cache do apt para deixar a imagem leve
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala as extensões vitais para o Laravel e MySQL
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

RUN usermod -u 1000 www-data && groupmod -g 1000 www-data
# Copia o Composer oficial para dentro do container
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho padrão
WORKDIR /var/www

# ... (conteúdo anterior do seu Dockerfile) ...


# Copia o script de entrypoint para o container
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Garante que o script tem permissão de execução dentro do Linux do container
RUN chmod +x /usr/local/bin/entrypoint.sh

# Define o script como o ponto de entrada oficial do container
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]