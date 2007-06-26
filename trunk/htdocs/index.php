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

if(!isset($_GET['module'])) {
    header("Location: ./?module=Login");
    exit;
}

define('FRAMEWORK_BASE_PATH',dirname(__FILE__) . '/..');
ini_set('include_path', FRAMEWORK_BASE_PATH . PATH_SEPARATOR . ini_get('include_path'));

require_once('Framework.php');

$result = Framework::start('Default');

if (PEAR::isError($result)) {
    die($result->getMessage());
}

Framework::stop();

?>
