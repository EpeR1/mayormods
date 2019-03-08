#!/bin/bash

for i in $(find /srv/mayor/ -name *.example -print | sed -e 's/.example//'); do 
	name=`basename $i`; 
	dir=$(echo $i | sed -e 's#/srv/mayor#/root/mayor/sulix#' -e "s/$name//"); 
	mkdir -p $dir; 
	cp $i.example $(echo $i | sed -e 's#/srv/mayor#/root/mayor/sulix#').sulix; 
done
