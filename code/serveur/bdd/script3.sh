#!/bin/bash

# create table triangles(TA,TB,TC,weight);
# create index i_relation_start_end on relation(start,end);
# create index i_relation_start_end_type on relation(start,end,type);

for TA in 5 7 9 10 13 14 22; do
	for TB in 5 7 9 10 13 14 22; do
			echo "$TA $TB" >&2
			(
					echo "begin transaction;"
					echo "insert into triangles(TA,TB,TC,weight) select $TA,$TB,C.type,count(C.type) from relation as A, relation as B, relation as C where A.end = B.start and A.type = $TA and B.type = $TB and C.start = A.start and C.end = B.end group by C.type order by count(C.type);"
					echo "commit;"
			) | sqlite3 db
	   done
done
