#!/bin/sh

LANGS='it  nl  pt  tr'
LCBASE='../Framework/Site/Default/locale'
LCDIR="$LCBASE/en/LC_MESSAGES"
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
rm $LCBASE/en/LC_MESSAGES/messages.po-e
rm $LCBASE/en/LC_MESSAGES/SMARTYmessages.po-e
rm $LCBASE/en/LC_MESSAGES/PHPmessages.po-e
rm $SMARTYFILE
rm $TEMPFILE.php
rm $TEMPFILE.tpl
rm $TEMPFILE.smarty

# update the non-en files
for i in $LANGS; do
    echo "merging $i ... "
    LANGDIR="$LCBASE/$i/LC_MESSAGES"
    mv $LANGDIR/messages.po $LANGDIR/messages.po.old
    msgmerge -o $LANGDIR/messages.po \
        $LANGDIR/messages.po.old \
        $LCBASE/en/LC_MESSAGES/messages.po
    echo "compiling $i ... "
    (cd $LANGDIR ; msgfmt -v --check messages.po)
    rm $LANGDIR/messages.po.old
done
