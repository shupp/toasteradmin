<?php


/**
 *
 * index.php
 *
 * The only public facing php file.  Sets basic paths, includes initialization
 * files and authorization files, then modules based on URI.
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 * @package ToasterAdmin
 * @tutorial public/tutorial.pkg
 * @version 1.0
 *
 */

// Setup includes
$base_dir = '/Users/shupp/web/mailtool';
/**
 * TADMIN_INC
 *
 * Define Includes directory path
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 */
define('TADMIN_INC', $base_dir . '/Includes');
/**
 * TADMIN_MODULES
 *
 * Define Modules directory path
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 */
define('TADMIN_MODULES', $base_dir . '/Modules');
ini_set('include_path', TADMIN_INC.':'.ini_get('include_path'));

require_once('init.php');


// Verify authentication with all modules but these
if(isset($_REQUEST['module']) 
    && ($_REQUEST['module'] != 'Login')
    && ($_REQUEST['module'] != 'Logout')) {

    require_once('auth.php');

}


// Include Module file it exists
if(isset($_REQUEST['module'])) {
    $mod_file = TADMIN_MODULES . '/' .  $_REQUEST['module'] . '.php';
    if(is_readable($mod_file)) {
        require_once($mod_file);
        exit;
    } else {
        $tpl->set_msg_err(_('Error: module does not exist or is not readable'));
        $tpl->wrap_exit();
    }
}

header('Location: ' . $_SERVER['PHP_SELF'] . '?module=Domains');
exit;

?>
