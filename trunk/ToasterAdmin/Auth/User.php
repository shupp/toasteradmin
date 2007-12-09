<?php

/**
 * ToasterAdmin_Auth_User 
 * 
 * PHP Version 5
 * 
 * Contains ToasterAdmin_Auth_User class
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
 * ToasterAdmin_Auth_User 
 * 
 * Check that the user has User privileges
 * 
 * @package   ToasterAdmin
 * @uses      Framework_Auth_User
 * @abstract
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2007 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://trac.merchbox.com/trac/toasteradmin
 */
abstract class ToasterAdmin_Auth_User extends ToasterAdmin_Common
{
    /**
     * authenticate 
     * 
     * authenticate, then check for isUserAdmin privileges
     * 
     * @access public
     * @return void
     */
    public function authenticate()
    {
        if (!parent::authenticate()) {
            return false;
        }
        $domain = !is_null($this->domain) ? 
            $this->domain : $this->user->loginUser['domain'];
        $name   = !is_null($_REQUEST['name']) ? 
            $this->loginUser['name'] : $this->user->loginUser['name'];
        return $this->user->isUserAdmin($name, $domain);
    }
}
?>
