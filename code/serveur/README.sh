#!/bin/sh

# cat dump.url
# Aller à cette adresse, et télécharger le dernier dump
echo "Étape 1/3 : Téléchargement"
wget 'http://www.lirmm.fr/~lafourcade/JDM-LEXICALNET-FR/02122011-LEXICALNET-JEUXDEMOTS-FR-NOHTML.txt'

echo "Étape 2/3 : Conversion vers sql"
./dump2sqlite.sh 01232011-LEXICALNET-JEUXDEMOTS-FR-NOHTML.txt > sql

echo "Étape 3/3 : Insertion dans la bdd"
mv php/db php/db.old
pv sql | sqlite3 php/db

sudo chown -R $USER.www-data php
chmod 664 php/db
