<?php

/**
 *
 * Forward Module
 *
 * This module is for viewing and editing vpopmail forwards
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 * @package TA_Modules
 * @version 1.0
 *
 */


class Framework_Module_Forwards extends Framework_Auth_vpopmail
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
        if(!isset($_REQUEST['domain'])) {
            die (_("Error: no domain supplied"));
        }
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
        $total = count($full_alias_array);

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
        $alias_array = $this->user->ListAliases($full_alias_array, $this->data['currentPage'], $this->data['limi']);
        if($this->user->Error) return PEAR::raiseError(_("Error: ") . $this->user->Error);
    
        if(count($alias_array) == 0) {
            $this->setData('message', _("No Forwards.  Care to add one?"));
            return $this->addForward();
        }
    
        $aliases = array();
        $count = 0;
        while(list($key,$val) = each($alias_array)) {
            $aliases[$count]['name'] = ereg_replace('^.qmail-', '', $val);
            $aliases[$count]['contents'] = $this->user->GetAliasContents($val, $this->domain);
            $aliases[$count]['edit_url'] = htmlspecialchars("$base_url?module=Forwards&domain={$this->domain}&forward=$val&event=modifyForward");
            $aliases[$count]['delete_url'] = htmlspecialchars("$base_url?module=Forwards&domain={$this->domain}&forward=$val&event=deleteForward");
            $count++;
        }
        $this->setData('forwards', $aliases);


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

    protected function forwardProvided() {
        // Make sure forward was supplied
        if(!isset($_REQUEST['forward'])) {
            return PEAR::raiseError(_('Error: no forward supplied'));
        }
    }
    protected function destinationProvided() {
        // Make sure destination was supplied
        if(!isset($_REQUEST['destination'])) {
            return PEAR::raiseError(_('Error: no destination supplied'));
        }
    }

    function addForward() {
        $form = $this->addForwardForm();
        $renderer =& new HTML_QuickForm_Renderer_Array();
        $form->accept($renderer);
        $this->setData('form', 
            HTML_QuickForm_Renderer_AssocArray::toAssocArray($form->toArray()));
        // $this->setData('form', $form->toHtml());
        $this->tplFile = 'addForward.tpl';
        return;
    }

    function addForwardForm() {
        // Lang
        $this->setData('LANG_Forward_Name', _("Forward Name"));
        $this->setData('LANG_Add_Forward', _("Add Forward"));
        $this->setData('LANG_Domain_Menu', _("Domain Menu"));
        // $defaults = array('forward' => "@{$this->domain}");
        $form = & new HTML_QuickForm('form', 'post', './?module=Forwards&event=addForwardNow');
        // $form->setDefaults($defaults);

        $form->addElement('hidden', 'domain', $this->domain);
        $form->addElement('text', 'forward', _("Forward Name"));
        $form->addElement('text', 'destination', _("Destination Address"));
        $form->addElement('submit', 'submit', _("Add"));

        $form->addRule('forward', _("Forward is required"), 'required', null, 'client');
        $form->addRule('destination', _("Destination is required"), 'required', null, 'client');
        $form->addRule('destination', _("Destination must be a full email address"), 'email', null, 'client');

        return $form;
    }

    function addForwardNow() {

        $this->checkPrivileges();
        $result = $this->forwardProvided();
        if(PEAR::isError($result)) return $result;
        $result = $this->destinationProvided();
        if(PEAR::isError($result)) return $result;

        $forward = ereg_replace('^.qmail-', '', $_REQUEST['forward']);
        $destination = $_REQUEST['destination'];

        $result = Mail_RFC822::parseAddressList($destination, '');
        if (PEAR::isError($result)) {
            $destination = $destination . '@' . $this->domain;
        }
        $result = Mail_RFC822::parseAddressList($destination, '');
        if (PEAR::isError($result)) return $result;

        // Add it!
        $contents = $this->user->ReadFile($this->domain, '', ".qmail-$forward");
        // if($this->user->Error && $this->user->Error != 'command failed - -ERR XXX invaild directory') 
        if($this->user->Error && $this->user->Error != 'command failed - -ERR XXX No such file or directory') 
            return PEAR::raiseError(_("Error: ") . $this->user->Error);
    
        // Now build a new array without that forward
        if(empty($contents)) $contents = array();
        array_push($contents, "&$destination");
        $this->user->WriteFile($contents, $this->domain, '', ".qmail-$forward");
        // if($this->user->Error && $this->user->Error != 'command failed - -ERR XXX invaild directory') 
        if($this->user->Error && $this->user->Error != 'command failed - -ERR XXX No such file or directory') 
            return PEAR::raiseError(_("Error: ") . $this->user->Error);
        $this->setData('message', _("Forward Added Successfully"));
        return $this->listForwards();
    }
    
    
    function modifyForward() {
    
        $this->checkPrivileges();
        $result = $this->forwardProvided();
        if(PEAR::isError($result)) return $result;

        $forward = ereg_replace('^.qmail-', '', $_REQUEST['forward']);
    
        // Get forward info if it exists
        $contents = $this->user->ReadFile($this->domain, '', ".qmail-$forward");
        if($this->user->Error && $this->user->Error != 'command failed - -ERR XXX No such file or directory') 
            die ("Error: {$this->user->Error}");
    
        $count = 0;
        while(list($key,$val) = each($contents)) {
            $forward_array[$count]['destination'] = $this->user->display_forward_line($val);
            $forward_array[$count]['delete_url'] = htmlspecialchars("$base_url?module=Forwards&event=delete_forward_line&domain={$this->domain}&forward=" . $_REQUEST['forward'] . "&line=$val");
            $count++;
        }

        // Set template data
        $this->setData('forward', $forward);
        $this->setData('forward_contents', $forward_array);

        $form = $this->modifyForwardForm();
        $renderer =& new HTML_QuickForm_Renderer_Array();
        $form->accept($renderer);
        $this->setData('form', HTML_QuickForm_Renderer_AssocArray::toAssocArray($form->toArray()));
        $this->tplFile = 'modifyForward.tpl';
        return;
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
        $form = & new HTML_QuickForm('form', 'post', './?module=Forwards&event=addForwardLine');

        $form->addElement('hidden', 'domain', $this->domain);
        $form->addElement('hidden', 'forward', $this->data['forward']);
        $form->addElement('text', 'forward', _("Forward Name"));
        $form->addElement('text', 'destination', _("Destination Address"));
        $form->addElement('submit', 'submit', _("Add"));

        $form->addRule('forward', _("Forward is required"), 'required', null, 'client');
        $form->addRule('destination', _("Destination is required"), 'required', null, 'client');
        $form->addRule('destination', _("Destination must be a full email address"), 'email', null, 'client');

        return $form;
    }
    
    /*
    
    } else if($_REQUEST['event'] == 'delete_forward_line') {
    
        // Make sure forward was supplied
        if(!isset($_REQUEST['forward'])) {
            $tpl->set_msg_err(_('Error: no forward supplied'));
            $tpl->wrap_exit('back.tpl');
        }
        $forward = ereg_replace('^.qmail-', '', $_REQUEST['forward']);
    
        // Make sure forward line was supplied
        if(!isset($_REQUEST['line'])) {
            $tpl->set_msg_err(_('Error: no forward destination line supplied'));
            $tpl->wrap_exit();
        }
        $line = $_REQUEST['line'];
    
        // Get forward info if it exists
        $contents = $this->user->ReadFile($domain, '', ".qmail-$forward");
        if($this->user->Error && $this->user->Error != 'command failed - -ERR XXX No such file or directory') 
            die ("Error: {$this->user->Error}");
    
        // Now build a new array without that forward
        $new_contents = array();
        $count = 0;
        while(list($key,$val) = each($contents)) {
            if($val != $line) {
                $new_contents[$count] = $val;
                $count++;
            }
        }
    
        if(count($new_contents) == 0) {
            $this->user->RmFile($domain, '', ".qmail-$forward");
            $message = $tpl->set_msg(_("Forward Deleted Successfully"));
            $redirect = "$base_url?module=Forwards&domain=" . urlencode($domain);
        } else {
            $this->user->WriteFile($new_contents, $domain, '', ".qmail-$forward");
            if($this->user->Error && $this->user->Error != 'command failed - -ERR XXX No such file or directory') 
                die ("Error: {$this->user->Error}");
            $tpl->set_msg(_("Forward Modified Successfully"));
            $redirect = "$base_url?module=Forwards&domain=" . urlencode($domain) 
                . '&forward=' . urlencode($_REQUEST['forward']) . '&event=modify';
        }
        header("Location: $redirect");
        exit;
    
    
    }
    */
    
}
    
?>
