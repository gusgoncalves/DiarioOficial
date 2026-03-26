# 1. Instalar Composer
composer install
# 2. Instalar dependência
composer require smalot/pdfparser

# 2. Rodar migrations
php spark migrate

# 3. Indexar pela primeira vez
php spark diario:indexar