#!/bin/bash

git checkout master
git pull local/master MaYor-dev

git checkout MaYor-dev
git merge devel

git checkout master
git merge MaYor-dev


