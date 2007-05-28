<?php

class Framework_Module_Responders extends Framework_Auth_vpopmail
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
            return PEAR::raiseError(_("Error: no domain supplied"));
        }
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
        if(!$this->user->isDomainAdmin($this->domain)) {
            return PEAR::raiseError(_('Error: you do not have edit privileges on domain ') . $this->domain);
        }
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

        $full_alias_array = $this->user->ListAlias($this->domain);
        $autoresponders_raw = $this->user->parseAliases($full_alias_array, 'responders');

        // Pagintation setup
        $total = count($autoresponders_raw);
        if($this->user->Error) die ("Error: {$this->user->Error}");
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

        // List Responders
        $autoresponders_paginated = $this->user->paginateArray($autoresponders_raw, $this->data['currentPage'], $this->data['limit']);
        $autoresponders = array();
        $count = 0;
        while(list($key,$val) = each($autoresponders_paginated)) {
            $autoresponders[$count]['autoresponder'] = $key;
            $autoresponders[$count]['edit_url'] = htmlspecialchars("./?module=Responders&domain={$this->domain}&responder=$key&event=modifyResponder");
            $autoresponders[$count]['delete_url'] = htmlspecialchars("./?module=Responders&domain={$this->domain}&responder=$key&event=deleteResponder");
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

        $form = $this->addForm();
        $renderer =& new HTML_QuickForm_Renderer_Array();
        $form->accept($renderer);
        $this->setData('form', 
            HTML_QuickForm_Renderer_AssocArray::toAssocArray($form->toArray()));
        $this->tplFile = 'addResponder.tpl';
        return;
    }

    static function sameDomain ($name, $value) {
        $emailArray = explode('@', $value);
        if($emailArray[1] == $this->domain) {
            return true;
        } else {
            return false;
        }
    }

    function addResponderNow() {

        $form = $this->addForm();
        if(!$form->validate()) {
            $renderer =& new HTML_QuickForm_Renderer_Array();
            $form->accept($renderer);
            $this->setData('form', 
                HTML_QuickForm_Renderer_AssocArray::toAssocArray($form->toArray()));
            $this->tplFile = 'addResponder.tpl';
            return;
        }

        $emailArray = explode('@', $_REQUEST['account']);
        $this->user->AddUser($this->domain, $emailArray[0], $_REQUEST['password'], $_REQUEST['comment']);
        if($this->user->Error) {
            $this->setData('message', _("Error: ") . $this->user->Error);
            $renderer =& new HTML_QuickForm_Renderer_Array();
            $form->accept($renderer);
            $this->setData('form', 
                HTML_QuickForm_Renderer_AssocArray::toAssocArray($form->toArray()));
            $this->tplFile = 'addAccount.tpl';
            return;
        }

        $this->setData('message', _("Account Added Successfully"));
        $this->listAutoResponders();
        return;
    }

    function addForm() {

        $this->setData('LANG_Add_AutoResponder_to_domain', _("Add Auto-Responder to domain "));
        $this->setData('LANG_Domain_Menu', _("Domain Menu"));
        $form = new HTML_QuickForm('formAddAccount', 'post', "./?module=Responders&event=addResponderNow&domain={$this->domain}");

        $form->setDefaults(array('autoresponder' => '@' . $this->domain));

        $form->addElement('text', 'autoresponder', _('Auto-Responder'));
        $form->addElement('text', 'copy', _("Send Copy To"));
        $form->addElement('text', 'subject', _("Subject"));
        $form->addElement('textarea', 'body', _("Body"), 'cols="40" rows="10"');
        $form->addElement('submit', 'submit', _("Add Auto-Responder"));

        $form->registerRule('sameDomain', 'regex', "/@$this->domain$/i");

        $form->addRule('autoresponder', _("Auto-Responder is required"), 'required', null, 'client');
        $form->addRule('autoresponder', _("Auto-Responder must be a full address"), 'email', null, 'client');
        $form->addRule('autoresponder', _('Error: wrong domain in Auto-Responder'), 'sameDomain');
        $form->addRule('copy', _("'Save a copy' must be an email address"), 'email', null, 'client');
        $form->addRule('subject', _("Subject is required"), 'required', null, 'client');
        $form->addRule('body', _("Body is required"), 'required', null, 'client');
        return $form;
    }

    function delete() {

        if(!isset($_REQUEST['account'])) {
            return PEAR::raiseError(_("Error: no account supplied"));
        }

        $this->setData('LANG_Are_you_sure_you_want_to_delete_this_account', _("Are you sure you want to delete this account"));
        $this->setData('LANG_cancel', _("cancel"));
        $this->setData('LANG_delete', _("delete"));

        $this->setData('account', $_REQUEST['account']);
        $this->setData('cancel_url', "./?module=Accounts&event=cancelDelete&domain=" . $this->domain);
        $this->setData('delete_now_url', "./?module=Accounts&event=deleteNow&domain=" . $this->domain . "&account=" . $_REQUEST['account']);
        $this->tplFile = 'accountConfirmDelete.tpl';
    }

    function deleteNow() {

        if(!isset($_REQUEST['account'])) {
            return PEAR::raiseError(_("Error: no account supplied"));
        }

        if(!isset($_REQUEST['domain'])) {
            return PEAR::raiseError(_("Error: no domain supplied"));
        }

        $this->user->DelUser($this->domain, $_REQUEST['account']);
        if($this->user->Error) {
            return PEAR::raiseError(_("Error: ") . $this->user->Error);
        }

        $this->setData('message', _("Account Deleted Successfully"));
        $this->listAccounts();
        return;
    }

    function cancelDelete() {
        $this->setData('message', _("Delete Canceled"));
        $this->listAccounts();
        return;
    }

    function modifyResponder() {

        // Make sure account was supplied
        if(!isset($_REQUEST['responder'])) {
            return PEAR::raiseError(_("Error: no Auto-Responder supplied"));
        }
        $respondername = $_REQUEST['responder'];

        // Check privs
        if(!$this->user->isUserAdmin($account, $this->domain)) {
            return PEAR::raiseError(_('Error: you do not have edit privileges on domain ') . $this->domain);
        }

        $array = explode('@', $respondername);
        $responder = $this->user->RobotGet($this->domain,$array[0]);
        print_r($responder);exit;

        if($this->user->Error) {
            return PEAR::raiseError(_('Error: ') . $this->user->Error);
        }
        $defaults = $this->user->parse_home_dotqmail($dot_qmail, $account_info);
        $form = $this->modifyAccountForm($account, $defaults);
        $renderer =& new HTML_QuickForm_Renderer_Array();
        $form->accept($renderer);
        $this->setData('form', 
            HTML_QuickForm_Renderer_AssocArray::toAssocArray($form->toArray()));
        // print_r($this->data['form']);exit;
        $this->tplFile = 'modifyAccount.tpl';
        return;

    }


    function modifyAccountForm($account, $defaults) {

        // Language stuff
        $this->setData('LANG_Modify_Account', _("Modify Account"));
        $this->setData('LANG_Domain_Menu', _("Domain Menu"));
        if($this->user->isDomainAdmin($this->domain)) {
            $this->setData('isDomainAdmin', 1);
            $this->setData('LANG_Main_Menu', _("Main Menu"));
        }
        $this->setData('account', $account);

        $form = new HTML_QuickForm('formModifyAccount', 'post', "./?module=Accounts&event=modifyAccountNow&domain={$this->domain}&account=$account");

        $form->setDefaults($defaults);

        $form->addElement('text', 'comment', _("Real Name/Comment"));
        $form->addElement('password', 'password', _("Password"));
        $form->addElement('password', 'password2', _("Re-Type Password"));
        $form->addElement('radio', 'routing', 'Mail Routing', _('Standard (No Forwarding)'), 'routing_standard');
        $form->addElement('radio', 'routing', '', _('All Mail Deleted'), 'routing_deleted');
        $form->addElement('radio', 'routing', '', _('Forward to:'), 'routing_forwarded');
        $form->addElement('text', 'forward');
        $form->addElement('checkbox', 'save_a_copy', _('Save A Copy'));

        $form->addElement('checkbox', 'vacation', _('Send a Vacation Auto-Response'));
        $form->addElement('text', 'vacation_subject', _('Vacation Subject:'));
        $form->addElement('textarea', 'vacation_body', _('Vacation Message:'), 'rows="10" cols="40"');
        $form->addElement('submit', 'submit', _('Modify Account'));

        $form->addRule(array('password', 'password2'), _('The passwords do not match'), 'compare', null, 'client');
        $form->addRule('routing', _('Please select a mail routing type'), 'required', null, 'client');
        $form->addRule('forward', _('"Forward to" must be a valid email address'), 'email', null, 'client');

        return $form;
    }


    function modifyAccountNow() {

        // Make sure account was supplied
        if(!isset($_REQUEST['account'])) {
            return PEAR::raiseError(_("Error: no account supplied"));
        }
        $account = $_REQUEST['account'];

        // Check privs
        if(!$this->user->isUserAdmin($account, $this->domain)) {
            return PEAR::raiseError(_('Error: you do not have edit privileges on domain ') . $this->domain);
        }

        // See what user_info to use
        $account_info = $this->user->UserInfo($this->domain, $_REQUEST['account']);
        if($this->user->Error) {
            return PEAR::raiseError(_('Error: ') . $this->user->Error);
        }

        // Get .qmail info if it exists
        $dot_qmail = $this->user->ReadFile($this->domain, $_REQUEST['account'], '.qmail');
        if($this->user->Error && $this->user->Error != 'command failed - -ERR 2102 No such file or directory') {
            return PEAR::raiseError(_('Error: ') . $this->user->Error);
        }
        $defaults = $this->user->parse_home_dotqmail($dot_qmail, $account_info);
        $form = $this->modifyAccountForm($account, $defaults);
        if(!$form->validate()) {
            $this->setData('message', _("Error Modifying Account"));
            $renderer =& new HTML_QuickForm_Renderer_Array();
            $form->accept($renderer);
            $this->setData('form', 
                HTML_QuickForm_Renderer_AssocArray::toAssocArray($form->toArray()));
            $this->tplFile = 'modifyAccount.tpl';
            return;
        }

        $routing = '';
        $save_a_copy = 0;
        if($_REQUEST['routing'] == 'routing_standard') {
            $routing = 'standard';
        } else if($_REQUEST['routing'] == 'routing_deleted') {
            $routing = 'deleted';
        } else if($_REQUEST['routing'] == 'routing_forwarded') {
            if(empty($_REQUEST['forward'])) {
                return PEAR::raiseError(_('Error: you must supply a forward address'));
            } else {
                $forward = $_REQUEST['forward'];
            }
            $routing = 'forwarded';
            if(isset($_REQUEST['save_a_copy'])) $save_a_copy = 1;
        } else {
            return PEAR::raiseError(_('Error: unsupported routing selection'));
        }

        // Check for vacation
        $vacation = 0;
        if(isset($_REQUEST['vacation'])) {
            $vacation = 1;
            $vacation_subject = $_REQUEST['vacation_subject'];
            $vacation_body = $_REQUEST['vacation_body'];
        }

        // Build .qmail contents
        $dot_qmail_contents = '';
        if($routing == 'deleted') {
            $dot_qmail_contents = "# delete";
        } else if($routing == 'forwarded') {
            $dot_qmail_contents = "&$forward";
            if($save_a_copy = 1) $dot_qmail_contents .= "\n./Maildir/";
        }

        if($vacation == 1) {
            if(strlen($dot_qmail_contents) > 0) $dot_qmail_contents .= "\n";
            $vacation_dir = $account_info['user_dir'] . '/vacation';
            $dot_qmail_contents .= "| $autorespond 86400 3 $vacation_dir/message $vacation_dir";
            // Example:
            // | /usr/local/bin/autorespond 86400 3 /home/vpopmail/domains/shupp.org/test/vacation/message /home/vpopmail/domains/shupp.org/test/vacation
        }

        $dot_qmail_file = $account_info['user_dir'] . '/.qmail';
        if(strlen($dot_qmail_contents) > 0) {
            $contents = explode("\n", $dot_qmail_contents);
            // Delete existing file
            $this->user->RmFile($this->domain, $account_info['name'], $dot_qmail_file);
            // Write .qmail file
            $this->user->WriteFile(explode("\n", $dot_qmail_contents), '', '', $dot_qmail_file);

            // Add vacation files
            if($vacation == 1) {
                $vcontents = "From: " . $account_info['name'] . "@{$this->domain}\n";
                $vcontents .= "Subject: $vacation_subject\n\n";
                $vcontents .= $vacation_body;
                $contents = explode("\n", $vcontents);
                $vdir = 'vacation';
                $message = 'vacation/message';
                // Delete existing file
                $this->user->RmDir($this->domain, $account_info['name'], $vdir);
                // Make vacation directory
                $this->user->MkDir($this->domain, $account_info['name'], $vdir);
                // Write vacation message
                $this->user->WriteFile($contents, $this->domain, $account_info['name'], $message);
            }
        } else {
            $this->user->RmDir($this->domain, $account_info['name'], 'vacation');
            $this->user->RmFile('', '', $dot_qmail_file);
        }

        $this->setData('message', _('Account Modified Successfully'));
        $this->modifyAccount();
    }

}

?>
