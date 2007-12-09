<?php

/**
 * Framework_Module_Accounts 
 * 
 * This module is for viewing and editing vpopmail accounts
 * 
 * @package   ToasterAdmin
 * @uses      ToasterAdmin_Auth_Domain
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2007 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://trac.merchbox.com/trac/toasteradmin
 */

/**
 * Framework_Module_Accounts 
 * 
 * This module is for viewing and editing vpopmail accounts
 * 
 * @package   ToasterAdmin
 * @uses      ToasterAdmin_Auth_Domain
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2007 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://trac.merchbox.com/trac/toasteradmin
 */
class Framework_Module_Accounts extends ToasterAdmin_Auth_Domain
{

    /**
     * __construct 
     * 
     * Throw an exception if no domain was supplied
     * 
     * @access protected
     * @return void
     */
    function __construct() {
        parent::__construct();
        $this->noDomainSupplied();
    }

    /**
     * __default 
     * 
     * @access protected
     * @return void
     */
    public function __default() {
        $this->listAccounts();
    }

    public function listAccounts() {
        // Pagintation setup - let exception bubble up
        $total = $this->user->userCount($this->domain);
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
    public function addAccount() {
        $form = $this->addAccountForm();
        $renderer =& new HTML_QuickForm_Renderer_AssocArray();
        $form->accept($renderer);
        $this->setData('form', $renderer->toAssocArray());
        $this->tplFile = 'addAccount.tpl';
        return;
    }

    public function addAccountNow() {
        $form = $this->addAccountForm();
        if (!$form->validate()) {
            $renderer =& new HTML_QuickForm_Renderer_AssocArray();
            $form->accept($renderer);
            $this->setData('form', $renderer->toAssocArray());
            $this->tplFile = 'addAccount.tpl';
            return;
        }

        $emailArray = explode('@', $_REQUEST['account']);
        try {
            $result = $this->user->addUser($this->domain, $emailArray[0], $_REQUEST['password']);
        } catch (Net_Vpopmaild_Exception $e) {
            $this->setData('message', _("Error: ") . $e->getMessage());
            $renderer =& new HTML_QuickForm_Renderer_AssocArray();
            $form->accept($renderer);
            $this->setData('form', $renderer->toAssocArray());
            $this->tplFile = 'addAccount.tpl';
            return;
        }
        // Update gecos
        $result = $this->user->modUser($this->domain, $emailArray[0], array('comment' => $_REQUEST['comment']));

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

    public function delete() {
        if (!isset($_REQUEST['account'])) {
            throw new Framework_Exception (_("Error: no account supplied"));
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
        if (!isset($_REQUEST['account'])) {
            throw new Framework_Exception (_("Error: no account supplied"));
        }
        if (!isset($_REQUEST['domain'])) {
            throw new Framework_Exception (_("Error: no domain supplied"));
        }

        $result = $this->user->delUser($this->domain, $_REQUEST['account']);

        $this->setData('message', _("Account Deleted Successfully"));
        $this->listAccounts();
        return;
    }

    function cancelDelete() {
        $this->setData('message', _("Delete Canceled"));
        $this->listAccounts();
        return;
    }

}

?>
