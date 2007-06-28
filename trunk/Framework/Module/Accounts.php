<?php

/**
 *
 * Accounts Module
 *
 * This module is for viewing and editing vpopmail accounts
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 * @package ToasterAdmin
 * @version 1.0
 *
 */


/**
 * Framework_Module_Accounts 
 * 
 * @package ToasterAdmin
 * @copyright 2005-2006 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @license GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */
class Framework_Module_Accounts extends Framework_Auth_Vpopmail
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
        $this->listAccounts();
    }

    function listAccounts() {

        $privs = $this->checkPrivileges();
        if(PEAR::isError($privs)) return privs;

        // Pagintation setup
        $total = $this->user->userCount($this->domain);
        if(PEAR::isError($total)) return $total;
        $this->paginate($total);

        // List Accounts
        $account_array = $this->user->listUsers($this->data['domain'], $this->data['currentPage'], $this->data['limit']);
        $accounts = array();
        $count = 0;
        while(list($key,$val) = each($account_array)) {
            $accounts[$count]['account'] = $key;
            $accounts[$count]['comment'] = $val['comment'];
            $accounts[$count]['quota'] = $this->user->get_quota($val['quota']);
            $accounts[$count]['edit_url'] = htmlspecialchars("./?module=Accounts&domain={$this->domain}&account=$key&event=modifyAccount");
            $accounts[$count]['delete_url'] = htmlspecialchars("./?module=Accounts&domain={$this->domain}&account=$key&event=delete");
            $count++;
        }
        $this->setData('accounts', $accounts);
        $this->setData('add_account_url', htmlspecialchars("./?module=Accounts&event=addAccount&domain={$this->domain}"));

        // Language
        $this->setData('LANG_Email_Accounts_in_domain', _('Email Accounts in domain'));
        $this->setData('LANG_Accounts_Page', _('Accounts: Page'));
        $this->setData('LANG_Add_Account', _('Add Account'));
        $this->setData('LANG_Domain_Menu', _('Domain Menu'));
        $this->setData('LANG_of', _('of'));
        $this->setData('LANG_Account', _('Account'));
        $this->setData('LANG_Comment', _('Comment'));
        $this->setData('LANG_Quota', _('Quota'));
        $this->setData('LANG_Edit', _('Edit'));
        $this->setData('LANG_Delete', _('Delete'));
        $this->setData('LANG_edit', _('edit'));
        $this->setData('LANG_delete', _('delete'));

        $this->tplFile = 'listAccounts.tpl';
        return;
    }

    /**
     * addAccount 
     * 
     * @access public
     * @return void
     */
    function addAccount() {

        $privs = $this->checkPrivileges();
        if(PEAR::isError($privs)) return privs;

        $form = $this->addAccountForm();
        $renderer =& new HTML_QuickForm_Renderer_Array();
        $form->accept($renderer);
        $this->setData('form', 
            HTML_QuickForm_Renderer_AssocArray::toAssocArray($form->toArray()));
        $this->tplFile = 'addAccount.tpl';
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

    function addAccountNow() {

        $privs = $this->checkPrivileges();
        if(PEAR::isError($privs)) return privs;

        $form = $this->addAccountForm();
        if(!$form->validate()) {
            $renderer =& new HTML_QuickForm_Renderer_Array();
            $form->accept($renderer);
            $this->setData('form', 
                HTML_QuickForm_Renderer_AssocArray::toAssocArray($form->toArray()));
            $this->tplFile = 'addAccount.tpl';
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
        $this->listAccounts();
        return;
    }

    private function addAccountForm() {

        $this->setData('LANG_Add_Account_to_domain', _("Add Account to domain"));
        $this->setData('LANG_Domain_Menu', _("Domain Menu"));
        $form = new HTML_QuickForm('formAddAccount', 'post', "./?module=Accounts&event=addAccountNow&domain={$this->domain}");

        $form->setDefaults(array('account' => '@' . $this->domain));

        $form->addElement('text', 'account', _("Account"));
        $form->addElement('text', 'comment', _("Real Name/Comment"));
        $form->addElement('text', 'password', _("Password"));
        $form->addElement('submit', 'submit', _("Add Account"));

        $form->registerRule('sameDomain', 'regex', "/@$this->domain$/i");

        $form->addRule('account', "Account is required", 'required', null, 'client');
        $form->addRule('comment', "Comment is required", 'required', null, 'client');
        $form->addRule('account', "Account must be the full email address", 'email', null, 'client');
        $form->addRule('account', 'Error: wrong domain in email address', 'sameDomain');
        $form->addRule('password', "Password is required", 'required', null, 'client');
        return $form;
    }

    function delete() {

        $privs = $this->checkPrivileges();
        if(PEAR::isError($privs)) return privs;

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

        $privs = $this->checkPrivileges();
        if(PEAR::isError($privs)) return privs;

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

        $privs = $this->checkPrivileges();
        if(PEAR::isError($privs)) return privs;

        $this->setData('message', _("Delete Canceled"));
        $this->listAccounts();
        return;
    }

    function modifyAccount() {

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
        if($this->user->isDomainAdmin($this->domain)) {
            $account_info = $this->user->userInfo($this->domain, $_REQUEST['account']);
            if(PEAR::isError($account_info)) return $account_info;
        } else {
            $account_info = $this->user->loginUser;
        }

        // Get .qmail info if it exists
        $dot_qmail = $this->user->ReadFile($this->domain, $_REQUEST['account'], '.qmail');
        if($this->user->Error && $this->user->Error != 'command failed - -ERR 2102 No such file or directory') {
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


    private function modifyAccountForm($account, $defaults) {

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
