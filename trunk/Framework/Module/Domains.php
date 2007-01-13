<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Framework_Module_Domains
 *
 * This module is for viewing and editing vpopmail domains
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 * @package TA_Modules
 * @version 1.0
 *
 *
 * @author      Bill Shupp <hostmaster@shupp.org>
 * @copyright   Bill Shupp <hostmaster@shupp.org>
 * @package     TA_Modules
 * @version     1.0
 * @filesource
 */

/**
 * Framework_Module_Domains
 *
 * This module is for viewing and editing vpopmail domains
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 * @package TA_Modules
 * @version 1.0
 *
 */
class Framework_Module_Domains extends Framework_Auth_vpopmail
{
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
     * accessDirector 
     * 
     * @access public
     * @return void
     */
    public function accessDirector()
    {
        if(!$this->user->isSysAdmin()) {
            // Redirect to appropriate page
            if($this->user->isDomainAdmin($_SESSION['domain'])) {
                header("Location: ./?module=Domains&event=domainMenu&domain=" . urlencode($_SESSION['domain']));
                return;
            } else {
                header("Location: ./?module=Accounts&domain=" . urlencode($_SESSION['domain']) . '&account=' . urlencode($_SESSION['user']) . '&event=modify');
                return;
            }
        }
    }


    /**
     * listDomains 
     * 
     * @access public
     * @return void
     */
    public function listDomains()
    {
        $this->accessDirector();
        // Pagination setup
        $total = $this->user->DomainCount();
        if($this->user->Error) die ("Error: {$this->user->Error}");
        $this->setData('total', $total);
        $this->setData('limit', (integer)Framework::$site->config->maxPerPage);
        if(isset($_REQUEST['start']) && !ereg('[^0-9]', $_REQUEST['start'])) {
            if($_REQUEST['start'] == 0) {
                $start = 1;
            } else {
                $start = $_REQUEST['start'];
            }
        }
        if(!isset($start)) $start = 1;
        $this->setData('start', $start);
        $this->setData('currentPage', ceil($this->data['start'] / $this->data['limit']));
        $this->setData('totalPages', ceil($this->data['total'] / $this->data['limit']));

        // Build domain list
        $domain_array = $this->user->ListDomains($this->data['currentPage'],$this->data['limit']);
        $domains = array();
        $count = 0;
        while(list($key,$val) = each($domain_array)) {
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
        if($domain == null) {
            if(empty($_REQUEST['domain']))
                return PEAR::raiseError(_("Error: no domain supplied"));
            $domain = $_REQUEST['domain'];
        }

        if(!$this->user->isDomainAdmin($domain)) {
            return PEAR::raiseError(_('Error: you do not have edit privileges on domain ') . $domain);
        }

        if($this->user->isSysAdmin()) $this->setData('isSysAdmin', 1);
        // Setup URLs
        $this->setData('domain', $domain);
        $this->setData('list_accounts_url', htmlspecialchars('./?module=Accounts&domain=' . $this->data['domain']));
        $this->setData('list_forwards_url', htmlspecialchars('./?module=Forwards&domain=' . $this->data['domain']));
        $this->setData('list_responders_url', htmlspecialchars('./?module=Responders&domain=' . $this->data['domain']));
        $this->setData('list_lists_url', htmlspecialchars('./?module=Lists&domain=' . $this->data['domain']));

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
        if(!$this->user->isSysAdmin()) {
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
        if(!$this->user->isSysAdmin()) {
            return PEAR::raiseError(_('Error: you do not have add domain privileges'));
        }

        $form = $this->addDomainForm();
        if(!$form->validate()) {
            $this->addDomain();
            return;
        }

        // Add domain
        $this->user->AddDomain($_REQUEST['domain'], $_REQUEST['password']);
        if($this->user->Error) {
            return PEAR::raiseError(_("Error: ") . $this->user->Error);
        }
        // $tpl->set_msg(_("Domain added successfully"));
        header("Location: ./?module=Domains&event=domainMenu&domain=" . $_REQUEST['domain']);
        return;
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
        if($domain == null) {
            if(empty($_REQUEST['domain']))
                return PEAR::raiseError(_("Error: no domain supplied"));
            $domain = $_REQUEST['domain'];
        }

        if(!$this->user->isDomainAdmin($domain)) {
            return PEAR::raiseError(_('Error: you do not have edit privileges on domain ') . $domain);
        }

        $this->setData('domain', $_REQUEST['domain']);
        $this->setData('delete_url', htmlspecialchars("./?module=Domains&event=delDomainNow&domain=" . $domain));
        $this->setData('cancel_url', htmlspecialchars("./?module=Domains&event=cancelDelDomain"));
        $this->tplFile = 'domainConfirmDelete.tpl';

    }

    function delDomainNow($domain = null)
    {
        // Make sure the domain was supplied
        if($domain == null) {
            if(empty($_REQUEST['domain']))
                return PEAR::raiseError(_("Error: no domain supplied"));
            $domain = $_REQUEST['domain'];
        }

        if(!$this->user->isDomainAdmin($domain)) {
            return PEAR::raiseError(_('Error: you do not have edit privileges on domain ') . $domain);
        }

        // Delete domain
        $this->user->DelDomain($domain);
        if($this->user->Error) {
            return PEAR::raiseError(_("Error: ") . $this->user->Error);
        }
        // $tpl->set_msg(_("Domain deleted successfully"));
        header("Location: ./?module=Domains");
        return;
    }

}
    
?>
