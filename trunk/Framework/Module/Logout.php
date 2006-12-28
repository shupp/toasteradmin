<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Framework_Module_Logout
 *
 * @author      Bill Shupp <hostmaster@shupp.org>
 * @copyright   Bill Shupp <hostmaster@shupp.org>
 * @package     Framework
 * @subpackage  Module
 * @filesource
 */

/**
 * Framework_Module_Logout
 *
 * @author      Bill Shupp <hostmaster@shupp.org>
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
        // Unset logged_in_as so it's not displayed
        unset($this->data['logged_in_as']);
        $form = $this->createLogoutForm();
        $this->setData('LANG_Logout_Confirm', _('Are you sure you want to logout?'));
        $this->setData('QF_Form', $form->toHtml());
    }

    /**
     * logoutNow 
     * 
     * @access public
     * @return void
     */
    public function logoutNow()
    {
        $this->session->destroy();
        header("Location: ./?module=Login");
        return;
    }

    /**
     * createLogoutForm 
     * 
     * @access private
     * @return void
     */
    private function createLogoutForm()
    {
        $form = new HTML_QuickForm('formLogout', 'post', $_SERVER['REQUEST_URI'] . '&event=logoutNow');
        $form->addElement('submit', 'submit', _('Logout'));
        return $form;
    }

}

?>
