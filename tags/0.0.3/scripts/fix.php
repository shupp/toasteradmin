<?php

$tr = file('messages.po');
$en = file('../../en/LC_MESSAGES/messages.po');
$total = count($tr);

for ($c = 0; $c < $total; $c ++) {
    if (!preg_match('/^msgid /', $tr[$c])) {
        continue;
    }
    $tr[$c + 1] = preg_replace('/^msgid (.*)$/', 'msgstr \\1', $tr[$c]);
    $tr[$c]     = $en[$c];
}

reset($tr);

$fp = fopen('new.po', 'w');
foreach ($tr as $line) {
    fwrite($fp, $line);
}
fclose($fp);
// print_r($tr);
?>
