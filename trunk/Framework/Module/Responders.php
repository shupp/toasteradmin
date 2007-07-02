<?php

/**
 * Responders Module
 * 
 * @uses Framework_Auth_Vpopmail
 * @package ToasterAdmin
 * @copyright 2006-2007 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @license GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */

/**
 * Framework_Module_Responders 
 * 
 * @uses Framework_Auth_Vpopmail
 * @package ToasterAdmin
 * @copyright 2006-2007 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @license GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */
class Framework_Module_Responders extends Framework_Auth_Vpopmail
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
        if (!isset($_REQUEST['domain']))
            throw new Framework_Exception(_("Error: no domain supplied"));
        $this->domain = $_REQUEST['domain'];
        $this->setData('domain', $this->domain);
        $this->setData('domain_url', htmlspecialchars('./?module=Domains&event=domainMenu&domain=' . $this->domain));
    }

    /**
     * checkPrivileges 
     * 
     * @access protected
     * @return void
     */
    protected function checkPrivileges() {
        // Verify that they have access to this domain
        if (!$this->user->isDomainAdmin($this->domain)) {
            return PEAR::raiseError(_('Error: you do not have edit privileges on domain ') . $this->domain);
        }
    }

    /**
     * sameDomain 
     * 
     * @param mixed $name 
     * @param mixed $value 
     * @access public
     * @return bool
     */
    public function sameDomain ($name, $value) {
        $emailArray = explode('@', $value);
        if ($emailArray[1] == $this->domain) return true;
        return false;
    }

    /**
     * __default 
     * 
     * @access protected
     * @return void
     */
    function __default() {
        $this->listResponders();
    }

    function listResponders() {

        $this->checkPrivileges();

        $full_alias_array = $this->user->listAlias($this->domain);
        if (PEAR::isError($full_alias_array)) return $full_alias_array;
        $autoresponders_raw = $this->user->parseAliases($full_alias_array, 'responders');

        // Pagintation setup
        $total = count($autoresponders_raw);
        $this->paginate($total);
        
        // List Responders
        $autoresponders_paginated = $this->user->paginateArray($autoresponders_raw, $this->data['currentPage'], $this->data['limit']);
        $autoresponders = array();
        $count = 0;
        while (list($key,$val) = each($autoresponders_paginated)) {
            $autoresponders[$count]['autoresponder'] = $key;
            $autoresponders[$count]['edit_url'] = htmlspecialchars("./?module=Responders&domain={$this->domain}&autoresponder=$key&event=modifyResponder");
            $autoresponders[$count]['delete_url'] = htmlspecialchars("./?module=Responders&domain={$this->domain}&autoresponder=$key&event=delete");
            $count++;
        }
        $this->setData('autoresponders', $autoresponders);
        $this->setData('add_url', htmlspecialchars("./?module=Responders&event=addResponder&domain={$this->domain}"));

        // Language
        $this->setData('LANG_AutoResponders_in_domain', _('Auto-Responders in domain'));
        $this->setData('LANG_AutoResponders_Page', _('Auto-Responders: Page'));
        $this->setData('LANG_Add_AutoResponder', _('Add Auto-Responder'));
        $this->setData('LANG_Domain_Menu', _('Domain Menu'));
        $this->setData('LANG_of', _('of'));
        $this->setData('LANG_AutoResponder', _('Auto-Responder'));
        $this->setData('LANG_Edit', _('Edit'));
        $this->setData('LANG_Delete', _('Delete'));
        $this->setData('LANG_edit', _('edit'));
        $this->setData('LANG_delete', _('delete'));

        $this->tplFile = 'listResponders.tpl';
        return;
    }

    /**
     * addResponder
     * 
     * @access public
     * @return void
     */
    function addResponder() {
        $form = $this->responderForm();
        $renderer =& new HTML_QuickForm_Renderer_AssocArray();
        $form->accept($renderer);
        $this->setData('form', $renderer->toAssocArray());
        $this->tplFile = 'responderForm.tpl';
        return;
    }

    function addResponderNow() {
        $this->setData('LANG_responder_submit', _("Add Auto-Responder"));
        $this->setData('LANG_responder_header', _("Add Auto-Responder to domain "));
        $form = $this->responderForm();
        if (!$form->validate()) {
            $renderer =& new HTML_QuickForm_Renderer_AssocArray();
            $form->accept($renderer);
            $this->setData('form', $renderer->toAssocArray());
            $this->tplFile = 'responderForm.tpl';
            return;
        }

        // Split autoresponder to get user
        $email_array = explode('@', $_REQUEST['autoresponder']);
        $responder = $this->user->robotGet($this->domain, $email_array[0]);
        if (!PEAR::isError($responder)) {
            $this->setData('message', _('Error: autoresponder already exists: ' . $email_array[0]));
            return $this->addResponder();
        }

        $result = $this->user->robotSet($this->domain, $email_array[0], $_POST['subject'], $_POST['body'], $_POST['copy']);
        if (PEAR::isError($result)) return $result;
        $this->setData('message', _("Auto-Responder Added Successfully"));
        return $this->listResponders();
    }

    function responderForm($type = 'add', $defaults = '') {
        $this->setData('LANG_Domain_Menu', _("Domain Menu"));
        $this->setData('type', $type);
        if ($type == 'add') {
            $this->setData('LANG_responder_submit', _("Add"));
            $this->setData('LANG_responder_header', _("Add Auto-Responder to domain ") . $this->domain);
        } else {
            $this->setData('responder_name', $defaults['autoresponder']);
            $this->setData('LANG_responder_submit', _("Modify"));
            $this->setData('LANG_responder_header', _("Modify Auto-Responder ") . $_REQUEST['autoresponder']);
        }

        $form = new HTML_QuickForm('formAddAccount', 'post', "./?module=Responders&event=${type}ResponderNow&domain={$this->domain}");

        if ($defaults == '') {
            $form->setDefaults(array('autoresponder' => '@' . $this->domain));
        } else {
            $form->setDefaults($defaults);
        }

        if ($type == 'modify') {
            $form->addElement('hidden', 'autoresponder');
            $form->addRule('autoresponder', _("Auto-Responder is required"), 'required', null );
            $form->addRule('autoresponder', _("Auto-Responder must be a full address"), 'email', null);
        } else {
            $form->addElement('text', 'autoresponder', _('Auto-Responder'));
            $form->addRule('autoresponder', _("Auto-Responder is required"), 'required', null, 'client');
            $form->addRule('autoresponder', _("Auto-Responder must be a full address"), 'email', null, 'client');
        }
        $form->addElement('text', 'copy', _("Send Copy To"));
        $form->addElement('text', 'subject', _("Subject"));
        $form->addElement('textarea', 'body', _("Body"), 'cols="40" rows="10"');
        $form->addElement('submit', 'submit', $this->__get('LANG_responder_submit'));

        $form->registerRule('sameDomain', 'regex', "/@$this->domain$/i");

        $form->addRule('autoresponder', _('Error: wrong domain in Auto-Responder'), 'sameDomain');
        $form->addRule('copy', _("'Save a copy' must be an email address"), 'email', null, 'client');
        $form->addRule('subject', _("Subject is required"), 'required', null, 'client');
        $form->addRule('body', _("Body is required"), 'required', null, 'client');
        return $form;
    }

    function delete() {
        if (!isset($_REQUEST['autoresponder'])) {
            return PEAR::raiseError(_("Error: no responder supplied"));
        }
        $this->setData('LANG_Are_you_sure_you_want_to_delete_this_responder', _("Are you sure you want to delete the responder"));
        $this->setData('LANG_cancel', _("cancel"));
        $this->setData('LANG_delete', _("delete"));
        $this->setData('autoresponder', $_REQUEST['autoresponder']);
        $this->setData('cancel_url', "./?module=Responders&event=cancelDelete&domain=" . $this->domain);
        $this->setData('delete_now_url', "./?module=Responders&event=deleteNow&domain=" . $this->domain . "&autoresponder=" . $_REQUEST['autoresponder']);
        $this->tplFile = 'responderConfirmDelete.tpl';
    }

    function deleteNow() {
        if (!isset($_REQUEST['autoresponder'])) {
            return PEAR::raiseError(_("Error: no responder supplied"));
        }
        if (!isset($_REQUEST['domain'])) {
            return PEAR::raiseError(_("Error: no domain supplied"));
        }
        $array = explode('@', $_REQUEST['autoresponder']);
        $result = $this->user->robotDel($this->domain, $array[0]);
        if (PEAR::isError($result)) return $result;
        $this->setData('message', _("Responder Deleted Successfully"));
        return $this->listResponders();
    }

    function cancelDelete() {
        $this->setData('message', _("Delete Canceled"));
        $this->listResponders();
        return;
    }

    function modifyResponder() {
        // Make sure account was supplied
        if (!isset($_REQUEST['autoresponder'])) {
            return PEAR::raiseError(_("Error: no Auto-Responder supplied"));
        }
        // Check privs
        if (!$this->user->isUserAdmin($account, $this->domain)) {
            return PEAR::raiseError(_('Error: you do not have edit privileges on domain ') . $this->domain);
        }
        $array = explode('@', $_REQUEST['autoresponder']);
        // Setup defaults
        $responder = $this->user->robotGet($this->domain,$array[0]);
        if (PEAR::isError($responder)) return $responder;
        $defaults = array();
        $defaults['subject'] = $responder['Subject'];
        $defaults['body'] = implode("\n", $responder['Message']);
        $defaults['autoresponder'] = $_REQUEST['autoresponder'];
        if (isset($responder['Forward'])) {
            $defaults['copy'] = $responder['Forward'];
        }

        $form = $this->responderForm('modify', $defaults);
        $renderer =& new HTML_QuickForm_Renderer_AssocArray();
        $form->accept($renderer);
        $this->setData('form', $renderer->toAssocArray());
        $this->tplFile = 'responderForm.tpl';
        return;
    }

    function modifyResponderNow() {
        // Make sure account was supplied
        if (!isset($_REQUEST['autoresponder'])) {
            return PEAR::raiseError(_("Error: no Auto-Responder supplied"));
        }
        // Check privs
        if (!$this->user->isUserAdmin($account, $this->domain)) {
            return PEAR::raiseError(_('Error: you do not have edit privileges on domain ') . $this->domain);
        }
        $array = explode('@', $_REQUEST['autoresponder']);
        // Setup defaults
        $responder = $this->user->robotGet($this->domain,$array[0]);
        if (PEAR::isError($responder)) return $responder;
        $defaults = array();
        $defaults['subject'] = $responder['Subject'];
        $defaults['body'] = implode("\n", $responder['Message']);
        $defaults['autoresponder'] = $_REQUEST['autoresponder'];
        $form = $this->responderForm('modify', $defaults);
        if (!$form->validate()) {
            $renderer =& new HTML_QuickForm_Renderer_AssocArray();
            $form->accept($renderer);
            $this->setData('form', $renderer->toAssocArray());
            $this->tplFile = 'responderForm.tpl';
            return;
        }
        $result = $this->user->robotDel($this->domain, $array[0]);
        $result = $this->user->robotSet($this->domain, $array[0], $_POST['subject'], $_POST['body'], $_POST['copy']);
        if (PEAR::isError($result)) return $result;
        $this->setData('message', _("Auto-Responder modified successfully"));
        return $this->listResponders();
    }
}
?>
