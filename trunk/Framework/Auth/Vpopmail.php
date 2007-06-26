<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

abstract class Framework_Auth_Vpopmail extends Framework_Auth
{
    public function authenticate()
    {
        $encryptedPass = $this->session->__get('password');
        $plainPass =  Framework_User_passEncryption::decryptPass($encryptedPass, (string)Framework::$site->config->mcryptKey);
        if($this->user->authenticate($this->session->__get('email'), $plainPass)) {
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
