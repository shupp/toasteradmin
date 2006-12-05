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
class Framework_Module_Logout extends Framework_Auth_vpopmail
{
    /**
     * __default
     *
     * @access      public
     * @return      mixed
     */
    public function __default()
    {
        $form = $this->createLogoutForm();
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

    public function logoutNow()
    {
        $this->session->destroy();
        header("Location: ./?module=Login");
        return;
    }

    private function createLogoutForm()
    {
        $form = new HTML_QuickForm('formLogout', 'post', $_SERVER['REQUEST_URI'] . '&event=logoutNow');
        $form->addElement('submit', 'submit', 'Logout');
        return $form;
    }

}

?>
