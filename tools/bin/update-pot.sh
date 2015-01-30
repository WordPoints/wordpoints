#!/bin/bash

if [ -z $1 ]; then
	echo 'Usage: update-pot.sh <wordpoints-src>'
	exit 0
fi

src=$( cd "$1"; pwd )
bin=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

php "$bin/../i18n/makepot.php" wordpoints "$src"

for f in $(find "$src/languages" -name '*.po' -type f); do
	msgmerge --backup=off --update "$f" "$src/languages/wordpoints.pot"
	msgfmt -o "${f%po}mo" "$f"
done

# EOF
