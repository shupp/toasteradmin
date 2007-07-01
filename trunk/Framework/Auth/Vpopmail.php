<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Framework_Auth_Vpopmail 
 * 
 * @uses Framework_Auth
 * @abstract
 * @package ToasterAdmin
 * @copyright 2006-2007 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @license GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */

/**
 * Framework_Auth_Vpopmail 
 * 
 * @uses Framework_Auth
 * @abstract
 * @package ToasterAdmin
 * @copyright 2006-2007 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @license GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */
abstract class Framework_Auth_Vpopmail extends Framework_Auth
{
    public function authenticate()
    {
        // Check timeout
        $time = time();
        $lastActionTime = $this->session->__get('lastActionTime');
        $timeLimit = (int)Framework::$site->config->inactiveTimeout;
        $this->user->recordio("timeout info: time: $time, lastActionTime: $lastActionTime, timeLimit: $timeLimit");
        if (($time - $lastActionTime) > $timeLimit) {
            header('Location: ./?module=Login&event=logoutInactive');
            return false;
        }

        // Authenticate
        $encryptedPass = $this->session->__get('password');
        $plainPass =  Framework_User_passEncryption::decryptPass($encryptedPass, (string)Framework::$site->config->mcryptKey);
        if (!PEAR::isError($this->user->authenticate($this->session->__get('email'), $plainPass))) {
            $this->session->__set('lastActionTime', $time);
            $this->setData('logged_in_as', $this->session->__get('email'));
            $this->setData('LANG_logged_in_as', _('logged in as'));
            $this->setData('LANG_logout', _('logout'));
            return true;
        } else {
            header('Location: ./?module=Login');
            return false;
        }
    }
}

?>
