<?php

/**
 *
 * Forward Module
 *
 * This module is for viewing and editing vpopmail forwards
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 * @package ToasterAdmin
 * @version 1.0
 *
 */


/**
 * Framework_Module_Forwards 
 * 
 * @package ToasterAdmin
 * @copyright 2005-2006 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @license GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */
class Framework_Module_Forwards extends ToasterAdmin_Common
{

    /**
     * __construct 
     * 
     * class constructor
     * 
     * @access protected
     * @return result of listForwards()
     */
    function __construct() {
        parent::__construct();
        // Make sure doamin was supplied
        if (($result = $this->noDomainSupplied())) {
            return $result;
        }
        // Make sure they are authorized
        if (($result = $this->noDomainPrivileges())) {
            return $result;
        }
    }

    /**
     * __default 
     * 
     * @access protected
     * @return void
     */
    function __default() {
        return $this->listForwards();
    }


    /**
     * checkPrivileges 
     * 
     * @access protected
     * @return mixed true on success, PEAR_Error on failure
     */
    protected function checkPrivileges() {
        // Verify that they have access to this domain
        if (!$this->user->isDomainAdmin($this->domain)) {
            return PEAR::raiseError(_('Error: you do not have edit privileges on domain ') . $this->domain);
        }
        return true;
    }
    
    /**
     * listForwards 
     * 
     * @access public
     * @return void
     */
    function listForwards() {
    
        $result = $this->checkPrivileges();
        if (PEAR::isError($result)) return $result;

        // Pagintation setup
        $full_alias_array = $this->user->listAlias($this->domain);
        if (PEAR::isError($full_alias_array)) return $full_alias_array;
        // Format the valias outpt from vpopmaild
        $aliasesParsed = $this->user->parseAliases($full_alias_array, 'forwards');
        $total = count($aliasesParsed);
        $this->paginate($total);

        // List Accounts
        $alias_array = $this->user->paginateArray($aliasesParsed, $this->data['currentPage'], $this->data['limit']);
    
        if (count($alias_array) == 0) {
            $this->setData('message', _("No Forwards.  Care to add one?"));
            return $this->addForward();
        }
    
        $aliases = array();
        $count = 0;
        while (list($key,$val) = each($alias_array)) {
            $forwardName = ereg_replace('@.*$', '', $key);
            $aliases[$count]['name'] = $forwardName;
            $aliases[$count]['contents'] = $this->user->getAliasContents($val);
            $aliases[$count]['edit_url'] = htmlspecialchars("$base_url?module=Forwards&domain={$this->domain}&forward=$forwardName&event=modifyForward");
            $aliases[$count]['delete_url'] = htmlspecialchars("$base_url?module=Forwards&domain={$this->domain}&forward=$forwardName&event=deleteForward");
            $count++;
        }
        $this->setData('forwards', $aliases);

        $this->setData('add_forward_url', htmlspecialchars("./?module=Forwards&event=addForward&domain={$this->domain}"));

        $this->setData('LANG_Forward', _("Forward"));
        $this->setData('LANG_Recipient', _("Recipient"));
        $this->setData('LANG_Edit', _("Edit"));
        $this->setData('LANG_Delete', _("Delete"));

        $this->setData('LANG_Forwards_for_domain', _("Forwards for domain"));
        $this->setData('LANG_Forwards_Page', _("Forwards Page"));
        $this->setData('LANG_of', _("of"));
        $this->setData('LANG_Add_Forward', _("Add Forward"));
        $this->setData('LANG_edit', _("edit"));
        $this->setData('LANG_delete', _("delete"));
        $this->setData('LANG_Domain_Menu', _("Domain Menu"));
        $this->tplFile = 'listForwards.tpl';
    }

    /**
     * addForward 
     * 
     * @access public
     * @return mixed void on success, PEAR_Error on failure
     */
    function addForward() {
        $result = $this->checkPrivileges();
        if (PEAR::isError($result)) return $result;

        $form = $this->addForwardForm();
        $renderer =& new HTML_QuickForm_Renderer_AssocArray();
        $form->accept($renderer);
        $this->setData('form', $renderer->toAssocArray());
        $this->tplFile = 'addForward.tpl';
    }

    /**
     * addForwardForm 
     * 
     * @access public
     * @return object $form
     */
    function addForwardForm() {
        // Lang
        $this->setData('LANG_Forward_Name', _("Forward Name"));
        $this->setData('LANG_Add_Forward', _("Add Forward"));
        $this->setData('LANG_Domain_Menu', _("Domain Menu"));

        $form = & new HTML_QuickForm('form', 'post', './?module=Forwards&event=addForwardNow');

        $form->registerRule('validForwardName', 'regex', "/^[a-z0-9]+([_\\.-][a-z0-9]+)*$/i");

        $form->addElement('hidden', 'domain', $this->domain);
        $form->addElement('text', 'forward', _("Forward Name"));
        $form->addElement('text', 'destination', _("Destination Address"));
        $form->addElement('submit', 'submit', _("Add"));

        $form->addRule('forward', _("Forward is required"), 'required', null, 'client');
        $form->addRule('forward', _("Forward name is invalid (should be forward name only, not full email address"), 'validForwardName');
        $form->addRule('destination', _("Destination is required"), 'required', null, 'client');
        $form->addRule('destination', _("Destination must be a full email address"), 'email', null, 'client');

        return $form;
    }

    /**
     * addForwardNow 
     * 
     * @access public
     * @return mixed PEAR_Error on failure, listForwards() on success
     */
    function addForwardNow() {
        $result = $this->checkPrivileges();
        if (PEAR::isError($result)) return $result;

        $form = $this->addForwardForm();
        if (!$form->validate()) {
            $renderer =& new HTML_QuickForm_Renderer_AssocArray();
            $form->accept($renderer);
            $this->setData('form', $renderer->toAssocArray());
            $this->tplFile = 'addForward.tpl';
            return;
        }

        $this->setData('forward', $_REQUEST['forward']);
        $this->setData('destination', $_REQUEST['destination']);

        $result = $this->addForwardLine();
        if (PEAR::isError($result)) {
            if ($result->getMessage() == 'Forward Exists') {
                $this->setData('message', _("Forward already exists"));
                return $this->addForward();
            }
            return $result;
        }
        $this->setData('message', _("Forward Added Successfully"));
        return $this->listForwards();
    }

    /**
     * addForwardLine 
     * 
     * @param string $type 
     * @access protected
     * @return mixed true on success, PEAR_Error on failure
     */
    protected function addForwardLine($type = 'new') {

        $contents = $this->user->readFile($this->domain, '', ".qmail-" . $this->data['forward']);
        if ($type == 'new') {
            if (!PEAR::isError($contents)) {
                return PEAR::raiseError('Forward Exists');
            } else if ($contents->getMessage() != '-ERR 2102 No such file or directory') {
                return $contents;
            } else {
                $contents = array();
            }
        } else {
            if (PEAR::isError($contents)) return $contents;
        }
    
        // Now build a new array without that forward
        if (in_array("&" . $this->data['destination'], $contents)) {
            $this->setData('message', 'Error: destination already exists');
            return $this->modifyForward();
        }
        array_push($contents, "&" . $this->data['destination']);
        $result = $this->user->writeFile($contents, $this->domain, '', ".qmail-" . $this->data['forward']);
        if (PEAR::isError($result)) {
                return $result;
        }
        return true;
    }

    /**
     * deleteForwardLine 
     * 
     * @access protected
     * @return mixed true on success, PEAR_Error on failure
     */
    protected function deleteForwardLine() {

        $contents = $this->user->readFile($this->domain, '', ".qmail-" . $this->data['forward']);
        if (PEAR::isError($contents)) {
            // Go back to list aliases, which will display the messgae
            return PEAR::raiseError($contents->getMessage(), 1);
        }
    
        // Now build a new array without that forward
        if (!in_array($this->data['line'], $contents)) {
            return PEAR::raiseError(_('Error: forward line does not exist'), 2);
        }

        if (count($contents) == 1) {
            // tell caller to delete instead
            return PEAR::raiseError(_("Only one line, use delete instead"), 3);
        }
        $newContents = array();
        $count = 1;
        while (list($key, $val) = each($contents)) {
            if ($val == $this->data['line']) continue;
            $newContents[$count] = $val;
            $count++;
        }
        $result = $this->user->writeFile($newContents, $this->domain, '', ".qmail-" . $this->data['forward']);
        if (PEAR::isError($result)) {
            return $result;
        }
        return true;
    }
    
    
    /**
     * modifyForward 
     * 
     * @access public
     * @return mixed void on success, PEAR_Error on failure
     */
    function modifyForward() {
    
        $this->checkPrivileges();
        // Make sure forward was supplied
        if (!isset($_REQUEST['forward'])) {
            $this->setData('message', _("Error: no forward provided"));
            return $this->listForwards();
        }

        $forward = ereg_replace('^.qmail-', '', $_REQUEST['forward']);
    
        // Get forward info if it exists
        $contents = $this->user->ReadFile($this->domain, '', ".qmail-$forward");
        if ($this->user->Error) return PEAR::raiseError(_("Error: ") . $this->user->Error);
    
        // Set template data
        $this->setData('forward', $forward);
        $this->setData('forward_contents', $this->returnForwardArray($contents));

        $form = $this->modifyForwardForm();
        $renderer =& new HTML_QuickForm_Renderer_AssocArray();
        $form->accept($renderer);
        $this->setData('form', $renderer->toAssocArray());
        $this->tplFile = 'modifyForward.tpl';
    }
    
    /**
     * returnForwardArray 
     * 
     * @param mixed $contents 
     * @access protected
     * @return array $forward_array
     */
    protected function returnForwardArray($contents) {
        $count = 0;
        $forward_array = array();
        while (list($key,$val) = each($contents)) {
            $forward_array[$count]['destination'] = $this->user->displayForwardLine($val);
            $forward_array[$count]['delete_url'] = htmlspecialchars("./?module=Forwards&event=deleteForwardLineNow&domain={$this->domain}&forward=" . $_REQUEST['forward'] . "&line=" . urlencode($val));
            $count++;
        }
        return $forward_array;
    }

    /**
     * modifyForwardNow 
     * 
     * @access public
     * @return mixed void on success, PEAR_Error on failure
     */
    function modifyForwardNow() {
        $this->checkPrivileges();

        $forward = ereg_replace('^.qmail-', '', $_REQUEST['forward']);
        $this->setData('forward', $forward);

        // Get forward info if it exists
        $contents = $this->user->ReadFile($this->domain, '', ".qmail-$forward");
        if ($this->user->Error) return PEAR::raiseError(_("Error: ") . $this->user->Error);

        $form = $this->modifyForwardForm();
        if (!$form->validate()) {

            // Set template data
            $this->setData('forward', $forward);
            $this->setData('forward_contents', $this->returnForwardArray($contents));

            $renderer =& new HTML_QuickForm_Renderer_AssocArray();
            $form->accept($renderer);
            $this->setData('form', $renderer->toAssocArray());
            $this->tplFile = 'modifyForward.tpl';
            return;
        }
        $this->setData('destination', $_REQUEST['destination']);
    
        $result = $this->addForwardLine($type = 'modify');
        if (PEAR::isError($result)) {
            if ($result->getMessage() == 'Error: destination already exists') {
                $this->setData('message', _("Error: destination already exists"));
                return $this->modifyForward();
            } else {
                return $result;
            }
        }
        $this->setData('message', _("Destination Added Successfully"));
        return $this->modifyForward();
    }

    /**
     * modifyForwardForm 
     * 
     * @access public
     * @return object $form
     */
    function modifyForwardForm() {
        // Lang
        $this->setData('LANG_Modify_Forward', _("Modify Forward"));
        $this->setData('LANG_Destination', _("Destination"));
        $this->setData('LANG_Delete', _("Delete"));
        $this->setData('LANG_delete', _("delete"));
        $this->setData('LANG_Forwards_Menu', _("Forwards Menu"));
        $this->setData('LANG_Add_Destination', _("Add Destination"));
        $this->setData('forwards_url', htmlspecialchars("./?module=Forwards&domain={$this->domain}"));

        // Form
        $form = & new HTML_QuickForm('form', 'post', './?module=Forwards&event=modifyForwardNow');

        $form->addElement('hidden', 'domain', $this->domain);
        $form->addElement('hidden', 'forward', $this->data['forward']);
        $form->addElement('text', 'destination', _("Destination Address"));
        $form->addElement('submit', 'submit', _("Add"));

        $form->addRule('forward', _("Forward is required"), 'required');
        $form->addRule('destination', _("Destination is required"), 'required', null, 'client');
        $form->addRule('destination', _("Destination must be a full email address"), 'email', null, 'client');

        return $form;
    }

    /**
     * deleteForwardLineNow 
     * 
     * @access public
     * @return mixed result of modifyForward on success, PEAR_Error on failure
     */
    function deleteForwardLineNow() {
        $this->checkPrivileges();

        // Make sure forward was supplied
        if (!isset($_REQUEST['forward'])) {
            $this->setData('message', _("Error: no forward provided"));
            return $this->listForwards();
        }
        // Make sure line was supplied
        if (!isset($_REQUEST['line'])) {
            $this->setData('message', _("Error: no forward line provided"));
            return $this->modifyForward();
        }

        $forward = ereg_replace('^.qmail-', '', $_REQUEST['forward']);
        $this->setData('forward', $forward);
        $this->setData('line', $_REQUEST['line']);

        $result = $this->deleteForwardLine();
        if (PEAR::isError($result)) {
            if ($result->getCode() == 1) {
                $this->setData('message', $result->getMessage());
                return $this->listAliases();
            } else if ($result->getCode() == 2) {
                $this->setData('message', $result->getMessage());
                return $this->modifyForward();
            } else if ($result->getCode() == 3) {
                return $this->deleteForward();
            } else {
                return $result;
            }
        }
        $this->setData('message', _("Destination Deleted Successfully"));
        return $this->modifyForward();
    }

    /**
     * deleteForward 
     * 
     * @access public
     * @return mixed listForwards() on success, PEAR_Error on failure
     */
    function deleteForward() {
        $result = $this->checkPrivileges();
        if (PEAR::isError($result)) return $result;

        // Make sure forward was supplied
        if (!isset($_REQUEST['forward'])) {
            $this->setData('message', _("Error: no forward provided"));
            return $this->listForwards();
        }
        $forward = ereg_replace('^.qmail-', '', $_REQUEST['forward']);
        $contents = $this->user->readFile($this->domain, '', ".qmail-" . $forward);
        if (PEAR::isError($contents))
            return $contents;
        $result = $this->user->rmFile($this->domain, '', '.qmail-' . $forward);
        if (PEAR::isError($contents)) return $contents;
        $this->setData('message', _("Forward Deleted Successfully"));
        return $this->listForwards();
    }

}
?>
