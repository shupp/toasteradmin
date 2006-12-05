<?php

/**
 *
 * Verify Login for ToasterAdmin
 *
 * This is where authentication is verified after initial login
 * succeeds
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 * @package ToasterAdmin
 * @version 1.0
 *
 */

$tpl->assign('logged_in', false);
if( isset($_SESSION['domain']) &&
    isset($_SESSION['user']) &&
    isset($_SESSION['password'])) {

    // Try and initiate class with that info
    $vp = new vpopmail_admin( $_SESSION['domain'], 
                        $_SESSION['user'], 
                        decryptPass($_SESSION['password'], $mcrypt_key),
                        $server_ip,
                        $server_port);
   if( $vp->Error ) {
        unset($_SESSION['email']);
        $tpl->set_msg_err("Unable to open vpopmaild - {$vp->Error}");
        $tpl->wrap_exit();
    } else {
        $user_info = $vp->GetLoginUser();
        $tpl->assign('email', $_SESSION['email']);

        // Set display stuff
        $tpl->assign('logged_in', false);
    }

} else {

    header('Location: ' . $base_url. '?module=Login');
    exit;

}

?>
