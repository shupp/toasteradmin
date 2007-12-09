<?php

/**
 * Framework_Module_Main 
 * 
 * Main Menu Module
 * 
 * PHP Version 5
 * 
 * @uses      ToasterAdmin_Auth_System
 * @package   ToasterAdmin
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2007 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://trac.merchbox.com/trac/toasteradmin
 */

/**
 * Framework_Module_Main 
 * 
 * Main Menu Module
 * 
 * @uses      ToasterAdmin_Auth_System
 * @package   ToasterAdmin
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2007 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://trac.merchbox.com/trac/toasteradmin
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
     * @access public
     * @return void
     */
    public function mainMenu()
    {
        // Language stuff
        $this->setData('LANG_Main_Menu', _('Main Menu'));
        $this->setData('LANG_List_Domains', _('List Domains'));
        $this->setData('LANG_Add_Domain', _('Add Domain'));
        $this->setData('LANG_Add_Alias_Domain', _('Add Alias Domain'));
        $this->setData('LANG_Find_Domain', _('Find Domain'));
        $this->setData('LANG_Delete_Domain', _('Delete Domain'));
        $this->setData('LANG_Modify_Limits', _('Modify Limits'));
        $this->tplFile = 'menu.tpl';
        return;
    }
}
