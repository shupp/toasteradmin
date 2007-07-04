<?php

/**
 * index.php
 *
 * An basic controller for Framework.
 *
 * @author Joe Stump <joe@joestump.net>
 * @author Bill Shupp <hostmaster@shupp.org>
 * @filesource
 */

ini_set('error_reporting', E_ALL & ~E_NOTICE);

if(!isset($_GET['module'])) {
    header("Location: ./?module=Login");
    exit;
}

define('FRAMEWORK_BASE_PATH',dirname(__FILE__) . '/..');
$ta_include_path = FRAMEWORK_BASE_PATH;
// If you are running a local PEAR install, uncomment the next line
// and edit it accordingly

// $ta_include_path .= PATH_SEPARATOR . '/Users/shupp/pear/lib';

ini_set('include_path', $ta_include_path . PATH_SEPARATOR . ini_get('include_path'));

require_once('Framework.php');

$result = Framework::start('Default');

if (PEAR::isError($result)) {
    die($result->getMessage());
}

Framework::stop();

?>
