#!/bin/sh
# Prépare l'API à chaque démarrage du conteneur.
# Sur l'offre gratuite de Render, le disque est effacé à chaque redémarrage :
# la base SQLite et le compte de démo sont donc recréés à neuf.
set -e

php bin/console lexik:jwt:generate-keypair --skip-if-exists
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console app:seed --demo
php bin/console cache:warmup

exec "$@"
