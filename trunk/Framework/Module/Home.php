<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Framework_Module_Home
 *
 * @author      Bill Shupp <hostmaster@shupp.org>
 * @copyright   Bill Shupp <hostmaster@shupp.org>
 * @package     ToasterAdmin
 * @subpackage  Module
 * @filesource
 */

/**
 * Framework_Module_Home
 *
 * @author      Bill Shupp <hostmaster@shupp.org>
 * @package     ToasterAdmin
 * @subpackage  Module
 */
class Framework_Module_Home extends ToasterAdmin_Common
{
    /**
     * __default 
     * 
     * @access public
     * @return void
     */
    public function __default()
    {
        // Redirect to appropriate page
        if ($this->user->isSysAdmin()) {
            header('Location: ./?module=Domains');
            return;
        }
        $domain = $this->session->domain;
        if ($this->user->isDomainAdmin($domain)) {
            header("Location: ./?module=Domains&class=Menu&domain=" . urlencode($domain));
            return;
        } else {
            header("Location: ./?module=Accounts&class=Modify&domain=" . urlencode($domain) . '&account=' . urlencode($this->session->user) . '&event=modifyAccount');
            return;
        }
    }
}
?>
