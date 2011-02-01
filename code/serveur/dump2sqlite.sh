#!/bin/sh

echo "  dump2sql.sh : conversion des dumps de JeuxDeMots vers du sql (sqlite3)." >&2
echo "  La progression est affichée avec pv. Si vous n'avez pas pv, supprimez la ligne correspondante dans ce script." >&2
echo "  Et c'est parti !" >&2
echo >&2

cat <<EOF
begin transaction;
create table node(eid integer primary key autoincrement, name, type, weight);
create table relation(rid integer primary key autoincrement, start, end, type, weight);
create table type_node(name, num);
create table type_relation(name, num, extended_name, info);
create table user(login primary key, mail, hash_passwd);
create table game(gid integer primary key autoincrement, eid_central_word, relation_1, relation_2, relation_3, relation_4, reference_played_game);
create table game_cloud(gid, num, difficulty, eid_word);
create table played_game(gid, type, num, relation, weight);
EOF

# tr : pour virer le CRLF qui traîne
# Le gros tas de sed / tr : pour virer le newline dans une des description étendue
cat "$1" \
| iconv -f iso-8859-1 -t utf-8 \
| tr '\r' ' ' \
| sed -e 's/X/XX/g' | sed -e 's/A/Xa/g' | tr '\n' 'A' | sed -e 's/A")/")/g' | tr 'A' '\n' | sed -e 's/Xa/A/g' | sed -e 's/XX/X/g' \
| pv -s $(wc -c < "$1") \
| sed -E -e "s#'#''#g" \
  -e 's#^/?// [0-9]+ occurrences of relations ([a-z_]+) \(t=([0-9]+) nom_etendu="([^"]+)" info="([^"]+)"\)$#insert into type_relation(name, num, extended_name, info) values('\''\1'\'', \2, '\''\3'\'', '\''\4'\'');#' \
  -e 's#^/?// [0-9]+ occurrences of nodes ([a-z_]+) \(t=([0-9]+)\)$#insert into type_node(name, num) values('\''\1'\'', \2);#' \
  -e 's#^eid=([0-9]+):n="(.*)":t=([0-9]+):w=(-?[0-9]+)$#insert into node(eid, name, type, weight) values(\1, '\''\2'\'', '\''\3'\'', '\''\4'\'');#' \
  -e 's#^rid=([0-9]+):n1=([0-9]+):n2=([0-9]+):t=([0-9]+):w=(-?[0-9]+)#insert into relation(rid, start, end, type, weight) values(\1,\2,\3,\4,\5);#' \
| grep -v '^//' \
| grep -v '^$'

cat <<EOF
create index i_relation_start on relation(start);
create index i_relation_end on relation(end);
create index i_relation_type on relation(type);
create index i_relation_end_type on relation(end,type);
commit;
EOF
