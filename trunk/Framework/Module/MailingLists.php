<?php
/**
 * Framework_Module_MailingLists 
 * 
 * PHP Version 5.1.0+
 * 
 * @uses       Framework_Auth_User
 * @category   Mail
 * @package    ToasterAdmin
 * @subpackage Module
 * @author     Bill Shupp <hostmaster@shupp.org> 
 * @copyright  2007-2008 Bill Shupp
 * @license    GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link       http://trac.merchbox.com/trac/toasteradmin
 */

/**
 * Framework_Module_MailingLists 
 * 
 * Management of ezmlm-idx lists through vpopmaild
 * 
 * @uses       Framework_Auth_User
 * @category   Mail
 * @package    ToasterAdmin
 * @subpackage Module
 * @author     Bill Shupp <hostmaster@shupp.org> 
 * @copyright  2007-2008 Bill Shupp
 * @license    GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link       http://trac.merchbox.com/trac/toasteradmin
 */
class Framework_Module_MailingLists extends ToasterAdmin_Auth_Domain
{
    /**
     * __construct 
     * 
     * Check that a domain was supplied.
     * 
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        // Check that the domain was supplied
        if ($this->noDomainSupplied()) {
            return $result;
        }
        // $this->user->setDefaultEzmlmOpts();
    }

    /**
     * __default 
     * 
     * Call $this->listLists()
     * 
     * @access public
     * @return void
     */
    public function __default()
    {
        return $this->listLists();
    }

    /**
     * listLists 
     * 
     * List ezmlm mailing lists
     * 
     * @access public
     * @return void
     */
    public function listLists()
    {
    }

    /**
     * addList 
     * 
     * Show add list page
     * 
     * @access public
     * @return void
     */
    public function addList()
    {
    }

    /**
     * addListNow 
     * 
     * Try and add a list
     * 
     * @access public
     * @return void
     */
    public function addListNow()
    {
    }

    /**
     * modifyList 
     * 
     * Show modify list page
     * 
     * @access public
     * @return void
     */
    public function modifyList()
    {
    }

    /**
     * modifyListNow 
     * 
     * Try and modify a list
     * 
     * @access public
     * @return void
     */
    public function modifyListNow()
    {
    }

    /**
     * deleteList 
     * 
     * Show delete list confirmation
     * 
     * @access public
     * @return void
     */
    public function deleteList()
    {
    }

    /**
     * deleteListNow 
     * 
     * Delete a list
     * 
     * @access public
     * @return void
     */
    public function deleteListNow()
    {
    }

    /**
     * deleteListCancel 
     * 
     * Cancel list deletion and go back to listing lists
     * 
     * @access public
     * @return void
     */
    public function deleteListCancel()
    {
    }
}
?>
