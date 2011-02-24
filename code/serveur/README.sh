#!/bin/sh

# cat dump.url
# Aller à cette adresse, et télécharger le dernier dump
echo
echo "Étape 1/3 : Téléchargement"
echo "=========================="
latest="$(wget 'http://www.lirmm.fr/~lafourcade/JDM-LEXICALNET-FR/?C=M;O=D' -O- | grep '\-LEXICALNET\-JEUXDEMOTS\-FR\-\(NOHTML\)\?\.txt' | head -n 1 | sed -E -e 's/^.*<a href="([0-9]*-LEXICALNET-JEUXDEMOTS-FR-(NOHTML)?\.txt)">.*$/\1/')"
wget -c 'http://www.lirmm.fr/~lafourcade/JDM-LEXICALNET-FR/'"$latest"

echo
echo "Étape 2/3 : Conversion vers sql"
echo "==============================="
./dump2sqlite.sh "$latest" > sql

echo
echo "Étape 3/3 : Insertion dans la bdd"
echo "================================="
[ -e php/db ] && mv php/db php/db.old
pv sql | sqlite3 php/db


: > /tmp/log-chmod-pticlic
sudo chgrp -R www-data php > /tmp/log-chmod-pticlic || sudo chgrp -R www php > /tmp/log-chmod-pticlic || {
	cat /tmp/log-chmod-pticlic
	echo "ATTENTION : Les deux méthodes de chgrp ont échoué !"
	exit 1
}
chmod 664 php/db
chmod 775 php
