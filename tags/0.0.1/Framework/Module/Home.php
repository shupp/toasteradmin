<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Framework_Module_Home
 *
 * PHP Version 5.1.0+
 *
 * @category   Mail
 * @package    ToasterAdmin
 * @subpackage Module
 * @author     Bill Shupp <hostmaster@shupp.org>
 * @copyright  2007-2008 Bill Shupp <hostmaster@shupp.org>
 * @license    GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link       http://trac.merchbox.com/trac/toasteradmin
 */

/**
 * Framework_Module_Home
 *
 * Simple redirect home class based on privileges
 *
 * @category   Mail
 * @package    ToasterAdmin
 * @subpackage Module
 * @author     Bill Shupp <hostmaster@shupp.org>
 * @copyright  2007-2008 Bill Shupp <hostmaster@shupp.org>
 * @license    GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link       http://trac.merchbox.com/trac/toasteradmin
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
            header('Location: ./?module=Main');
            return;
        }
        $domain = $this->session->domain;
        if ($this->user->isDomainAdmin($domain)) {
            header("Location: ./?module=Domains&class=Menu&domain="
               . urlencode($domain));
            return;
        } else {
            header("Location: ./?module=Accounts&class=Modify&domain="
               . urlencode($domain) . '&account=' . urlencode($this->session->user)
               . '&event=modifyAccount');
            return;
        }
    }
}
?>
