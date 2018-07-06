#!/bin/bash


orev=$(cat rev.txt)
nrev=$(wget http://www.mayor.hu/download/rev.txt -O -)

pd=$(pwd)

if [ "$nrev" -gt "$orev" ]; then 

	wget http://www.mayor.hu/download/rev.txt -O rev.txt

	cd /tmp/
	wget http://www.mayor.hu/download/current/mayor-base-current.tgz
	wget http://www.mayor.hu/download/current/mayor-naplo-current.tgz
	wget http://www.mayor.hu/download/current/mayor-portal-current.tgz
	
	wget http://www.mayor.hu/download/current/mayor-installer-current.tgz
	
	cd $pd/mayor-installer-orig/
	tar -xzf /tmp/mayor-installer-current.tgz

	cd $pd/mayor-orig/
	tar -xzf /tmp/mayor-base-current.tgz
	tar -xzf /tmp/mayor-naplo-current.tgz
	tar -xzf /tmp/mayor-portal-current.tgz
        
        
        
fi
        
	
 