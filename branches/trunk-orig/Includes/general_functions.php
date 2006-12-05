<?php

/**
 *
 * General Functions
 *
 * These are all the general functions for ToasterAdmin that most 
 * elements need.
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 * @package ToasterAdmin
 * @version 1.0
 *
 */

/**
 * For debugging only
 *
 * This function is just for debugging.  It wraps print_r($val) in pre tags
 * and exits
 */

function pre($val) {
    echo "<pre>\n";
    print_r($val);
    echo "</pre>\n";
    exit;
}

/**
 * checkEmailFormat 
 * 
 * @param mixed $address 
 * @access public
 * @return void
 */
function checkEmailFormat($address) {

    $result = ereg("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$", 
        strtolower($address));
    return $result;

}

/**
 * encryptPass 
 * 
 * @param mixed $pass 
 * @param mixed $key 
 * @access public
 * @return void
 */
function encryptPass($pass, $key) {

    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $cryptpass = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $pass, MCRYPT_MODE_ECB, $iv);
    return $cryptpass;

}

function decryptPass($encryptedpass, $key) {

    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $clearpass = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $encryptedpass, MCRYPT_MODE_ECB, $iv);
    return trim($clearpass);

}

function destruct() {
    global $vp;

    if( is_object($vp) && isset($vp->Socket)) {
        Socket_shutdown( $vp->Socket, 2 );
        Socket_close( $vp->Socket );
        unset( $vp->Socket );
    }
}


?>
