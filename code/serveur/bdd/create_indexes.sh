#!/bin/bash
set -e
dbname="$1"

command() {
	cmd="$1"
	if [ -z "$cmd" ]; then cmd=$(cat); fi
	commands["${#commands[@]}"]="$cmd"
}

do_commands() {
	ctr=0
	for i in "${commands[@]}"; do
		echo -n '.'
		sqlite3 "$dbname" "$i"
	done | pv -s "${#commands[@]}" > /dev/null
}

echo 1/3
unset commands
command "create index i_relation_start on relation(start);"
command "create index i_relation_end on relation(end);"
command "create index i_relation_type on relation(type);"
command "create index i_relation_start_type on relation(start,type);"
command "create index i_relation_end_type on relation(end,type);"
command "create index i_played_game_all on played_game(pgid, gid, login, timestamp);"
command "create index i_colon_nodes_eid on colon_nodes(eid);"
command "insert into colon_nodes(eid) select eid from node where name glob '::*';"
command <<EOF
create table random_cloud_node(eid,nbneighbors);
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
EOF
command "create index i_random_cloud_node_nbneighbors on random_cloud_node(nbneighbors);"
command <<EOF
create table random_center_node(eid);
insert into random_center_node(eid) select eid from random_cloud_node where nbneighbors > 3;
EOF
do_commands

echo 2/3
unset commands
# «Réseau de neuronnes»
command "create index i_relation_start_end on relation(start,end);"
command "create index i_relation_start_end_type on relation(start,end,type);"
# Environ 0.2% des poids sont négatifs, donc on ne s'occupe pas de les traiter.
command "create table guessTransitivity2(TA,TB,TDeduction,weight);"
for TA in 5 7 9 10 13 14 22; do
	for TB in 5 7 9 10 13 14 22; do
		command "insert into guessTransitivity2(TA,TB,TDeduction,weight,total) select $TA,$TB,C.type,sum(A.weight)+sum(B.weight),0 from relation as A, relation as B, relation as C where A.end = B.start and A.type = $TA and B.type = $TB and C.start = A.start and C.end = B.end group by C.type order by count(C.type);"
		command "update guessTransitivity2 set total = (select sum(A.weight)+sum(B.weight) from relation as A, relation as B where A.end = B.start and A.type = $TA and B.type = $TB) where TA = $TA and TB = $TB;"
	done
done
do_commands

echo 3/3
unset commands
command "create table guessTransitivity3(TA,TB,TC,TDeduction,weight);"
for TA in 5 7 9 10 13 14 22; do
	for TB in 5 7 9 10 13 14 22; do
		for TC in 5 7 9 10 13 14 22; do
			command "insert into guessTransitivity3(TA,TB,TC,TDeduction,weight,total) select $TA,$TB,$TC,D.type,sum(A.weight)+sum(B.weight)+sum(C.weight),0 from relation as A, relation as B, relation as C, relation as D where A.end = B.start and B.end = C.start and A.type = $TA and B.type = $TB and C.type = $TC and D.start = A.start and D.end = C.end group by D.type order by count(D.type);"
			command "update guessTransitivity2 set total = (select sum(A.weight)+sum(B.weight)+sum(C.weight) from relation as A, relation as B, relation as C where A.end = B.start and B.end = C.start and A.type = $TA and B.type = $TB and C.type = $TC) where TA = $TA and TB = $TB and TC = $TC;"
		done
	done
done
do_commands
