<?php

/**
 * index.php
 *
 * An example controller to use with Framework. Copy this file into your 
 * website's document root to use.
 *
 * @author Joe Stump <joe@joestump.net>
 * @filesource
 */

/**
 * FRAMEWORK_BASE_PATH
 *
 * Dynamically figure out where in the filesystem we are located.
 *
 * @author Joe Stump <joe@joestump.net>
 * @global string FRAMEWORK_BASE_PATH Absolute path to our framework
 */

if(!isset($_GET['module'])) {
    header("Location: ./?module=Login");
    exit;
}


define('FRAMEWORK_BASE_PATH',dirname(__FILE__) . '/..');
ini_set('include_path', FRAMEWORK_BASE_PATH . PATH_SEPARATOR . ini_get('include_path'));
// ini_set('session.auto_start', 1);

require_once('Framework.php');

// Load the Framework_Site_Example class and initialize modules, run events, 
// etc. You could create an array based on $_SERVER['SERVER_NAME'] that loads
// up different site drivers depending on the server name. For instance, 
// www.foo.com and foo.com load up Framework_Site_Foo, while www.bar.com, 
// www.baz.com, baz.com, and bar.com load up Bar (Framework_Site_Bar).
$result = Framework::start('toasteradmin');

// If a PEAR error is returned usually something catastrophic happend like an
// event returning a PEAR_Error or throwing an exception of some sort.
if (PEAR::isError($result)) {
    die($result->getMessage());
}

// Run shutdown functions and stop the Framework
Framework::stop();

?>
