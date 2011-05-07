#!/bin/bash

# TODO : sed -E sur certaines machines, sed -r sur d'autres.

user="foo"
passwd="bar"

if ! [ -e "$1" ]; then
	echo "Le fichier $1 n'existe pas !"
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

cat <<EOF
begin transaction;
create table node(eid integer primary key autoincrement, name, type, weight);
create table relation(rid integer primary key autoincrement, start, end, type, weight);
create table type_node(name, num);
create table type_relation(name, num, extended_name, info);
create table user(login primary key, mail, hash_passwd, score, ugroup);
create table game(gid integer primary key autoincrement, eid_central_word, relation_1, relation_2, difficulty, author);
create table game_cloud(gid, num, difficulty, eid_word, totalWeight, probaR1, probaR2, probaR0, probaTrash);
create table played_game(pgid integer primary key autoincrement, gid, login, timestamp);
create table played_game_cloud(pgid, gid, type, num, relation, weight, score);
create table colon_nodes(eid);
create table random_cloud_node(eid,nbneighbors);
create table random_center_node(eid);
create table user_info(user, key, value, primary key (user, key));

insert into user(login, mail, hash_passwd, score, ugroup) values('$(echo "$user" | sed -e "s/'/''/g")', 'foo@isp.com', '$(echo "$passwd" | dd bs=1 count="${#passwd}" | (if which md5sum >/dev/null 2>&1; then md5sum; else md5; fi) | cut -d ' ' -f 1)', 0, 1);
EOF

# tr : pour virer le CRLF qui traîne
# Le gros tas de sed / tr : pour virer le newline dans une des description étendue
cat "$1" \
| iconv -f iso-8859-1 -t utf-8 \
| tr '\r' ' ' \
| sed -e 's/X/XX/g' | sed -e 's/A/Xa/g' | tr '\n' 'A' | sed -e 's/A")/")/g' | tr 'A' '\n' | sed -e 's/Xa/A/g' | sed -e 's/XX/X/g' \
| pv -s "$(wc -c "$1" | sed -E -e 's/^ *([0-9]*) .*$/\1/')" \
| sed -E \
  -e 's#\\##g' \
  -e "s#'#''#g" \
  -e 's#^/?// [0-9]+ occurrences of relations ([a-z_]+) \(t=([0-9]+) nom_etendu="([^"]+)" info="([^"]+)"\)$#insert into type_relation(name, num, extended_name, info) values('\''\1'\'', \2, '\''\3'\'', '\''\4'\'');#' \
  -e 's#^/?// [0-9]+ occurrences of nodes ([a-z_]+) \(t=([0-9]+)\)$#insert into type_node(name, num) values('\''\1'\'', \2);#' \
  -e 's#^eid=([0-9]+):n="(.*)":t=([0-9]+):w=(-?[0-9]+)$#insert into node(eid, name, type, weight) values(\1, '\''\2'\'', \3, \4);#' \
  -e 's#^rid=([0-9]+):n1=([0-9]+):n2=([0-9]+):t=([0-9]+):w=(-?[0-9]+)#insert into relation(rid, start, end, type, weight) values(\1,\2,\3,\4,\5);#' \
| grep -v '^//' \
| grep -v '^$'

cat <<EOF
create index i_relation_start on relation(start);
create index i_relation_end on relation(end);
create index i_relation_type on relation(type);
create index i_relation_start_type on relation(start,type);
create index i_relation_end_type on relation(end,type);
create index i_played_game_all on played_game(pgid, gid, login, timestamp);
create index i_colon_nodes_eid on colon_nodes(eid);

insert into colon_nodes(eid) select eid from node where name glob '::*';

insert into random_cloud_node(eid,nbneighbors) select eid,sum(nb) from (
	select (select type from node where node.eid = relation.start) as type,
		start as eid,
		count(start) as nb from relation
		where type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) and start not in colon_nodes
		group by start
	union
	select (select type from node where node.eid = relation.start) as type,
		end as eid,
		count(end) as nb from relation
		where type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) and start not in colon_nodes
		group by end
) where type = 1 group by eid;
create index i_random_cloud_node_nbneighbors on random_cloud_node(nbneighbors);
insert into random_center_node(eid) select eid from random_cloud_node where nbneighbors > 3;
commit;
EOF
