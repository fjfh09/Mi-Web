#!/usr/bin/env bash
set -euo pipefail

MARCADOR="/home/fjavipi/Desktop/Web/html/mantenimiento/club-shaolin/activo"

if [[ -f "$MARCADOR" ]]; then
  rm -f "$MARCADOR"
  echo "Mantenimiento desactivado para clubshaolin.almagara.es"
else
  touch "$MARCADOR"
  echo "Mantenimiento activado para clubshaolin.almagara.es"
fi
