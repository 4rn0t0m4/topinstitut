#!/bin/sh
# Pointe git sur .githooks/ pour activer les hooks versionnés.
# À lancer une fois après clone : `./bin/install-hooks.sh`

set -e
cd "$(dirname "$0")/.."
git config core.hooksPath .githooks
chmod +x .githooks/*
echo "Hooks installés (core.hooksPath=.githooks)"
