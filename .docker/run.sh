#!/bin/bash

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[0;93m'
NC='\033[0m'

cd "$(dirname "$0")"

source ".env"

WP_HOME="${WP_HOME:-${HOME:-http://${DOMAIN}}}"
HTTP_PORT="${HTTP_PORT:-80}"
HTTPS_PORT="${HTTPS_PORT:-443}"

COMPOSE="docker compose --env-file .env -f docker-compose.yml"

port_used_by_other_container() {
    local port="$1"

    docker ps --format '{{.Names}} {{.Ports}}' \
        | grep -E "0\.0\.0\.0:${port}->|\\[::\\]:${port}->" \
        | grep -v "^${APP_NAME}-nginx " || true
}

echo -e ${RED}"Stop project docker containers${NC}"
${COMPOSE} down --remove-orphans

HTTP_CONFLICT=$(port_used_by_other_container "$HTTP_PORT")
HTTPS_CONFLICT=$(port_used_by_other_container "$HTTPS_PORT")

if [ -n "$HTTP_CONFLICT" ] || [ -n "$HTTPS_CONFLICT" ]; then
    echo -e ${RED}"Port already used by another container:${NC}"

    if [ -n "$HTTP_CONFLICT" ]; then
        echo "$HTTP_CONFLICT"
    fi

    if [ -n "$HTTPS_CONFLICT" ]; then
        echo "$HTTPS_CONFLICT"
    fi

    echo -e ${YELLOW}"Stop the container above or change HTTP_PORT/HTTPS_PORT in .docker/.env.${NC}"
    exit 1
fi

echo -e ${GREEN}"Build project docker container${NC}"
${COMPOSE} up

echo -e ${GREEN}"Website up and running:${NC}"
echo -e ${GREEN}"${WP_HOME}${NC}"
echo -e ${GREEN}"http://localhost:${HTTP_PORT}/${NC}"
