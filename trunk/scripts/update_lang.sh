#!/bin/sh

MFILE='locale/en/LC_MESSAGES/messages.po'

xgettext -L PHP --keyword=_ public/index.php Includes/*php Modules/*php tpl/* --output=$MFILE
sed -e 's/CHARSET/UTF-8/' $MFILE > $MFILE.new
mv $MFILE.new $MFILE
(cd locale/en/LC_MESSAGES/ ; msgfmt messages.po)
