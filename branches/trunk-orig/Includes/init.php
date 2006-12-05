<?php

/**
 *
 * ToasterAdmin Initialization
 *
 * Initialize PHP settings, do basic sanity checks, setup presentation layer,
 * set some variables, start session.
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 * @package ToasterAdmin
 * @version 1.0
 *
 */

// Lanuage Stuff
require_once('lang.php');

// PHP INIT/SECURITY STUFF
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('allow_url_fopen', 0);
ini_set('session.use_cookies', 1);
ini_set('error_reporting', E_ALL);

// Check that register_globals is off
if(ini_get('register_globals')) {
    die('Error: register_globals is on. Please read INSTALL regarding PHP Security');
}
if(ini_get('safe_mode')) {
    die('Error: safe_mode is on.  Please read INSTALL regarding PHP Security');
}

session_start();

// Presentation layer
/**
 *  BTS_TEMPLATE_DIR
 *  
 *  Define location of templates
 *  
 *  @author Bill Shupp <hostmaster@shupp.org>
 */
define('BTS_TEMPLATE_DIR', $base_dir . '/tpl');
require_once('presenter.php');
$tpl = new presenter;

// Get configuration settings
require_once('config.php');
require_once('general_functions.php');

// Include vpopmaild stuff
require_once('vpopmail_admin.php');

$base_url = $_SERVER['PHP_SELF'];

$tpl->assign('version', $version);

register_shutdown_function('destruct');

?>
