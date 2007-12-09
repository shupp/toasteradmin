<?php

/**
 * Framework_Module_Domains_Menu 
 * 
 * @uses      ToasterAdmin_Auth_Domain
 * @package   ToasterAdmin
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2007 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://trac.merchbox.com/trac/toasteradmin
 */

/**
 * Framework_Module_Domains_Menu 
 * 
 * Show Domain Menu
 * 
 * @uses      ToasterAdmin_Auth_Domain
 * @package   ToasterAdmin
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2007 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://trac.merchbox.com/trac/toasteradmin
 */
class Framework_Module_Domains_Menu extends ToasterAdmin_Auth_Domain
{
    /**
     * __default 
     * 
     * Show domain menu
     * 
     * @access protected
     * @return void
     */
    function __default()
    {
        // Make sure the domain was supplied
        if ($this->domain == null) {
            throw new Framework_Exception (_("Error: no domain supplied"));
        }

        // Display success message
        if (isset($_REQUEST['success'])) {
            $this->setData('message', _("Domain added successfully"));
        }

        // Should be show a main menu link?
        if ($this->user->isSysAdmin()) {
            $this->setData('isSysAdmin', 1);
        }
        // Setup URLs
        $this->setData('domain', $this->domain);
        $this->setData('list_accounts_url', htmlspecialchars('./?module=Accounts&domain=' . $this->data['domain']));
        $this->setData('list_forwards_url', htmlspecialchars('./?module=Forwards&domain=' . $this->data['domain']));
        $this->setData('list_responders_url', htmlspecialchars('./?module=Responders&domain=' . $this->data['domain']));
        // $this->setData('list_lists_url', htmlspecialchars('./?module=Lists&domain=' . $this->data['domain']));

        // Language
        $this->setData('LANG_Email_Accounts', _('Email Accounts'));
        $this->setData('LANG_Forwards', _('Forwards'));
        $this->setData('LANG_Auto_Responders', _('Auto-Responders'));
        $this->setData('LANG_Mailing_Lists', _('Mailing Lists'));
        $this->setData('LANG_Main_Menu', _('Main Menu'));

        $this->tplFile = 'domainMenu.tpl';
        return;
    }
}
?>
