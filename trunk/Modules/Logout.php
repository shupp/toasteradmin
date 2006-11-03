<?php

/**
 *
 * Logout Module
 *
 * This module is for logging out of ToasterAdmin
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 * @package TA_Modules
 * @version 1.0
 *
 */

$_SESSION = array();
session_destroy();
setcookie("PHPSESSID", "", 0, "/");


$tpl->assign('login_url', $_SERVER['PHP_SELF'] . '?module=Login');
// Else show login screen
$tpl->wrap_exit('logout.tpl');

?>
