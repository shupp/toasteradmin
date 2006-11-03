<?php

/**
 *
 * Login for ToasterAdmin
 *
 * This is where initial authentication happens
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 * @package TA_Modules
 * @version 1.0
 *
 */


// Use login info (should check it first
// Try and initiate class with that info
if(isset($_POST['event']) && $_REQUEST['event'] == 'login_now') {

    if(
        !isset($_POST['email_address']) ||
        !isset($_POST['password'])) {

        $tpl->set_msg_err(_('Error: missing fields'));
        $tpl->wrap_exit('login.tpl');

    }

    if(!checkEmailFormat($_POST['email_address'])) {

        $tpl->set_msg_err(_('Error: invalid email address format'));
        $tpl->wrap_exit('login.tpl');

    }
    $email_array = explode('@', $_POST['email_address']);
    $login_user = $email_array[0];
    $login_domain = $email_array[1];
    $vp = new vpopmail_admin( $login_domain, 
                        $login_user, 
                        $_POST['password'],
                        $server_ip,
                        $server_port);
    if( $vp->Error ) {
        unset($_SESSION['user']);
        unset($_SESSION['domain']);
        unset($_SESSION['password']);
        unset($_SESSION['email']);
        $tpl->set_msg("Unable to open vpopmaild - {$vp->Error}");
        $tpl->wrap_exit();
    } else {
        $_SESSION['user'] = $login_user;
        $_SESSION['domain'] = $login_domain;
        $_SESSION['password'] = encryptPass($_POST['password'], $mcrypt_key);
        $_SESSION['email'] = $_SESSION['user'].'@'.$_SESSION['domain'];
        header("Location: " . $_SERVER['PHP_SELF'] . '?module=Domains');
        exit;
    }
}


// Else show login screen
$tpl->wrap_exit('login.tpl');

?>
