#!/bin/bash

git checkout master
git fetch update
git fetch origin


git checkout MaYor-dev
git pull update master
git merge devel


git checkout master
git merge MaYor-dev


