<?php

/**
 * Framework_Module_Main 
 * 
 * Main Menu Module
 * 
 * PHP Version 5.1.0+
 * 
 * @uses       ToasterAdmin_Auth_System
 * @category   Mail
 * @package    ToasterAdmin
 * @subpackage Module
 * @author     Bill Shupp <hostmaster@shupp.org> 
 * @copyright  2007-2008 Bill Shupp
 * @license    GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link       http://trac.merchbox.com/trac/toasteradmin
 */

/**
 * Framework_Module_Main 
 * 
 * Main Menu Module
 * 
 * @uses       ToasterAdmin_Auth_System
 * @category   Mail
 * @package    ToasterAdmin
 * @subpackage Module
 * @author     Bill Shupp <hostmaster@shupp.org> 
 * @copyright  2007-2008 Bill Shupp
 * @license    GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link       http://trac.merchbox.com/trac/toasteradmin
 */
class Framework_Module_Main extends ToasterAdmin_Auth_System
{
    /**
     * __default 
     * 
     * Run mainMenu()
     * 
     * @access public
     * @return void
     */
    public function __default()
    {
        return $this->mainMenu();
    }

    /**
     * mainMenu 
     * 
     * Display man menu
     * 
     * @access protected
     * @return void
     */
    protected function mainMenu()
    {
        $this->tplFile = 'menu.tpl';
        return;
    }
}
