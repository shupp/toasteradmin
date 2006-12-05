<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

abstract class Framework_Auth_vpopmail extends Framework_Auth
{
    public function authenticate()
    {
        if($this->user->authenticate($this->session->__get('email'), $this->session->__get('password'))) {
            return true;
        } else {
            header('Location: ./?module=Login');
            return false;
        }
    }
}

?>
