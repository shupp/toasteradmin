<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Framework_Module_Login
 *
 * @author      Bill Shupp <hostmaster@shupp.org>
 * @copyright   Bill Shupp <hostmaster@shupp.org>
 * @package     ToasterAdmin
 * @subpackage  Module
 * @filesource
 */

/**
 * Framework_Module_Login
 *
 * @author      Bill Shupp <hostmaster@shupp.org>
 * @package     ToasterAdmin
 * @subpackage  Module
 */
class Framework_Module_Login extends Framework_Auth_No
{
    /**
     * __default
     *
     * @access      public
     * @return      mixed
     */
    public function __default()
    {
        $form = $this->createLoginForm();
        $this->setData('QF_Form', $form->toHtml());
    }

    public function loginNow()
    {
        $form = $this->createLoginForm();
        if ($form->validate()) {
            $result = $this->user->authenticate($_POST['email'], $_POST['password']);
            if(PEAR::isError($result)) {
                $this->setData('loginError', $result->getMessage());
                $this->setData('QF_Form', $form->toHtml());
                $this->session->__set('email', null);
                $this->session->__set('password',  null);
                return;
            }
            $emailArray = explode('@', $_POST['email']);
            $this->session->__set('user', $emailArray[0]);
            $this->session->__set('domain', $emailArray[1]);
            $this->session->__set('email', $_POST['email']);
            $this->session->__set('password', Framework_User_passEncryption::encryptPass($_POST['password'], 
                (string)Framework::$site->config->mcryptKey));
            $this->session->__set('lastActionTime', time());
            header("Location: ./index.php?module=Domains");
            return;
        } else {
            $this->setData('QF_Form', $form->toHtml());
        }
    }

    private function createLoginForm()
    {
        $form = new HTML_QuickForm('formLogin', 'post', $_SERVER['REQUEST_URI'] . '&event=loginNow');

        $form->addElement('header', 'MyHeader', _('Login'));
        $form->addElement('text', 'email', _('Email'));
        $form->addElement('password', 'password', _('Password'));
        $form->addElement('submit', 'submit', _('Login'));

        $form->addRule('email', _('Please enter your email address'), 'required', null, 'client');
        $form->addRule('email', _('Please enter a valid email address'), 'email', null, 'client');
        $form->addRule('password', _('Please enter your password'), 'required', null, 'client');
        $form->applyFilter('__ALL__', 'trim');

        return $form;
    }

    function logoutNow() {
        $this->session->destroy();
        $this->setData('message', _('Logged out successfully'));
        return $this->__default();
    }

    function logoutInactive() {
        $this->session->destroy();
        $this->setData('message', _('You have been logged out automatically for inactivity'));
        return $this->__default();
    }

}

?>
