<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Framework_Module_Domains
 *
 * This module is for viewing and editing vpopmail domains
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 * @copyright   Bill Shupp 2006-2007
 * @package ToasterAdmin
 * @version 1.0
 * @filesource
 */

/**
 * Framework_Module_Domains
 *
 * This module is for viewing and editing vpopmail domains
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 * @package ToasterAdmin
 * @version 1.0
 *
 */
class Framework_Module_Domains extends ToasterAdmin_Common
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->user->isSysAdmin()) {
            // Should not use $_GET['event'] here
            if (!isset($_GET['event'] || $_GET['event'] != 'domainMenu') {
                throw new  Framework_Exception('Error: not enough permissions');
            }
        }
    }

    /**
     * __default
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
     * @access public
     * @return void
     */
    public function listDomains()
    {
        // Pagination setup
        $total = $this->user->domainCount();
        $this->paginate($total);

        // Build domain list
        $domain_array = $this->user->listDomains($this->data['currentPage'],$this->data['limit']);
        $domains = array();
        $count = 0;
        while (list($key,$val) = each($domain_array)) {
            $domains[$count]['name'] = $key;
            $domains[$count]['edit_url'] = htmlspecialchars('./?module=Domains&event=domainMenu&domain=' . $key);
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

    function domainMenu($domain = null)
    {
        // Make sure the domain was supplied
        if ($domain == null) {
            if (empty($_REQUEST['domain']))
                return PEAR::raiseError(_("Error: no domain supplied"));
            $domain = $_REQUEST['domain'];
        }

        if (!$this->user->isDomainAdmin($domain)) {
            return PEAR::raiseError(_('Error: you do not have edit privileges on domain ') . $domain);
        }

        if ($this->user->isSysAdmin()) {
            $this->setData('isSysAdmin', 1);
        }
        // Setup URLs
        $this->setData('domain', $domain);
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

    function addDomain()
    {
        if (!$this->user->isSysAdmin()) {
            return PEAR::raiseError(_('Error: you do not have add domain privileges'));
        }
        // Create form

        $form = $this->addDomainForm();
        $this->setData('addDomainForm', $form->toHtml());
        $this->setData('LANG_Main_Menu', _('Main Menu'));
        $this->tplFile = 'addDomain.tpl';
        return;
    }

    function addDomainNow()
    {
        if (!$this->user->isSysAdmin()) {
            return PEAR::raiseError(_('Error: you do not have add domain privileges'));
        }

        $form = $this->addDomainForm();
        if (!$form->validate()) {
            $this->addDomain();
            return;
        }

        // Add domain
        $result = $this->user->AddDomain($_REQUEST['domain'], $_REQUEST['password']);
        if (PEAR::isError($result)) {
            $this->setData('message', _("Error: ") . $result->getMessage());
            return $this->addDomain();
        }
        $this->setData('message', _("Domain added successfully"));
        return $this->domainMenu();
    }

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

    function delDomain($domain = null)
    {
        // Make sure the domain was supplied
        if ($domain == null) {
            if (empty($_REQUEST['domain']))
                return PEAR::raiseError(_("Error: no domain supplied"));
            $domain = $_REQUEST['domain'];
        }

        if (!$this->user->isDomainAdmin($domain)) {
            return PEAR::raiseError(_('Error: you do not have edit privileges on domain ') . $domain);
        }

        $this->setData('LANG_Are_you_sure_you_want_to_delete_this_domain', _("Are you sure you want to delete this domain?"));
        $this->setData('LANG_cancel', _("cancel"));
        $this->setData('LANG_delete', _("delete"));

        $this->setData('domain', $_REQUEST['domain']);
        $this->setData('delete_url', htmlspecialchars("./?module=Domains&event=delDomainNow&domain=" . $domain));
        $this->setData('cancel_url', htmlspecialchars("./?module=Domains&event=cancelDelDomain"));
        $this->tplFile = 'domainConfirmDelete.tpl';

    }

    function delDomainNow($domain = null)
    {
        // Make sure the domain was supplied
        if ($domain == null) {
            if (empty($_REQUEST['domain']))
                return PEAR::raiseError(_("Error: no domain supplied"));
            $domain = $_REQUEST['domain'];
        }

        if (!$this->user->isDomainAdmin($domain)) {
            return PEAR::raiseError(_('Error: you do not have edit privileges on domain ') . $domain);
        }

        // Delete domain
        $result = $this->user->delDomain($domain);
        if (PEAR::isError($result)) {
            $this->setData('message', _("Error: ") . $result->getMessage());
            return $this->listDomains();
        }
        $this->setData('message', _("Domain deleted successfully"));
        return $this->listDomains();
    }

    function cancelDelDomain() {
        $this->setData('message', _("Domain deletion canceled"));
        return $this->listDomains();
    }

}
    
?>
