#!/bin/sh
#cd /Users/shupp/web/mailtool || exit 99

FILES="./htdocs/index.php"
for i in `find Framework/ -name *.php | grep -v templates_c ` ; do 
    FILES="$FILES,$i"
done
for i in `find HTML/ -name *.php | grep -v templates_c ` ; do 
    FILES="$FILES,$i"
done

rm -rf doc
phpdoc \
    -s off \
    -ti 'ToasterAdmin Documentation' \
    -dn 'Framework' \
    -t ./doc \
    -f ./README,$FILES \
    -ric README \
    -o HTML:frames:DOM/earthli
    #-o HTML:frames:DOM/phphtmllib,HTML:Smarty:default,HTML:frames:DOM/earthli,HTML:Smarty:PHP,HTML:Smarty:HandS,HTML:frames:phpedit,HTML:frames:DOM/l0l33t,HTML:frames:DOM/default,HTML:frames:earthli \

#HTML:frames:phpedit
#HTML:frames:l0l33t  apple
#HTML:Smarty:HandS  tan
#HTML:Smarty:default 
#HTML:Smarty:PHP
#HTML:frames:earthli
#phpdoc -s off -ti 'ToasterAdmin Documentation' -dn 'ToasterAdmin' -t doc -f Includes/vpopmail_admin.php
