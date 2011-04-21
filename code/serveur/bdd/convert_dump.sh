#!/bin/bash

# TODO : sed -E sur certaines machines, sed -r sur d'autres.

user="foo"
passwd="bar"

if ! [ -e "$1" ]; then
	echo "Le fichier '$1' n'existe pas !" >&2
	exit 1
fi

echo "  dump2sql.sh : conversion des dumps de JeuxDeMots vers du sql (sqlite3)." >&2
echo "  La progression est affichée avec pv. Si vous n'avez pas pv, supprimez la ligne correspondante dans ce script." >&2
echo "  Et c'est parti !" >&2
echo >&2

# Played_game(type) : 0 => partie de référence, 1 => joueur
# Note : l'index i_played_game_all sert à la vérification lors du set_partie.
# Note : le echo | dd | md5 permet de ne pas avoir le \n, y compris sur les versions de sh sous mac boguées qui ne supportent pas «echo -n»
# Valeurs pour le champ group dans user : 1 = player, 2 = admin

(
cat <<EOF
begin transaction;
create table node(eid integer primary key autoincrement, name, type, weight);
create table relation(rid integer primary key autoincrement, start, end, type, weight);
create table type_node(name, num);
create table type_relation(name, num, extended_name, info);
create table user(login primary key, mail, hash_passwd, score, ugroup);
create table game(gid integer primary key autoincrement, eid_central_word, relation_1, relation_2, difficulty);
create table game_cloud(gid, num, difficulty, eid_word, totalWeight, probaR1, probaR2, probaR0, probaTrash);
create table played_game(pgid integer primary key autoincrement, gid, login, timestamp);
create table played_game_cloud(pgid, gid, type, num, relation, weight, score);
create table clouds(eidCentralWord, eidCloudWord, rel5, rel7, rel9, rel10, rel13, rel14, rel22);

insert into user(login, mail, hash_passwd, score, ugroup) values('$(echo "$user" | sed -e "s/'/''/g")', 'foo@isp.com', '$(echo "$passwd" | dd bs=1 count="${#passwd}" | (if which md5sum >/dev/null 2>&1; then md5sum; else md5; fi) | cut -d ' ' -f 1)', 0, 1);
EOF

# tr : pour virer le CRLF qui traîne
# Le gros tas de sed / tr : pour virer le newline dans une des description étendue
cat "$1" \
| iconv -f iso-8859-1 -t utf-8 \
| tr '\r' ' ' \
| sed -e 's/X/XX/g' | sed -e 's/A/Xa/g' | tr '\n' 'A' | sed -e 's/A")/")/g' | tr 'A' '\n' | sed -e 's/Xa/A/g' | sed -e 's/XX/X/g' \
| pv -W -s "$(wc -c "$1" | sed -E -e 's/^ *([0-9]*) .*$/\1/')" \
| sed -E \
  -e 's#\\##g' \
  -e "s#'#''#g" \
  -e 's#^/?// [0-9]+ occurrences of relations ([a-z_]+) \(t=([0-9]+) nom_etendu="([^"]+)" info="([^"]+)"\)$#insert into type_relation(name, num, extended_name, info) values('\''\1'\'', \2, '\''\3'\'', '\''\4'\'');#' \
  -e 's#^/?// [0-9]+ occurrences of nodes ([a-z_]+) \(t=([0-9]+)\)$#insert into type_node(name, num) values('\''\1'\'', \2);#' \
  -e 's#^eid=([0-9]+):n="(.*)":t=([0-9]+):w=(-?[0-9]+)$#insert into node(eid, name, type, weight) values(\1, '\''\2'\'', \3, \4);#' \
  -e 's#^rid=([0-9]+):n1=([0-9]+):n2=([0-9]+):t=([0-9]+):w=(-?[0-9]+)#insert into relation(rid, start, end, type, weight) values(\1,\2,\3,\4,\5);#' \
| grep -v '^//' \
| grep -v '^$'

echo "commit;"
) | sqlite3 "$2"
