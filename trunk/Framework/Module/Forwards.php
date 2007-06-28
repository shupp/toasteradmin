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
class Framework_Module_Forwards extends Framework_Auth_Vpopmail
{

    /**
     *  $domain is set from $_REQUEST['domain'];
     */
    public $domain = null;

    /**
     * __construct 
     * 
     * class constructor
     * 
     * @access protected
     * @return void
     */
    function __construct() {
        parent::__construct();
        // Make sure doamin was supplied
        if(!isset($_REQUEST['domain'])) 
            throw new Framework_Exception(_("Error: no domain supplied"));
        $this->domain = $_REQUEST['domain'];
        $this->setData('domain', $this->domain);
        $this->setData('domain_url', htmlspecialchars('./?module=Domains&event=domainMenu&domain=' . $this->domain));
    }

    function __default() {
        return $this->listForwards();
    }


    /**
     * checkPrivileges 
     * 
     * @access protected
     * @return void
     */
    protected function checkPrivileges() {
        // Verify that they have access to this domain
        if(!$this->user->isDomainAdmin($this->domain)) {
            return PEAR::raiseError(_('Error: you do not have edit privileges on domain ') . $this->domain);
        }
    }
    
    function listForwards() {
    
        $this->checkPrivileges();

        // Pagintation setup
        $full_alias_array = $this->user->ListAlias($this->domain);
        if($this->user->Error) return PEAR::raiseError(_("Error: ") . $this->user->Error);
        // Format the valias outpt from vpopmaild
        $aliasesParsed = $this->user->parseAliases($full_alias_array, 'forwards');
        $total = count($aliasesParsed);

        $this->setData('total', $total);
        $this->setData('limit', (integer)Framework::$site->config->maxPerPage);
        if(isset($_REQUEST['start']) && !ereg('[^0-9]', $_REQUEST['start'])) {
            if($_REQUEST['start'] == 0) {
                $start = 1;
            } else {
                $start = $_REQUEST['start'];
            }
        }
        if(!isset($start)) $start = 1;
        $this->setData('start', $start);
        $this->setData('currentPage', ceil($this->data['start'] / $this->data['limit']));
        $this->setData('totalPages', ceil($this->data['total'] / $this->data['limit']));

        // List Accounts
        $alias_array = $this->user->ListAliases($aliasesParsed, $this->data['currentPage'], $this->data['limit']);
        if($this->user->Error) return PEAR::raiseError(_("Error: ") . $this->user->Error);
    
        if(count($alias_array) == 0) {
            $this->setData('message', _("No Forwards.  Care to add one?"));
            return $this->addForward();
        }
    
        $aliases = array();
        $count = 0;
        while(list($key,$val) = each($alias_array)) {
            $forwardName = ereg_replace('@.*$', '', $key);
            $aliases[$count]['name'] = $forwardName;
            $aliases[$count]['contents'] = $this->user->GetAliasContents($val);
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
        return;
    }

    function addForward() {
        $result = $this->checkPrivileges();
        if(PEAR::isError($result)) return $result;

        $form = $this->addForwardForm();
        $renderer =& new HTML_QuickForm_Renderer_Array();
        $form->accept($renderer);
        $this->setData('form', 
            HTML_QuickForm_Renderer_AssocArray::toAssocArray($form->toArray()));
        $this->tplFile = 'addForward.tpl';
        return;
    }

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

    function addForwardNow() {
        $result = $this->checkPrivileges();
        if(PEAR::isError($result)) return $result;

        $form = $this->addForwardForm();
        if(!$form->validate()) {
            $renderer =& new HTML_QuickForm_Renderer_Array();
            $form->accept($renderer);
            $this->setData('form', 
                HTML_QuickForm_Renderer_AssocArray::toAssocArray($form->toArray()));
            $this->tplFile = 'addForward.tpl';
            return;
        }

        $this->setData('forward', $_REQUEST['forward']);
        $this->setData('destination', $_REQUEST['destination']);

        $result = $this->addForwardLine();
        if(PEAR::isError($result)) {
            if($result->getMessage() == 'Forward Exists') {
                $this->setData('message', _("Forward already exists"));
                return $this->addForward();
            }
            return $result;
        }
        $this->setData('message', _("Forward Added Successfully"));
        return $this->listForwards();
    }

    protected function addForwardLine($type = 'new') {

        $contents = $this->user->ReadFile($this->domain, '', ".qmail-" . $this->data['forward']);
        if($type == 'new') {
            if(!$this->user->Error) {
                return PEAR::raiseError("Forward Exists");
            } else if($this->user->Error != 'command failed - -ERR 2102 No such file or directory') {
                return PEAR::raiseError(_("Error: ") . $this->user->Error);
            }
        } else {
            if($this->user->Error) return PEAR::raiseError(_("Error: ") . $this->user->Error);
        }
    
        // Now build a new array without that forward
        if(empty($contents)) $contents = array();
        if(in_array("&" . $this->data['destination'], $contents)) {
            return PEAR::raiseError('Error: destination already exists');
        }
        array_push($contents, "&" . $this->data['destination']);
        $this->user->WriteFile($contents, $this->domain, '', ".qmail-" . $this->data['forward']);
        if($this->user->Error) {
                return PEAR::raiseError(_("Error: ") . $this->user->Error);
        }
    }

    protected function deleteForwardLine() {

        $contents = $this->user->ReadFile($this->domain, '', ".qmail-" . $this->data['forward']);
        if($this->user->Error)
                return PEAR::raiseError($this->user->Error, 1);
    
        // Now build a new array without that forward
        if(!in_array($this->data['line'], $contents)) {
            return PEAR::raiseError(_('Error: forward line does not exist'), 2);
        }

        if(count($contents) == 1) {
            // tell caller to delete instead
            return PEAR::raiseError(_("Only one line, use delete instead"), 3);
        }
        $newContents = array();
        $count = 1;
        while(list($key, $val) = each($contents)) {
            if($val == $this->data['line']) continue;
            $newContents[$count] = $val;
            $count++;
        }
        $this->user->WriteFile($newContents, $this->domain, '', ".qmail-" . $this->data['forward']);
        if($this->user->Error) {
            return PEAR::raiseError(_("Error: ") . $this->user->Error, 4);
        }
    }
    
    
    function modifyForward() {
    
        $this->checkPrivileges();
        // Make sure forward was supplied
        if(!isset($_REQUEST['forward'])) {
            $this->setData('message', _("Error: no forward provided"));
            return $this->listForwards();
        }

        $forward = ereg_replace('^.qmail-', '', $_REQUEST['forward']);
    
        // Get forward info if it exists
        $contents = $this->user->ReadFile($this->domain, '', ".qmail-$forward");
        if($this->user->Error) return PEAR::raiseError(_("Error: ") . $this->user->Error);
    
        // Set template data
        $this->setData('forward', $forward);
        $this->setData('forward_contents', $this->returnForwardArray($contents));

        $form = $this->modifyForwardForm();
        $renderer =& new HTML_QuickForm_Renderer_Array();
        $form->accept($renderer);
        $this->setData('form', HTML_QuickForm_Renderer_AssocArray::toAssocArray($form->toArray()));
        $this->tplFile = 'modifyForward.tpl';
        return;
    }
    
    protected function returnForwardArray($contents) {
        $count = 0;
        $forward_array = array();
        while(list($key,$val) = each($contents)) {
            $forward_array[$count]['destination'] = $this->user->display_forward_line($val);
            $forward_array[$count]['delete_url'] = htmlspecialchars("./?module=Forwards&event=deleteForwardLineNow&domain={$this->domain}&forward=" . $_REQUEST['forward'] . "&line=" . urlencode($val));
            $count++;
        }
        return $forward_array;
    }

    function modifyForwardNow() {
        $this->checkPrivileges();

        $forward = ereg_replace('^.qmail-', '', $_REQUEST['forward']);
        $this->setData('forward', $forward);

        // Get forward info if it exists
        $contents = $this->user->ReadFile($this->domain, '', ".qmail-$forward");
        if($this->user->Error) return PEAR::raiseError(_("Error: ") . $this->user->Error);

        $form = $this->modifyForwardForm();
        if(!$form->validate()) {

            // Set template data
            $this->setData('forward', $forward);
            $this->setData('forward_contents', $this->returnForwardArray($contents));

            $renderer =& new HTML_QuickForm_Renderer_Array();
            $form->accept($renderer);
            $this->setData('form', HTML_QuickForm_Renderer_AssocArray::toAssocArray($form->toArray()));
            $this->tplFile = 'modifyForward.tpl';
            return;
        }
        $this->setData('destination', $_REQUEST['destination']);
    
        $result = $this->addForwardLine($type = 'modify');
        if(PEAR::isError($result)) {
            if($result->getMessage() == 'Error: destination already exists') {
                $this->setData('message', _("Error: destination already exists"));
                return $this->modifyForward();
            } else {
                return $result;
            }
        }
        $this->setData('message', _("Destination Added Successfully"));
        return $this->modifyForward();
    }

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

    function deleteForwardLineNow() {
        $this->checkPrivileges();

        // Make sure forward was supplied
        if(!isset($_REQUEST['forward'])) {
            $this->setData('message', _("Error: no forward provided"));
            return $this->listForwards();
        }
        // Make sure line was supplied
        if(!isset($_REQUEST['line'])) {
            $this->setData('message', _("Error: no forward line provided"));
            return $this->modifyForward();
        }

        $forward = ereg_replace('^.qmail-', '', $_REQUEST['forward']);
        $this->setData('forward', $forward);
        $this->setData('line', $_REQUEST['line']);

        $result = $this->deleteForwardLine();
        if(PEAR::isError($result)) {
            if($result->getCode() == 1) {
                $this->setData('message', $result->getMessage());
                return $this->listAliases();
            } else if($result->getCode() == 2) {
                $this->setData('message', $result->getMessage());
                return $this->modifyForward();
            } else if($result->getCode() == 3) {
                return $this->deleteForward();
            } else {
                return $result;
            }
        }
        $this->setData('message', _("Destination Deleted Successfully"));
        return $this->modifyForward();

    }

    function deleteForward() {
        $this->checkPrivileges();

        // Make sure forward was supplied
        if(!isset($_REQUEST['forward'])) {
            $this->setData('message', _("Error: no forward provided"));
            return $this->listForwards();
        }
        $forward = ereg_replace('^.qmail-', '', $_REQUEST['forward']);
        $contents = $this->user->ReadFile($this->domain, '', ".qmail-" . $forward);
        if($this->user->Error)
            return PEAR::raiseError($this->user->Error);
        $this->user->RmFile($this->domain, '', '.qmail-' . $forward);
        if($this->user->Error)
            return PEAR::raiseError($this->user->Error);
        $this->setData('message', _("Forward Deleted Successfully"));
        return $this->listForwards();
    }

}
?>
