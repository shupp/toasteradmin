<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Framework_Module_Welcome
 *
 * @author      Joe Stump <joe@joestump.net>
 * @copyright   Joe Stump <joe@joestump.net> 
 * @package     Framework
 * @subpackage  Module
 * @filesource
 */

/**
 * Framework_Module_Welcome
 *
 * @author      Joe Stump <joe@joestump.net>
 * @package     Framework
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
        if(!empty($_POST['email']) && !empty($_POST['password'])) {

            if($this->user->authenticate($_POST['email'], $_POST['password'])) {
                $this->session->email = $_POST['email'];
                $this->session->password = $_POST['password'];
                header("Location: ./index.php?module=Welcome");
            } else {
                $this->session->email = null;
                $this->session->password = null;
                header("Location: ./index.php?module=Login");
            }
        } else {
            header("Location: ./index.php?module=Login");
        }
    }

    private function createLoginForm()
    {
        $form = new HTML_QuickForm('formLogin', 'post', $_SERVER['REQUEST_URI'] . '&event=loginNow');

        $form->addElement('header', 'MyHeader', 'Login');
        $form->addElement('text', 'email', 'Email');
        $form->addElement('password', 'password', 'Password');
        $form->addElement('submit', 'submit', 'Login');

        $form->addRule('email', 'Please enter your email address', 'required', null, 'client');
        $form->addRule('email', 'Please enter a valid email address', 'email', null, 'client');
        $form->addRule('password', 'Please enter your password', 'required', null, 'client');
        $form->applyFilter('__ALL__', 'trim');

        if ($form->validate()) {
            // $form->freeze();
            // $data = $form->exportValues();
            // $user = self::getUser($data['username']);
        }
        return $form;
    }

}

?>
