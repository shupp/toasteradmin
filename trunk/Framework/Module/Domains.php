<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Framework_Module_Domains 
 * 
 * This module is for viewing and editing vpopmail domains
 * 
 * @uses      ToasterAdmin_Auth_System
 * @package   ToasterAdmin
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2007 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://trac.merchbox.com/trac/toasteradmin
 */


/**
 * Framework_Module_Domains 
 * 
 * This module is for viewing and editing vpopmail domains
 * 
 * @uses      ToasterAdmin_Auth_System
 * @package   ToasterAdmin
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2007 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://trac.merchbox.com/trac/toasteradmin
 */
class Framework_Module_Domains extends ToasterAdmin_Auth_System
{

    /**
     * __default
     *
     * Run listDomains()
     *
     * @access      public
     * @return      mixed
     */
    public function __default()
    {
        $this->listDomains();
    }

    /**
     * listDomains 
     * 
     * List Domains
     * 
     * @access public
     * @return void
     */
    public function listDomains()
    {
        // Pagination setup
        $total = $this->user->domainCount();
        $this->paginate($total);

        // Build domain list
        $domainArray = $this->user->listDomains($this->data['currentPage'],$this->data['limit']);
        $domains = array();
        $count = 0;
        while (list($key,$val) = each($domainArray)) {
            $domains[$count]['name'] = $key;
            $domains[$count]['limits_url'] = htmlspecialchars('./?module=Main&class=Limits&domain=' . $key);
            $domains[$count]['edit_url'] = htmlspecialchars('./?module=Domains&class=Menu&domain=' . $key);
            $domains[$count]['delete_url'] = htmlspecialchars('./?module=Domains&event=delDomain&domain=' . $key);
            $count++;
        }
        $this->setData('domains', $domains);
        $this->setData('add_domain_url', htmlspecialchars("./?module=Domains&event=addDomain"));

        // Language
        $this->setData('LANG_Add_Domain', _('Add Domain'));
        $this->setData('LANG_Domains_Page', _('Domains: Page'));
        $this->setData('LANG_of', _('of'));
        $this->setData('LANG_edit_domain', _('edit domain'));
        $this->setData('LANG_delete_domain', _('delete domain'));

        $this->tplFile = 'listDomains.tpl';
        return;
    }

    /**
     * addDomain 
     * 
     * Display add domain form
     * 
     * @access public
     * @return void
     */
    public function addDomain()
    {
        $form = $this->addDomainForm();
        $this->_renderForm($form);
        return;
    }

    /**
     * addDomainNow 
     * 
     * Add Domain
     * 
     * @access public
     * @return void
     */
    public function addDomainNow()
    {
        $form = $this->addDomainForm();
        if (!$form->validate()) {
            $this->_renderForm($form);
            return;
        }

        // Add domain
        try {
            $result = $this->user->addDomain($form->getElementValue('domain'), $form->getElementValue('password'));
        } catch (Net_Vpopmaild_Exception $e) {
            $this->setData('message', _("Error: ") . $e->getMessage());
            return $this->addDomain();
        }
        header('Location: ./?module=Domains&class=Menu&success&domain=' . $form->getElementValue('domain'));
        return;
    }

    /**
     * addDomainForm 
     * 
     * create add domain form
     * 
     * @access private
     * @return void
     */
    private function addDomainForm()
    {
        $form = new HTML_QuickForm('formLogin', 'post', './?module=Domains&event=addDomainNow');

        $form->addElement('text', 'domain', _('Domain'));
        $form->addElement('password', 'password', _('Password'));
        $form->addElement('submit', 'submit', _('Add Domain'));

        $form->addRule('domain', _('Please a domain name'), 'required', null, 'client');
        $form->addRule('password', _('Please enter a postmaster password'), 'required', null, 'client');
        $form->applyFilter('__ALL__', 'trim');

        return $form;
    }

    /**
     * delDomain 
     * 
     * Show delete domain link
     * 
     * @access public
     * @return void
     */
    public function delDomain()
    {
        // Make sure the domain was supplied
        if ($this->domain == null) {
            throw new Framework_Exception (_("Error: no domain supplied"));
        }

        $this->setData('LANG_Are_you_sure_you_want_to_delete_this_domain', _("Are you sure you want to delete this domain?"));
        $this->setData('LANG_cancel', _("cancel"));
        $this->setData('LANG_delete', _("delete"));

        $this->setData('delete_url', htmlspecialchars("./?module=Domains&event=delDomainNow&domain=" . $this->domain));
        $this->setData('cancel_url', htmlspecialchars("./?module=Domains&event=cancelDelDomain"));
        $this->tplFile = 'domainConfirmDelete.tpl';

    }

    /**
     * delDomainNow 
     * 
     * Delete domain
     * 
     * @access public
     * @return void
     */
    public function delDomainNow()
    {
        // Make sure the domain was supplied
        if ($this->domain == null) {
            throw new Framework_Exception (_("Error: no domain supplied"));
        }

        // Delete domain
        try {
            $result = $this->user->delDomain($this->domain);
        } catch (Exception $e) {
            $this->setData('message', _("Error: ") . $e->getMessage());
            return $this->listDomains();
        }
        $this->setData('message', _("Domain deleted successfully"));
        return $this->listDomains();
    }

    /**
     * cancelDelDomain 
     * 
     * Cancel domain deletion
     * 
     * @access public
     * @return void
     */
    public function cancelDelDomain() {
        $this->setData('message', _("Domain deletion canceled"));
        return $this->listDomains();
    }

    private function _renderForm($form)
    {
        $this->tplFile = 'addDomain.tpl';
        $this->setData('addDomainForm', $form->toHtml());
        $this->setData('LANG_Main_Menu', _('Main Menu'));
        $this->setData('LANG_List_Domains', _('List Domains'));
    }

}
    
?>
