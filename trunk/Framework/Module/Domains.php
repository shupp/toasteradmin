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
 * @copyright   Joe Stump Bill Shupp <hostmaster@shupp.org>
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
            if($this->user->has_domain_privs($_SESSION['domain'])) {
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
        // Pagintation setup
        $total = $this->user->DomainCount();
        if($this->user->Error) die ("Error: {$this->user->Error}");
        $this->setData('total', $total);
        $this->setData('limit', (integer)Framework::$site->config->maxPerPage);
        if(isset($_REQUEST['start'])) {
            if(!ereg('[^0-9]', $_REQUEST['start'])) $start = $_REQUEST['start'] + 1;
        }
        if(!isset($start)) $start = 1;
        $this->setData('start', $start);

        // Build domain list
        $currentPage = ceil($this->data['start'] / $this->data['limit']);
        $domain_array = $this->user->ListDomains($currentPage,$this->data['limit']);
        $domains = array();
        $count = 0;
        while(list($key,$val) = each($domain_array)) {
            $domains[$count]['name'] = $key;
            $domains[$count]['edit_url'] = htmlspecialchars('./?module=Domains&event=domainMenu&domain=' . $key);
            $domains[$count]['delete_url'] = htmlspecialchars('./?module=Domains&event=del_domain&domain=' . $key);
            $count++;
        }
        $this->setData('domains', $domains);
        $this->setData('add_domain_url', htmlspecialchars("./?module=Domains&event=add_domain"));
        $this->tplFile = 'listDomains.tpl';
        return;

    }


    function domainMenu()
    {
        // Make sure the domain was supplied
        if(!isset($_REQUEST['domain'])) {
            PEAR::raiseError(_("Error: no domain supplied"));
            return;
        }

        if(!$this->user->isDomainAdmin($_REQUEST['domain'])) {
            PEAR::raiseError(_('Error: you do not have edit privileges on domain ') . $_REQUEST['domain']);
            return;
        }
        // Setup URLs
        $this->setData('domain', $_REQUEST['domain']);
        $this->setData('list_accounts_url', htmlspecialchars('./?module=Accounts&domain=' . $_REQUEST['domain']));
        $this->setData('list_forwards_url', htmlspecialchars('./?module=Forwards&domain=' . $_REQUEST['domain']));
        $this->setData('list_responders_url', htmlspecialchars('./?module=Responders&domain=' . $_REQUEST['domain']));
        $this->setData('list_lists_url', htmlspecialchars('./?module=Lists&domain=' . $_REQUEST['domain']));
        $this->tplFile = 'domainMenu.tpl';
        return;
    }

}
    
?>
