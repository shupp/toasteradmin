#!/bin/sh

LCDIR='../Framework/Site/Default/locale/en/LC_MESSAGES'
PHPFILE="$LCDIR/PHPmessages.po"
SMARTYFILE="$LCDIR/SMARTYmessages.po"
MFILE="$LCDIR/messages.po"

TEMPFILE="/tmp/gettext_files.$$"

find .. -name "*.php" | egrep -v '(.svn|Templates/Default/templates_c|Templates/Default/cache)' > $TEMPFILE.php
find .. -name "*.tpl" | egrep -v '(.svn|Templates/Default/templates_c|Templates/Default/cache)' > $TEMPFILE.tpl

php tsmarty2c.php `cat $TEMPFILE.tpl` > $TEMPFILE.smarty

xgettext -L PHP --keyword=_ -f $TEMPFILE.php --output=$PHPFILE
xgettext -L C --output=$SMARTYFILE $TEMPFILE.smarty
sed -i -e 's/CHARSET/UTF-8/' $MFILE $PHPFILE $SMARTYFILE
sed -i -e 's!FULL NAME <EMAIL@ADDRESS>!Bill Shupp <hostmaster@shupp.org>!' $MFILE $PHPFILE $SMARTYFILE

msgcat $PHPFILE $SMARTYFILE -o $MFILE
sed -i -e 's/CHARSET/UTF-8/' $MFILE
sed -i -e 's!FULL NAME <EMAIL@ADDRESS>!Bill Shupp <hostmaster@shupp.org>!' $MFILE
(cd $LCDIR ; msgfmt messages.po)
# cleanup
rm $PHPFILE
rm $SMARTYFILE
rm $TEMPFILE.php
rm $TEMPFILE.tpl
rm $TEMPFILE.smarty
