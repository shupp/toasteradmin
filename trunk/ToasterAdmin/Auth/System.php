<?php

/**
 * ToasterAdmin_Auth_SysAdmin 
 * 
 * PHP Version 5
 * 
 * Contains ToasterAdmin_Auth_System class
 * 
 * @package   ToasterAdmin
 * @uses      Framework_Auth_User
 * @abstract
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2007 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://trac.merchbox.com/trac/toasteradmin
 */

/**
 * ToasterAdmin_Auth_SysAdmin 
 * 
 * Check that the user has SysAdmin privileges
 * 
 * @package   ToasterAdmin
 * @uses      Framework_Auth_User
 * @abstract
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2007 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://trac.merchbox.com/trac/toasteradmin
 */
abstract class ToasterAdmin_Auth_SysAdmin extends ToasterAdmin_Common
{
    /**
     * authenticate 
     * 
     * authenticate, then check isSysAdmin
     * 
     * @access public
     * @return void
     */
    public function authenticate()
    {
        if (!parent::authenticate()) {
            return false;
        }
        return $this->user->isSysAdmin();
    }
}
?>
