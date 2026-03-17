#!/bin/bash

set -e

input="$1"
output="$2"
progress="$3"

if [ ! -f "$input" ]; then
  echo "Input file not found"
  exit 1
fi

ffmpeg -i "$input" \
      -vn \
      -acodec libmp3lame \
      -ab 192k \
      -progress "$progress" \
      "$output"