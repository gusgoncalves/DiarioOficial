# 1. Instalar Composer
composer install
# 2. Instalar dependência
composer require smalot/pdfparser

# 2. Rodar migrations
php spark migrate

# Rodar manualmente:
php spark diario:indexar

# Com número de dias personalizado ex 10 dias:
php spark diario:indexar 0

# Agendar via cron (todo dia às 8h):
 cd /var/www/diariooficial && php spark diario:indexar >> /var/log/diario_indexar.log 2>&1