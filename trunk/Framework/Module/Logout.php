<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Framework_Module_Logout
 *
 * @author      Bill Shupp <hostmaster@shupp.org>
 * @copyright   2005-2007 Bill Shupp <hostmaster@shupp.org>
 * @package     ToasterAdmin
 */

/**
 * Framework_Module_Logout
 *
 * @author      Bill Shupp <hostmaster@shupp.org>
 * @package     ToasterAdmin
 */
class Framework_Module_Logout extends Framework_Auth_Vpopmail
{
    /**
     * __default
     *
     * @access      public
     * @return      mixed
     */
    public function __default()
    {
        return $this->logoutNow();
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

}

?>
