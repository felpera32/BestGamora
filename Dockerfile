# Usa uma imagem oficial com PHP 8.2 + Apache
FROM php:8.2-apache

# Copia todos os seus arquivos para o diretório padrão do Apache
COPY . /var/www/html/

# Ajusta permissões para evitar problemas
RUN chown -R www-data:www-data /var/www/html

# Exponha a porta 80 (padrão HTTP)
EXPOSE 80
