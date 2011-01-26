#!/bin/sh

echo "  dump2sql.sh : conversion des dumps de JeuxDeMots vers du sql (mysql)." >&2
echo "  La progression est affichée avec pv. Si vous n'avez pas pv, supprimez la ligne correspondante dans ce script." >&2
echo "  Et c'est parti !" >&2
echo >&2

cat <<EOF
begin transaction;
create database if not exists pticlic;
create table node(eid integer primary key autoincrement, name, type, weight);
create table relation(rid integer primary key autoincrement, start, end, type, weight);
create table type_node(nom, num);
create table type_relation(nom, num, nom_etendu, info);
create table user(login primary key, mail, hash_mdp);
create table sessid(login, sid);
create table partie(pid, eid_mot_central, relation_1, relation_2, relation_3, relation_4);
create table partie_nuage(pid, num, eid_mod);
create table partie_reference(pid, num, relation, poids);
EOF

# tr : pour virer le CRLF qui traîne
# Le gros tas de sed / tr : pour virer le newline dans une des description étendue
cat "$1" \
| iconv -f iso-8859-1 -t utf-8 \
| tr '\r' ' ' \
| sed -e 's/X/XX/g' | sed -e 's/A/Xa/g' | tr '\n' 'A' | sed -e 's/A")/")/g' | tr 'A' '\n' | sed -e 's/Xa/A/g' | sed -e 's/XX/X/g' \
| pv -s $(wc -c "$1" | cut -d ' ' -f 1) \
| sed -e "s#'#''#g" \
| sed -E -e 's#^// [0-9]+ occurrences of relations ([a-z_]+) \(t=([0-9]+) nom_etendu="([^"]+)" info="([^"]+)"\)$#insert into type_relation(nom, num, nom_etendu, info) values('\''\1'\'', \2, '\''\3'\'', '\''\4'\'');#' \
| sed -E -e 's#^// [0-9]+ occurrences of nodes ([a-z_]+) \(t=([0-9]+)\)$#insert into type_node(nom, num) values('\''\1'\'', \2);#' \
| sed -E -e 's#^eid=([0-9]+):n="(.*)":t=([0-9]+):w=(-?[0-9]+)$#insert into node(eid, name, type, weight) values(\1, '\''\2'\'', '\''\3'\'', '\''\4'\'');#' \
| sed -E -e 's#^rid=([0-9]+):n1=([0-9]+):n2=([0-9]+):t=([0-9]+):w=(-?[0-9]+)#insert into relation(rid, start, end, type, weight) values(\1,\2,\3,\4,\5);#' \
| grep -v '^//' \
| grep -v '^$'

cat <<EOF
create index i_relation_start on relation(start);
create index i_relation_end on relation(end);
create index i_relation_type on relation(type);
create index i_relation_end_type on relation(end,type);
create index i_sessid_login on sessid(login);
create index i_sessid_sid on sessid(sid);
commit;
EOF
