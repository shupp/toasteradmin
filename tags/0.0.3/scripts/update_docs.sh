#!/bin/sh
#cd /Users/shupp/web/mailtool || exit 99

DOCSDIR="./doc"
PEARDIR='/usr/share/php'

#setup links
cp $PEARDIR/Framework.php .
cp -rp $PEARDIR/Framework PEAR-Framework

FILES="./htdocs/index.php./Framework.php"
for i in `find ./Framework/ -name "*.php" | grep -v templates_c ` ; do 
    FILES="$FILES,$i"
done
for i in `find ./HTML/ -name "*.php"` ; do 
    FILES="$FILES,$i"
done
for i in `find ./Vpopmail/ -name "*.php"` ; do 
    FILES="$FILES,$i"
done
for i in `find ./PEAR-Framework/ -name "*.php" | grep -v Object/Web.php` ; do 
    FILES="$FILES,$i"
done

phpdoc \
    -s on \
    -ti 'ToasterAdmin Documentation' \
    -dn 'ToasterAdmin' \
    -t $DOCSDIR.tmp \
    -f ./README,$FILES \
    -ric README \
    -o HTML:frames:DOM/earthli

mv $DOCSDIR $DOCSDIR.old
mv $DOCSDIR.tmp $DOCSDIR
rm -rf $DOCSDIR.old

rm Framework.php
rm -r PEAR-Framework

    #-o HTML:frames:DOM/phphtmllib,HTML:Smarty:default,HTML:frames:DOM/earthli,HTML:Smarty:PHP,HTML:Smarty:HandS,HTML:frames:phpedit,HTML:frames:DOM/l0l33t,HTML:frames:DOM/default,HTML:frames:earthli \

#HTML:frames:phpedit
#HTML:frames:l0l33t  apple
#HTML:Smarty:HandS  tan
#HTML:Smarty:default 
#HTML:Smarty:PHP
#HTML:frames:earthli
#phpdoc -s off -ti 'ToasterAdmin Documentation' -dn 'ToasterAdmin' -t doc -f Includes/vpopmail_admin.php
