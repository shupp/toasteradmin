<?php

/**
 * Framework_Module_Accounts 
 * 
 * This module is for viewing and editing vpopmail accounts
 * 
 * PHP Version 5.1.0+
 * 
 * @category  Mail
 * @package   ToasterAdmin
 * @uses      ToasterAdmin_Auth_Domain
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2007-2008 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://trac.merchbox.com/trac/toasteradmin
 */

/**
 * Framework_Module_Accounts 
 * 
 * This module is for viewing and editing vpopmail accounts
 * 
 * @category  Mail
 * @package   ToasterAdmin
 * @uses      ToasterAdmin_Auth_Domain
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2007-2008 Bill Shupp
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
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->noDomainSupplied();
    }

    /**
     * __default 
     * 
     * @access public
     * @return void
     */
    public function __default()
    {
        $this->listAccounts();
    }

    /**
     * listAccounts 
     * 
     * List accounts for the current domain
     * 
     * @access public
     * @return void
     */
    public function listAccounts()
    {
        // Pagintation setup - let exception bubble up
        $total = $this->user->userCount($this->domain);
        $this->paginate($total);

        // List Accounts
        $a    = array();
        $c    = 0;
        $list = $this->user->listUsers($this->data['domain'],
            $this->data['currentPage'], $this->data['limit']);
        foreach ($list as $key => $val) {
            $eus  =  "./?module=Accounts&class=Modify&domain={$this->domain}";
            $eus .= "&account=$key&event=modifyAccount";
            $lus  =  "./?module=Accounts&class=Limits&domain={$this->domain}";
            $lus .= "&account=$key";
            $dus  = "./?module=Accounts&domain={$this->domain}";
            $dus .= "&account=$key&event=delete";

            $a[$c]['account']    = $key;
            $a[$c]['comment']    = $val['comment'];
            $a[$c]['quota']      = $this->user->getQuota($val['quota']);
            $a[$c]['edit_url']   = htmlspecialchars($eus);
            $a[$c]['limits_url'] = htmlspecialchars($lus);
            $a[$c]['delete_url'] = htmlspecialchars($dus);
            $c++;
        }
        if ($this->user->isSysAdmin()) {
            $this->setData('isSysAdmin', 1);
        }
        $aus = "./?module=Accounts&event=addAccount&domain={$this->domain}";
        $this->setData('add_account_url', htmlspecialchars($aus));
        $this->setData('accounts', $a);
        $this->tplFile = 'listAccounts.tpl';
        return;
    }

    /**
     * addAccount 
     * 
     * @access public
     * @return void
     */
    public function addAccount()
    {
        $form     = $this->addAccountForm();
        $renderer = new HTML_QuickForm_Renderer_AssocArray();
        $form->accept($renderer);
        $this->setData('form', $renderer->toAssocArray());
        $this->tplFile = 'addAccount.tpl';
        return;
    }

    /**
     * addAccountNow 
     * 
     * Actually add account, then list accounts
     * 
     * @access public
     * @return void
     */
    public function addAccountNow()
    {
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
            $result = $this->user->addUser($this->domain, $emailArray[0],
                $_REQUEST['password']);
        } catch (Net_Vpopmaild_Exception $e) {
            $this->setData('message', _("Error: ") . $e->getMessage());
            $renderer =& new HTML_QuickForm_Renderer_AssocArray();
            $form->accept($renderer);
            $this->setData('form', $renderer->toAssocArray());
            $this->tplFile = 'addAccount.tpl';
            return;
        }
        // Update gecos
        $userInfo            = $this->user->userInfo($this->domain, $emailArray[0]);
        $userInfo['comment'] = $_REQUEST['comment'];

        $result = $this->user->modUser($this->domain, $userInfo['name'], $userInfo);

        $this->setData('message', _("Account Added Successfully"));
        $this->listAccounts();
        return;
    }

    /**
     * addAccountForm 
     * 
     * Create HTML_QuickForm instance of add account form
     * 
     * @access protected
     * @return object HTML_QuickForm object
     */
    protected function addAccountForm()
    {
        $form = new HTML_QuickForm('formAddAccount', 'post',
            "./?module=Accounts&event=addAccountNow&domain={$this->domain}");
        $form->setDefaults(array('account' => '@' . $this->domain));

        $form->addElement('text', 'account', _("Account"));
        $form->addElement('text', 'comment', _("Real Name/Comment"));
        $form->addElement('text', 'password', _("Password"));
        $form->addElement('submit', 'submit', _("Add Account"));

        $form->registerRule('sameDomain', 'regex', "/@$this->domain$/i");

        $form->addRule('account',
            _("Account is required"), 'required', null, 'client');
        $form->addRule('comment',
            _("Comment is required"), 'required', null, 'client');
        $form->addRule('account',
            _("Account must be the full email address"), 'email', null, 'client');
        $form->addRule('account',
            _('Error: wrong domain in email address'), 'sameDomain');
        $form->addRule('password',
            _("Password is required"), 'required', null, 'client');
        return $form;
    }

    /**
     * delete 
     * 
     * Display delete account confirmation
     * 
     * @access public
     * @return void
     */
    public function delete()
    {
        if (!isset($_REQUEST['account'])) {
            throw new Framework_Exception (_("Error: no account supplied"));
        }

        $this->setData('account', $_REQUEST['account']);
        $this->setData('cancel_url',
            "./?module=Accounts&event=cancelDelete&domain=" . $this->domain);
        $this->setData('delete_now_url',
            "./?module=Accounts&event=deleteNow&domain="
            . $this->domain . "&account=" . $_REQUEST['account']);
        $this->tplFile = 'accountConfirmDelete.tpl';
    }

    /**
     * deleteNow 
     * 
     * Actuall delete the account, then list accounts
     * 
     * @access public
     * @return void
     */
    public function deleteNow()
    {
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

    /**
     * cancelDelete 
     * 
     * Cancel account deletion and list accounts
     * 
     * @access public
     * @return void
     */
    public function cancelDelete()
    {
        $this->setData('message', _("Delete Canceled"));
        $this->listAccounts();
        return;
    }

}

?>
