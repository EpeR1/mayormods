#!/bin/bash


orev=$(cat rev.txt)
nrev=$(wget http://www.mayor.hu/download/current/md5sum -O - | grep 'Revision' | cut -d ' ' -f 2)

pd=$(pwd)

if [ "$nrev" -gt "$orev" ]; then 

	git checkout MaYor-dev

	wget http://www.mayor.hu/download/current/rev.txt -O rev.txt
	wget http://www.mayor.hu/download/current/md5sum  -O mayor-orig/md5sum

	cd /tmp

	for i in $(cat "$pd"/mayor-orig/md5sum | tail -n+2 | cut -d ' ' -f 3); do
		
		j=$(echo $i | sed -e 's/-rev.*//g')
		wget http://www.mayor.hu/download/current/"$i"
		if [ ! -d "$pd/mayor-orig/$i" ]; then
			mkdir "$pd"/mayor-orig/"$j"
		fi
		tar -xzvf /tmp/"$i" -C "$pd"/mayor-orig/"$j"

	done

	
	cd $pd
	cp -f 'rev.txt' 'mayor-orig/rev.txt'
	cp -rf 'mayor-installer/mayor-installer-orig/log/' 'mayor-installer/mayor-installer-jav/'
	cp -rf 'mayor-installer/mayor-installer-orig/log/' 'mayor-installer/mayor-installer-for-fcgi/'

	git add --all
	git commit -a -m "Rev: $nrev"
	git tag -a "rev$nrev" -m "Rev: $nrev"

	git checkout master
        
fi
        
	
 