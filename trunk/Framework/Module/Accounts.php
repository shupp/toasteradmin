<?php

/**
 *
 * Accounts Module
 *
 * This module is for viewing and editing vpopmail accounts
 * succeeds
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 * @package TA_Modules
 * @version 1.0
 *
 */


class Framework_Module_Accounts extends Framework_Auth_vpopmail
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

        $this->checkPrivileges();

        // Pagintation setup
        $total = $this->user->UserCount($this->domain);
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

        // List Accounts
        $account_array = $this->user->ListUsers($this->data['domain'], $this->data['currentPage'], $this->data['limit']);
        $accounts = array();
        $count = 0;
        while(list($key,$val) = each($account_array)) {
            $accounts[$count]['account'] = $key;
            $accounts[$count]['comment'] = $val['comment'];
            $accounts[$count]['quota'] = $this->user->get_quota($val['quota']);
            $accounts[$count]['edit_url'] = htmlspecialchars("./?module=Accounts&domain={$this->domain}&account=$key&event=modify");
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

        $form = $this->addAccountForm();
        $renderer =& new HTML_QuickForm_Renderer_Array();
        $form->accept($renderer);
        // print_r($form->toArray());exit;
        $this->setData('form', $form->toArray());
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

        $form = $this->addAccountForm();
        if(!$form->validate()) {
            $renderer =& new HTML_QuickForm_Renderer_Array();
            $form->accept($renderer);
            $this->setData('form', $form->toArray());
            $this->tplFile = 'addAccount.tpl';
            return;
        }

        $emailArray = explode('@', $_REQUEST['account']);
        $this->user->AddUser($this->domain, $emailArray[0], $_REQUEST['password'], $_REQUEST['comment']);
        if($this->user->Error) {
            $this->setData('message', _("Error: ") . $this->user->Error);
            $renderer =& new HTML_QuickForm_Renderer_Array();
            $form->accept($renderer);
            $this->setData('form', $form->toArray());
            $this->tplFile = 'addAccount.tpl';
            return;
        }

        $this->setData('message', _("Account Added Successfully"));
        $this->listAccounts();
        return;
    }

    function addAccountForm() {

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



/*

} else if($_REQUEST['event'] == 'modify') {

    // Make sure account was supplied
    if(!isset($_REQUEST['account'])) {
        $tpl->set_msg_err(_('Error: no account supplied'));
        $tpl->wrap_exit();
    }
    $account = $_REQUEST['account'];

    // Check privs
    if(!$this->user->has_user_privs($account, {$this->domain})) {
        $tpl->set_msg_err(_('Error: you do not have edit privileges on domain ') . {$this->domain});
        $tpl->wrap_exit();
    }

    // See what user_info to use
    if($_REQUEST['account'] == $_SESSION['user'] && {$this->domain} == $_SESSION['domain']) {
        $account_info = $user_info;
    } else {
        $account_info = $this->user->UserInfo({$this->domain}, $_REQUEST['account']);
        if($this->user->Error) {
            $tpl->set_msg_err(_("Error: ") . $this->user->Error);
            $tpl->wrap_exit('back.tpl');
        }
    }

    // Get .qmail info if it exists
    $dot_qmail = $this->user->ReadFile({$this->domain}, $_REQUEST['account'], '.qmail');
    if($this->user->Error && $this->user->Error != 'command failed - -ERR XXX No such file or directory') {
        $tpl->set_msg_err(_("Error: ") . $this->user->Error);
        $tpl->wrap_exit('back.tpl');
    }
    $this->user->parse_home_dotqmail($dot_qmail, $account_info);
    

    // Set template data
    $this->setData('account', $_REQUEST['account']);
    $this->setData('comment', $account_info['comment']);

    $tpl->wrap_exit('modify_account.tpl');

} else if($_REQUEST['event'] == 'modify_now') {

    // Make sure account was supplied
    if(!isset($_REQUEST['account'])) {
        $tpl->set_msg_err(_('Error: no account supplied'));
        $tpl->wrap_exit();
    }
    $account = $_REQUEST['account'];

    // Check privs
    if(!$this->user->has_user_privs($account, {$this->domain})) {
        $tpl->set_msg_err(_('Error: you do not have edit privileges on domain ') . {$this->domain});
        $tpl->wrap_exit();
    }

    // See what user_info to use
    if($_REQUEST['account'] == $_SESSION['user'] && {$this->domain} == $_SESSION['domain']) {
        $account_info = $user_info;
    } else {
        $account_info = $this->user->UserInfo({$this->domain}, $_REQUEST['account']);
        if($this->user->Error) {
            $tpl->set_msg_err(_("Error: ") . $this->user->Error);
            $tpl->wrap_exit('back.tpl');
        }
    }

    // Detect password changing
    $password_changing = 0;
    if(!empty($_REQUEST['password1']) || !empty($_REQUEST['password2'])) {
        if($_REQUEST['password1'] != $_REQUEST['password2']) {
            $tpl->set_msg_err(_('Error: passwords to not match'));
            $tpl->wrap_exit('back.tpl');
        }
        $password_changing = 1;
    }

    // Get routing
    if(!isset($_REQUEST['routing'])) {
        $tpl->set_msg_err(_('Error: message routing is not set'));
        $tpl->wrap_exit('back.tpl');
    }

    $routing = '';
    $save_a_copy = 0;
    if($_REQUEST['routing'] == 'routing_standard') {
        $routing = 'standard';
    } else if($_REQUEST['routing'] == 'routing_deleted') {
        $routing = 'deleted';
    } else if($_REQUEST['routing'] == 'routing_forwarded') {
        if(empty($_REQUEST['forward'])) {
            $tpl->set_msg_err(_('Error: you must supply a forward address'));
            $tpl->wrap_exit('back.tpl');
        } else {
            $forward = $_REQUEST['forward'];
            if(!checkEmailFormat($forward)) {
                $forward = $forward . '@' . {$this->domain};
            }
        }
        $routing = 'forwarded';
        if(isset($_REQUEST['save_a_copy'])) $save_a_copy = 1;
    } else {
        $tpl->set_msg_err(_('Error: unsupported routing selection'));
        $tpl->wrap_exit('back.tpl');
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
        $this->user->RmFile({$this->domain}, $account_info['name'], $dot_qmail_file);
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
            $this->user->RmDir({$this->domain}, $account_info['name'], $vdir);
            // Make vacation directory
            $this->user->MkDir({$this->domain}, $account_info['name'], $vdir);
            // Write vacation message
            $this->user->WriteFile($contents, {$this->domain}, $account_info['name'], $message);
        }
    } else {
        $this->user->RmDir({$this->domain}, $account_info['name'], 'vacation');
        $this->user->RmFile('', '', $dot_qmail_file);
    }

    $url = $base_url . "?module={$_REQUEST['module']}&domain={$this->domain}&account={$_REQUEST['account']}&event=modify";
    $tpl->set_msg(_('Account Modified Successfully'));
    header("Location: $url");
    exit;

} else {
    $tpl->set_msg_err(_("Error: unknown event"));
    $tpl->wrap_exit();
}

*/

}

?>
