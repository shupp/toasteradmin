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
class Framework_Module_Accounts extends ToasterAdmin_Common
{

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
        if (($result = $this->noDomainSupplied())) {
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
        $this->listAccounts();
    }

    function listAccounts() {

        if (($result = $this->noDomainPrivs())) {
            return $result;
        }

        // Pagintation setup
        $total = $this->user->userCount($this->domain);
        if (PEAR::isError($total)) {
            return $total;
        }
        $this->paginate($total);

        // List Accounts
        $account_array = $this->user->listUsers($this->data['domain'], $this->data['currentPage'], $this->data['limit']);
        $accounts = array();
        $count = 0;
        while (list($key,$val) = each($account_array)) {
            $accounts[$count]['account'] = $key;
            $accounts[$count]['comment'] = $val['comment'];
            $accounts[$count]['quota'] = $this->user->getQuota($val['quota']);
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

        if (($result = $this->noDomainPrivs())) {
            return $result;
        }

        $form = $this->addAccountForm();
        $renderer =& new HTML_QuickForm_Renderer_AssocArray();
        $form->accept($renderer);
        $this->setData('form', $renderer->toAssocArray());
        $this->tplFile = 'addAccount.tpl';
        return;
    }

    function addAccountNow() {

        if (($result = $this->noDomainPrivs())) {
            return $result;
        }

        $form = $this->addAccountForm();
        if (!$form->validate()) {
            $renderer =& new HTML_QuickForm_Renderer_AssocArray();
            $form->accept($renderer);
            $this->setData('form', $renderer->toAssocArray());
            $this->tplFile = 'addAccount.tpl';
            return;
        }

        $emailArray = explode('@', $_REQUEST['account']);
        $result = $this->user->addUser($this->domain, $emailArray[0], $_REQUEST['password']);
        if(!PEAR::isError($result)) {
            // Update gecos
            $result = $this->user->modUser($this->domain, $emailArray[0], array('comment' => $_REQUEST['comment']));
        }
        if (PEAR::isError($result)) {
            $this->setData('message', _("Error: ") . $result->getMessage());
            $renderer =& new HTML_QuickForm_Renderer_AssocArray();
            $form->accept($renderer);
            $this->setData('form', $renderer->toAssocArray());
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

        if (($result = $this->noDomainPrivs())) {
            return $result;
        }

        if (!isset($_REQUEST['account'])) {
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

        if (($result = $this->noDomainPrivs())) {
            return $result;
        }

        if (!isset($_REQUEST['account'])) {
            return PEAR::raiseError(_("Error: no account supplied"));
        }

        if (!isset($_REQUEST['domain'])) {
            return PEAR::raiseError(_("Error: no domain supplied"));
        }

        $result = $this->user->delUser($this->domain, $_REQUEST['account']);
        if (PEAR::isError($result)) {
            return $result;
        }

        $this->setData('message', _("Account Deleted Successfully"));
        $this->listAccounts();
        return;
    }

    function cancelDelete() {

        if (($result = $this->noDomainPrivs())) {
            return $result;
        }

        $this->setData('message', _("Delete Canceled"));
        $this->listAccounts();
        return;
    }

    function modifyAccount() {

        // Make sure account was supplied
        if (!isset($_REQUEST['account'])) {
            return PEAR::raiseError(_("Error: no account supplied"));
        }
        $account = $_REQUEST['account'];

        // Check privs
        if (!$this->user->isUserAdmin($account, $this->domain)) {
            return PEAR::raiseError(_('Error: you do not have edit privileges on domain ') . $this->domain);
        }

        // See what user_info to use
        if ($this->user->isDomainAdmin($this->domain)) {
            $account_info = $this->user->userInfo($this->domain, $_REQUEST['account']);
            if (PEAR::isError($account_info)) return $account_info;
        } else {
            $account_info = $this->user->loginUser;
        }

        // Get .qmail info if it exists
        try {
            $dot_qmail = $this->user->readFile($this->domain, $_REQUEST['account'], '.qmail');
        } catch (Net_Vpopmaild_Exception $e) {
        }
        $defaults = $this->parseHomeDotqmail($dot_qmail, $account_info);
        $this->user->recordio(print_r($defaults, 1));
        $form = $this->modifyAccountForm($account, $defaults);
        $renderer =& new HTML_QuickForm_Renderer_AssocArray();
        $form->accept($renderer);
        if(isset($_REQUEST['modified'])) {
            $this->setData('message', _('Account Modified Successfully'));
        }
        $this->setData('form', $renderer->toAssocArray());
        $this->tplFile = 'modifyAccount.tpl';
        return;
    }

    private function modifyAccountForm($account, $defaults) {

        // Language stuff
        $this->setData('LANG_Modify_Account', _("Modify Account"));
        $this->setData('LANG_Domain_Menu', _("Domain Menu"));
        if ($this->user->isDomainAdmin($this->domain)) {
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
        if (!isset($_REQUEST['account'])) {
            return PEAR::raiseError(_("Error: no account supplied"));
        }
        $account = $_REQUEST['account'];

        // Check privs
        if (!$this->user->isUserAdmin($account, $this->domain)) {
            return PEAR::raiseError(_('Error: you do not have edit privileges on domain ') . $this->domain);
        }

        // See what user_info to use
        $account_info = $this->user->userInfo($this->domain, $_REQUEST['account']);
        if (PEAR::isError($account_info)) {
            return $account_info;
        }

        // Get .qmail info if it exists
        try {
            $dot_qmail = $this->user->readFile($this->domain, $_REQUEST['account'], '.qmail');
        } catch (Net_Vpopmaild_Exception $e) {
        }
        $defaultsOrig = $this->parseHomeDotqmail($dot_qmail, $account_info);
        $form = $this->modifyAccountForm($account, $defaultsOrig);
        if (!$form->validate()) {
            $this->setData('message', _("Error Modifying Account"));
            $renderer =& new HTML_QuickForm_Renderer_AssocArray();
            $form->accept($renderer);
            $this->setData('form', $renderer->toAssocArray());
            $this->tplFile = 'modifyAccount.tpl';
            return;
        }

        // Determine new routing
        $routing = '';
        $save_a_copy = 0;
        if ($_REQUEST['routing'] == 'routing_standard') {
            $routing = 'standard';
        } else if ($_REQUEST['routing'] == 'routing_deleted') {
            $routing = 'deleted';
        } else if ($_REQUEST['routing'] == 'routing_forwarded') {
            if (empty($_REQUEST['forward'])) {
                $this->setData('message', _('Error: you must supply a forward address'));
                return $this->modifyAccount();
            } else {
                $forward = $_REQUEST['forward'];
            }
            $routing = 'forwarded';
            if (isset($_REQUEST['save_a_copy'])) $save_a_copy = 1;
        } else {
                $this->setData('message', _('Error: unsupported routing selection'));
                return $this->modifyAccount();
        }

        // Check for vacation
        $vacation = 0;
        if (isset($_REQUEST['vacation']) && $_REQUEST['vacation'] == 1) {
            $vacation = 1;
            $vacation_subject = $_REQUEST['vacation_subject'];
            $vacation_body = $_REQUEST['vacation_body'];
        }

        // Are we deleting a vacation message?
        if ($vacation == 0 && $defaultsOrig['vacation'] == ' checked') {
            // Kill old message
            $this->user->rmDir($this->domain, $account_info['name'], 'vacation');
        }

        // Build .qmail contents
        $dot_qmail_contents = '';
        if ($routing == 'deleted') {
            $dot_qmail_contents = "# delete";
        } else if ($routing == 'forwarded') {
            $dot_qmail_contents = "&$forward";
            if ($save_a_copy == 1) $dot_qmail_contents .= "\n./Maildir/";
        }

        if ($vacation == 1) {
            if (strlen($dot_qmail_contents) > 0) $dot_qmail_contents .= "\n";
            $vacation_dir = $account_info['user_dir'] . '/vacation';
            $dot_qmail_contents .= '| ' . $this->user->vpopmailRobotProgram;
            $dot_qmail_contents .= ' ' . $this->user->vpopmailRobotTime;
            $dot_qmail_contents .= ' ' . $this->user->vpopmailRobotNumber;
            $dot_qmail_contents .= " $vacation_dir/message $vacation_dir";
        }

        $dot_qmail_file = '.qmail';
        if (strlen($dot_qmail_contents) > 0) {
            $contents = explode("\n", $dot_qmail_contents);
            // Write .qmail file
            $result = $this->user->writeFile($contents, $this->domain, $account_info['name'], $dot_qmail_file);

            // Add vacation files
            if ($vacation == 1) {
                $vcontents = "From: " . $account_info['name'] . "@{$this->domain}\n";
                $vcontents .= "Subject: $vacation_subject\n\n";
                $vcontents .= $vacation_body;
                $contents = explode("\n", $vcontents);
                $vdir = 'vacation';
                $message = 'vacation/message';
                // Delete existing file
                try {
                    $this->user->rmDir($this->domain, $account_info['name'], $vdir);
                } catch (Net_Vpopmaild_Exception $e) {
                }
                // Make vacation directory
                $result = $this->user->mkDir($this->domain, $account_info['name'], $vdir);
                // Write vacation message
                $result = $this->user->writeFile($contents, $this->domain, $account_info['name'], $message);
            }
        } else {
            try {
                $this->user->rmFile($this->domain, $account_info['name'], $dot_qmail_file);
            } catch (Net_Vpopmaild_Exception $e) {
            }
        }

        $url = "./?module=Accounts&event=modifyAccount&domain={$this->domain}&account={$account_info['name']}&modified=1";
        header("Location: $url");
        return $this->modifyAccount();
    }

    /**
     * Parse Home dot-qmail
     *
     * Evaluate contents of a .qmail file in a user's home directory.
     * Looking for routing types standard, delete, or forward, with optional
     * saving of messages, as well as vacation messages.
     *
     * @param mixed $contents     .qmail contents
     * @param mixed $account_info user account info
     *
     * @access protected
     * @return array $defaults
     */
    protected function parseHomeDotqmail($contents, $account_info)
    {
        $is_standard  = false;
        $is_deleted   = false;
        $is_forwarded = false;
        // Set default template settings
        $defaults['comment']          = $account_info['comment'];
        $defaults['forward']          = '';
        $defaults['save_a_copy']      = '';
        $defaults['vacation']         = '';
        $defaults['vacation_subject'] = '';
        $defaults['vacation_body']    = '';
        if (empty($contents)) {
            $is_standard = true;
        }
        if ((is_array($contents)
            && count($contents) == 1
            && $contents[0] == '# delete')) {
            $is_deleted = true;
        }
        if ($is_standard) {
            $defaults['routing'] = 'routing_standard';
        } else if ($is_deleted) {
            $defaults['routing'] = 'routing_deleted';
        } else {
            // now let's parse it
            while (list($key, $val) = each($contents)) {
                if ($val == $account_info['user_dir'].'/Maildir/'
                    || $val == './Maildir/') {

                    $defaults['save_a_copy'] = ' checked';
                    continue;
                }
                if (preg_match("({$this->user->vpopmailRobotProgram})", $val)) {
                    $vacation_array = $this->user->getVacation($account_info, $val);

                    while (list($vacKey, $vacVal) = each($vacation_array)) {
                        $defaults[$vacKey] = $vacVal;
                    }
                    continue;
                } else {
                    if (Validate::email(preg_replace('/^&/', '', $val),
                        array('use_rfc822' => 1))) {

                        $is_forwarded        = true;
                        $defaults['routing'] = 'routing_forwarded';
                        $defaults['forward'] = preg_replace('/^&/', '', $val);
                    }
                }
            }
            // See if default routing select applies
            if (!$is_standard && !$is_deleted && !$is_forwarded) {
                $defaults['routing'] = 'routing_standard';
            }
        }
        return $defaults;
    }

}

?>
