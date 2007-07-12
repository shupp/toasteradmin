<?php
/**
 * Framework_Module_MailingLists 
 * 
 * @uses Framework_Auth_User
 * @package ToasterAdmin
 * @copyright 2007 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @license GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */

/**
 * Framework_Module_MailingLists 
 * 
 * Management of ezmlm-idx lists through vpopmaild
 * 
 * @uses Framework_Auth_User
 * @package ToasterAdmin
 * @copyright 2007 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @license GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */
class Framework_Module_MailingLists extends ToasterAdmin_Common
{
    public function __construct() {
        parent::__construct();
        // Check that the domain was supplied
        if (($result = $this->noDomainSupplied() {
            return $result;
        }
        // Check that they have domain edit privilges
        if (($result = $this->noDomainPrivs() {
            return $result;
        }
        $this->user->setDefaultEzmlmOpts();
    }

    public function __default() {
        return $this->listLists();
    }

    protected function checkPrivileges() {
    }

    public function listLists() {
    }

    public function addList() {
    }

    public function addListNow() {
    }

    public function modifyList() {
    }

    public function modifyListNow() {
    }

    public function deleteList() {
    }

    public function deleteListNow() {
    }

    public function deleteListCancel() {
    }
}
?>

