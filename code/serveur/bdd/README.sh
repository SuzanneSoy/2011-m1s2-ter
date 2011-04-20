#!/bin/sh

# cat dump.url
# Aller à cette adresse, et télécharger le dernier dump
echo
echo "Étape 1/5 : Téléchargement de la version du dump"
echo "================================================"
latest="$(wget 'http://www.lirmm.fr/~lafourcade/JDM-LEXICALNET-FR/?C=M;O=D' -O- | grep '\-LEXICALNET\-JEUXDEMOTS\-FR\-\(NOHTML\)\?\.txt' | head -n 1 | sed -E -e 's/^.*<a href="([0-9]*-LEXICALNET-JEUXDEMOTS-FR-(NOHTML)?\.txt)">.*$/\1/')"
if [ -z "$latest" ]; then
	echo "Une erreur est survenue lors de la récupération de la dernière version."
	exit 1
fi

echo
echo "Étape 2/5 : Téléchargement du dump"
echo "=================================="
wget -c 'http://www.lirmm.fr/~lafourcade/JDM-LEXICALNET-FR/'"$latest"

echo
echo "Étape 3/5 : Conversion vers sql et insertion dans la bdd"
echo "========================================================"
[ -e ../php/db.new ] && rm -f ../php/db.new
./convert_dump.sh "$latest" ../php/db.new

echo
echo "Étape 4/5 : Création des index et caches"
echo "========================================"
./create_indexes.sh ../php/db.new

echo
echo "Étape 5/5 : Réglage des permissions"
echo "==================================="
: > /tmp/log-chmod-pticlic
sudo chgrp -R www-data ../php > /tmp/log-chmod-pticlic || sudo chgrp -R www ../php >> /tmp/log-chmod-pticlic || {
	cat /tmp/log-chmod-pticlic
	echo "ATTENTION : Les deux méthodes de chgrp ont échoué !"
	exit 1
}
chmod 664 ../php/db.new
chmod 775 ../php

[ -e ../php/db ] && mv ../php/db ../php/db.old
mv ../php/db.new ../php/db
