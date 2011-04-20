#!/bin/bash

# create table path4(TA,TB,TC,TD,weight);

for TA in 5 7 9 10 13 14 22; do
	for TB in 5 7 9 10 13 14 22; do
		for TC in 5 7 9 10 13 14 22; do
			echo "$TA $TB $TC" >&2
			(
				echo "begin transaction;"
				echo "insert into path4(TA,TB,TC,TD,weight) select $TA,$TB,$TC,D.type,count(D.type) from relation as A, relation as B, relation as C, relation as D where A.end = B.start and B.end = C.start and A.type = $TA and B.type = $TB and C.type = $TC and D.start = A.start and D.end = C.end group by D.type order by count(D.type);"
				echo "commit;"
			) | sqlite3 db
		done
	done
done
