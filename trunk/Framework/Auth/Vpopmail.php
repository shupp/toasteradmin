<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

abstract class Framework_Auth_Vpopmail extends Framework_Auth
{
    public function authenticate()
    {
        // Check timeout
        $time = time();
        $lastActionTime = $this->session->__get('lastActionTime');
        $timeLimit = (int)Framework::$site->config->inactiveTimeout;
        if($this->user->debug == 1);
            $this->log->log("timeout info: time: $time, lastActionTime: $lastActionTime, timeLimit: $timeLimit");
        if(($time - $lastActionTime) > $timeLimit) {
            header('Location: ./?module=Login&event=logoutInactive');
            return false;
        }

        // Authenticate
        $encryptedPass = $this->session->__get('password');
        $plainPass =  Framework_User_passEncryption::decryptPass($encryptedPass, (string)Framework::$site->config->mcryptKey);
        if(!PEAR::isError($this->user->authenticate($this->session->__get('email'), $plainPass))) {
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
