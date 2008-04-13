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

// ini_set('error_reporting', E_ALL & ~E_NOTICE);
ini_set('error_reporting', E_ALL);

if(!isset($_GET['module'])) {
    header("Location: ./?module=Login");
    exit;
}

define('FRAMEWORK_BASE_PATH',dirname(__FILE__) . '/..');
$ta_include_path = FRAMEWORK_BASE_PATH;

// Local copy of PEAR
$ta_include_path .= PATH_SEPARATOR . FRAMEWORK_BASE_PATH . '/PEAR';

ini_set('include_path', $ta_include_path . PATH_SEPARATOR . ini_get('include_path'));

try {
    require_once 'Framework.php';

    $controller = 'ToasterAdmin';
    if (isset($_GET['Controller'])) {
        $controller = $_GET['Controller'];
    }
    try {
        Framework::start('Default', $controller);
    } catch (Framework_Exception $e) {
        switch ($e->getCode()) {
        case FRAMEWORK_ERROR_AUTH:
            header('Location: ./?module=Login');
            break;
        default:
            die($e->getMessage());
        }
    }

    // Run shutdown functions and stop the Framework
    Framework::stop();
} catch (Exception $error) {
    echo $error->getMessage();
}

?>
