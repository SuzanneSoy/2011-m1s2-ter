#!/bin/sh

# cat dump.url
# Aller à cette adresse, et télécharger le dernier dump
echo "Étape 1/3 : Téléchargement"
latest="$(wget 'http://www.lirmm.fr/~lafourcade/JDM-LEXICALNET-FR/?C=M;O=D' -O- | grep '\-LEXICALNET\-JEUXDEMOTS\-FR\-\(NOHTML\)\?\.txt' | head -n 1 | sed -e 's/^.*<a href="\([0-9]*-LEXICALNET-JEUXDEMOTS-FR-\(NOHTML\)\?\.txt\)">.*$/\1/')"
wget 'http://www.lirmm.fr/~lafourcade/JDM-LEXICALNET-FR/'"$latest"

echo "Étape 2/3 : Conversion vers sql"
./dump2sqlite.sh "$latest" > sql

echo "Étape 3/3 : Insertion dans la bdd"
mv php/db php/db.old
pv sql | sqlite3 php/db

sudo chgrp -R www-data php || sudo chgrp -R www php
chmod 664 php/db
