<?php

/**
 * Framework_Module_Domains_Menu 
 * 
 * PHP Version 5.1.0+
 * 
 * @uses       ToasterAdmin_Auth_Domain
 * @category   Main
 * @package    ToasterAdmin
 * @subpackage Module
 * @author     Bill Shupp <hostmaster@shupp.org> 
 * @copyright  2007-2008 Bill Shupp
 * @license    GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link       http://trac.merchbox.com/trac/toasteradmin
 */

/**
 * Framework_Module_Domains_Menu 
 * 
 * Show Domain Menu
 * 
 * @uses       ToasterAdmin_Auth_Domain
 * @category   Mail
 * @package    ToasterAdmin
 * @subpackage Module
 * @author     Bill Shupp <hostmaster@shupp.org> 
 * @copyright  2007-2008 Bill Shupp
 * @license    GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link       http://trac.merchbox.com/trac/toasteradmin
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
        $aurl = './?module=Accounts&domain=' . $this->data['domain'];
        $furl = './?module=Forwards&domain=' . $this->data['domain'];
        $rurl = './?module=Responders&domain=' . $this->data['domain'];
        $this->setData('list_accounts_url', htmlspecialchars($aurl));
        $this->setData('list_forwards_url', htmlspecialchars($furl));
        $this->setData('list_responders_url', htmlspecialchars($rurl));
        $this->setData('domain', $this->domain);

        $this->tplFile = 'domainMenu.tpl';
        return;
    }
}
?>
