<?php

/**
 * ToasterAdmin_Auth_Domain 
 * 
 * PHP Version 5
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
 * ToasterAdmin_Auth_Domain 
 * 
 * Check for Domain privileges
 * 
 * @package   ToasterAdmin
 * @uses      Framework_Auth_User
 * @abstract
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2007 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://trac.merchbox.com/trac/toasteradmin
 */
abstract class ToasterAdmin_Auth_Domain extends ToasterAdmin_Common
{
    /**
     * authenticate 
     * 
     * authenticate, then check for domain level privileges
     * 
     * @access public
     * @return void
     */
    public function authenticate()
    {
        if (!parent::authenticate()) {
            return false;
        }
        if ($this->user->isSysAdmin()) {
            return true;
        }
        $domain = !is_null($this->domain) ? 
            $this->domain : $this->user->loginUser['domain'];
        return $this->user->isDomainAdmin($domain);
    }
}
?>
