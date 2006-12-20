<?php
/**
 * Framework_User_passEncryption 
 * 
 * @uses Framework
 * @uses _User
 * @package 
 * @version $id$
 * @copyright 2006 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @license GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */

/**
 * Framework_User_passEncryption 
 * 
 * @uses Framework
 * @uses _User
 * @package 
 * @version $id$
 * @copyright 2006 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @license GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */
class Framework_User_passEncryption extends Framework_User
{

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

    /**
     * decryptPass 
     * 
     * @param mixed $encryptedpass 
     * @param mixed $key 
     * @access public
     * @return void
     */
    function decryptPass($encryptedpass, $key) {
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $clearpass = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $encryptedpass, MCRYPT_MODE_ECB, $iv);
        return trim($clearpass);
    }
}

?>
