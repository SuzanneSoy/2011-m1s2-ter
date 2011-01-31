#!/bin/sh

echo "  dump2sql.sh : conversion des dumps de JeuxDeMots vers du sql (mysql)." >&2
echo "  La progression est affichée avec pv. Si vous n'avez pas pv, supprimez la ligne correspondante dans ce script." >&2
echo "  Et c'est parti !" >&2
echo >&2

# game_played(type) : 0=partie de référence initiale, 1=partie d'un joueur.

cat <<EOF
start transaction;
create database if not exists pticlic;
use pticlic;
create table node(eid integer auto_increment, name varchar(255), type integer, weight integer, primary key(eid));
create table relation(rid integer auto_increment, start integer, end integer, type integer, weight integer, primary key(rid));
create table type_node(name varchar(255), num integer, primary key(num));
create table type_relation(name varchar(255), num integer, extended_name varchar(16384), info varchar(16384), primary key(num));
create table user(login varchar(255), mail varchar(255), hash_mdp char(32), primary key(login));
create table game(pid integer auto_increment, eid_central_word integer, relation_1 integer, relation_2 integer, relation_3 integer, relation_4 integer, reference_played_game integer, primary key(pid));
create table game_cloud(pid integer, num integer, eid_word integer);
create table game_played(pid integer, type integer, num integer, relation integer, weight integer);
EOF

# tr : pour virer le CRLF qui traîne
# Le gros tas de sed / tr : pour virer le newline dans une des description étendue
cat "$1" \
| iconv -f iso-8859-1 -t utf-8 \
| tr '\r' ' ' \
| sed -e 's/X/XX/g' | sed -e 's/A/Xa/g' | tr '\n' 'A' | sed -e 's/A")/")/g' | tr 'A' '\n' | sed -e 's/Xa/A/g' | sed -e 's/XX/X/g' \
| pv -s $(wc -c < "$1") \
| sed -e "s#'#''#g" \
| sed -e 's/\\//g' \
| sed -E -e 's#^// [0-9]+ occurrences of relations ([a-z_]+) \(t=([0-9]+) nom_etendu="([^"]+)" info="([^"]+)"\)$#insert into type_relation(name, num, extended_name, info) values('\''\1'\'', \2, '\''\3'\'', '\''\4'\'');#' \
| sed -E -e 's#^// [0-9]+ occurrences of nodes ([a-z_]+) \(t=([0-9]+)\)$#insert into type_node(name, num) values('\''\1'\'', \2);#' \
| sed -E -e 's#^eid=([0-9]+):n="(.*)":t=([0-9]+):w=(-?[0-9]+)$#insert into node(eid, name, type, weight) values(\1, '\''\2'\'', '\''\3'\'', '\''\4'\'');#' \
| sed -E -e 's#^rid=([0-9]+):n1=([0-9]+):n2=([0-9]+):t=([0-9]+):w=(-?[0-9]+)#insert into relation(rid, start, end, type, weight) values(\1,\2,\3,\4,\5);#' \
| grep -v 'insert into node(eid, name, type, weight) values(0,' \
| grep -v '^//' \
| grep -v '^$'

cat <<EOF
create index i_relation_start on relation(start);
create index i_relation_end on relation(end);
create index i_relation_type on relation(type);
create index i_relation_end_type on relation(end,type);
commit;
EOF
