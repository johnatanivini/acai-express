#!/bin/bash

echo "Instalando dependências do Composer..."
composer install --no-interaction --prefer-dist --optimize-autoloader

echo "Rodando as Migrations do banco de dados..."
# O --force é importante para o Laravel não pedir confirmação (yes/no) dentro do script automatizado
php artisan migrate --force

echo "Iniciando o servidor PHP-FPM..." 
# Executa o comando principal do container original
exec php-fpm