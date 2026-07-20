#!/bin/bash
set -e
# Mantem o esquema do banco atualizado antes de aceitar requisicoes.
php artisan migrate --force
# Substitui o processo do script pelo servidor para propagar sinais corretamente.
exec "$@"