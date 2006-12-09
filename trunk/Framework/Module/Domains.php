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
        $this->listDomains();
    }


    public function listDomains()
    {
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
        $this->setData('add_domain_url', htmlspecialchars("$base_url?module=Domains&event=add_domain"));
        $this->tplFile = 'listDomains.tpl';
        return;

    }
}
