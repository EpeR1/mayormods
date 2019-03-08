#!/bin/sh

echo "mayor naplo sbin / update.sh"

DIR=/var/mayor
REV="$DIR/www/include/config/rev.php"
PARENT="haladasi/haladasi haladasi/stat  osztalyozo/diak osztalyozo/jegy osztalyozo/dolgozat bejegyzesek/bejegyzesek tanev/fogadoOra tanev/helyettesites tanev/munkaterv tanev/orarend intezmeny/valtas hianyzas/diak hianyzas/nap hianyzas/info uzeno/uzeno ertekeles/ertekeles hirnok/hirnok hirnok/hirnokFeliratkozas"
PUBLIC="tanev/orarend tanev/szabadTerem tanev/helyettesites"
WRITABLE="download/private/export download/private/osztalyozo download/private/nyomtatas/osztalyozo download/private/nyomtatas/haladasi"

svn --force export svn+ssh://svn.mayor.hu/var/svn/trunk/mayor-base/www /var/mayor/www/
# svn --force export svn+ssh://svn.mayor.hu/var/svn/trunk/mayor-keptar/www /var/mayor/www/
# svn --force export svn+ssh://svn.mayor.hu/var/svn/trunk/mayor-honosito/www /var/mayor/www/
# svn --force export svn+ssh://svn.mayor.hu/var/svn/trunk/mayor-portal/www /var/mayor/www/
svn --force export svn+ssh://svn.mayor.hu/var/svn/trunk/mayor-naplo/install/module-naplo/mysql /var/mayor/install/module-naplo/mysql
svn --force export svn+ssh://svn.mayor.hu/var/svn/trunk/mayor-base/print /var/mayor/print
svn --force export svn+ssh://svn.mayor.hu/var/svn/trunk/mayor-naplo/print /var/mayor/print
svn --force export svn+ssh://svn.mayor.hu/var/svn/trunk/mayor-naplo/print /var/mayor/download
chown -R www-data.www-data /var/mayor/download
svn --force export svn+ssh://svn.mayor.hu/var/svn/trunk/mayor-base/data/base /var/mayor/data/base
svn --force export svn+ssh://svn.mayor.hu/var/svn/trunk/mayor-naplo/www /var/mayor/www/ | grep revision | cut -d ' ' -f 3 | uniq > $REV

for f in $PARENT; do
    ln -s $DIR/www/policy/private/naplo/$f-pre.php $DIR/www/policy/parent/naplo/$f-pre.php
    ln -s $DIR/www/policy/private/naplo/$f.php $DIR/www/policy/parent/naplo/$f.php
done

for f in $PUBLIC; do
    ln -s $DIR/www/policy/private/naplo/$f-pre.php $DIR/www/policy/public/naplo/$f-pre.php
    ln -s $DIR/www/policy/private/naplo/$f.php $DIR/www/policy/public/naplo/$f.php
done

for f in $WRITABLE; do
    chmod a+rwx $DIR/$f
done
