<?php

/**
 * Framework_Module_Accounts_Modify 
 * 
 * Class just for modifying accounts
 * 
 * PHP Version 5.1.0+
 * 
 * @uses      ToasterAdmin_Auth_User
 * @category  Mail
 * @package   ToasterAdmin
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2007-2008 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://trac.merchbox.com/trac/toasteradmin
 */

/**
 * Framework_Module_Accounts_Modify 
 * 
 * Class just for modifying accounts
 * 
 * @uses       ToasterAdmin_Auth_User
 * @category   Mail
 * @package    ToasterAdmin
 * @subpackage Module
 * @author     Bill Shupp <hostmaster@shupp.org> 
 * @copyright  2007-2008 Bill Shupp
 * @license    GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link       http://trac.merchbox.com/trac/toasteradmin
 */
class Framework_Module_Accounts_Modify extends ToasterAdmin_Auth_User
{

    /**
     * __default 
     * 
     * Execute modifyAccount()
     * 
     * @access public
     * @return void
     */
    public function __default()
    {
        return $this->modifyAccount();
    }

    /**
     * modifyAccount 
     * 
     * Modify a user account
     * 
     * @access public
     * @return void
     */
    public function modifyAccount()
    {
        // Make sure account was supplied
        if (!isset($_REQUEST['account'])) {
            throw new Framework_Exception (_("Error: no account supplied"));
        }
        $account = $_REQUEST['account'];
        if ($this->user->isDomainAdmin($this->domain)) {
            $account_info = $this->user->userInfo($this->domain, $account);
        } else {
            $account_info = $this->user->loginUser;
        }

        // Get .qmail info if it exists
        try {
            $dot_qmail = $this->user->readFile($this->domain,
                $_REQUEST['account'], '.qmail');
        } catch (Net_Vpopmaild_Exception $e) {
            $dot_qmail = '';
        }
        $defaults = $this->parseHomeDotqmail($dot_qmail, $account_info);
        $this->user->recordio(print_r($defaults, 1));

        $form     = $this->modifyAccountForm($account, $defaults);
        $renderer = new HTML_QuickForm_Renderer_AssocArray();
        $form->accept($renderer);
        if (isset($_REQUEST['modified'])) {
            $this->setData('message', _('Account Modified Successfully'));
        }
        $this->setData('form', $renderer->toAssocArray());
        $this->tplFile = 'modifyAccount.tpl';
        return;
    }

    /**
     * modifyAccountForm 
     * 
     * Build the modify account form
     * 
     * @param mixed $account  account name
     * @param mixed $defaults defaults
     * 
     * @access protected
     * @return void
     */
    protected function modifyAccountForm($account, $defaults)
    {

        // Language stuff
        if ($this->user->isDomainAdmin($this->domain)) {
            $this->setData('isDomainAdmin', 1);
        }
        $this->setData('account', $account);

        $url  =  "./?module=Accounts&class=Modify&event=modifyAccountNow";
        $url .= "&domain={$this->domain}&account=$account";
        $form = new HTML_QuickForm('formModifyAccount', 'post', $url);

        $form->setDefaults($defaults);

        $form->addElement('text', 'comment', _("Real Name/Comment"));
        $form->addElement('password', 'password', _("Password"));
        $form->addElement('password', 'password2', _("Re-Type Password"));
        $form->addElement('radio', 'routing', 'Mail Routing',
            _('Standard (No Forwarding)'), 'routing_standard');
        $form->addElement('radio', 'routing', '',
            _('All Mail Deleted'), 'routing_deleted');
        $form->addElement('radio', 'routing', '',
            _('Forward to:'), 'routing_forwarded');
        $form->addElement('text', 'forward');
        $form->addElement('checkbox', 'save_a_copy', _('Save A Copy'));

        $form->addElement('checkbox', 'vacation',
            _('Send a Vacation Auto-Response'));
        $form->addElement('text', 'vacation_subject',
            _('Vacation Subject:'));
        $form->addElement('textarea', 'vacation_body',
            _('Vacation Message:'), 'rows="10" cols="40"');
        $form->addElement('submit', 'submit', _('Modify Account'));

        $form->addRule(array('password', 'password2'),
            _('The passwords do not match'), 'compare', null, 'client');
        $form->addRule('routing',
            _('Please select a mail routing type'), 'required', null, 'client');
        $form->addRule('forward',
            _('"Forward to" must be a valid email address'),
            'email', null, 'client');

        return $form;
    }

    /**
     * modifyAccountNow 
     * 
     * Modify Acount
     * 
     * @access public
     * @return void
     */
    public function modifyAccountNow()
    {
        // Make sure account was supplied
        if (!isset($_REQUEST['account'])) {
            throw new Framework_Exception (_("Error: no account supplied"));
        }
        $account = $_REQUEST['account'];

        // See what user_info to use
        if ($this->user->isDomainAdmin($this->domain)) {
            $account_info = $this->user->userInfo($this->domain, $account);
        } else {
            $account_info = $this->user->loginUser;
        }

        // Get .qmail info if it exists
        try {
            $dot_qmail = $this->user->readFile($this->domain,
                $_REQUEST['account'], '.qmail');
        } catch (Net_Vpopmaild_Exception $e) {
            $dot_qmail = '';
        }
        $defs = $this->parseHomeDotqmail($dot_qmail, $account_info);
        $form = $this->modifyAccountForm($account, $defs);
        if (!$form->validate()) {
            $this->setData('message', _("Error Modifying Account"));
            $renderer =& new HTML_QuickForm_Renderer_AssocArray();
            $form->accept($renderer);
            $this->setData('form', $renderer->toAssocArray());
            $this->tplFile = 'modifyAccount.tpl';
            return;
        }

        // update password / comment if it's changing
        $changePass    = 0;
        $changeComment = 0;
        $password      = $form->getElementValue('password');
        $comment       = $form->getElementValue('comment');
        if (!empty($password)) {
            $account_info['clear_text_password'] = $password;
            $changePass = 1;
        }
        if (!empty($comment)) {
            $account_info['comment'] = $comment;
        }
        if ($changePass || $changeComment) {
            $this->user->modUser($this->domain, $_REQUEST['account'], $account_info);
        }
        if ($changePass && $account == $this->user->loginUser['name'] 
                && $this->domain == $this->user->loginUser['domain']) {
            $crypt = new Crypt_Blowfish((string)Framework::$site->config->mcryptKey);
            $this->session->password = $crypt->encrypt($password);
        }

        // Determine new routing
        $routing     = '';
        $save_a_copy = 0;
        if ($_REQUEST['routing'] == 'routing_standard') {
            $routing = 'standard';
        } else if ($_REQUEST['routing'] == 'routing_deleted') {
            $routing = 'deleted';
        } else if ($_REQUEST['routing'] == 'routing_forwarded') {
            if (empty($_REQUEST['forward'])) {
                $this->setData('message',
                    _('Error: you must supply a forward address'));
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
            $vacation         = 1;
            $vacation_subject = $_REQUEST['vacation_subject'];
            $vacation_body    = $_REQUEST['vacation_body'];
        }

        // Are we deleting a vacation message?
        if ($vacation == 0 && $defs['vacation'] == ' checked') {
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
            if (strlen($dot_qmail_contents) > 0) {
                $dot_qmail_contents .= "\n";
            }
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
            $result = $this->user->writeFile($contents, $this->domain,
                $account_info['name'], $dot_qmail_file);

            // Add vacation files
            if ($vacation == 1) {
                $vcontents  = "From: " . $account_info['name'] . "@{$this->domain}";
                $vcontents .= "\n";
                $vcontents .= "Subject: $vacation_subject\n\n";
                $vcontents .= $vacation_body;
                $contents   = explode("\n", $vcontents);
                $vdir       = 'vacation';
                $message    = 'vacation/message';
                // Delete existing file
                try {
                    $this->user->rmDir($this->domain, $account_info['name'], $vdir);
                } catch (Net_Vpopmaild_Exception $e) {
                }
                // Make vacation directory
                $result = $this->user->mkDir($this->domain,
                    $account_info['name'], $vdir);
                // Write vacation message
                $result = $this->user->writeFile($contents, $this->domain,
                    $account_info['name'], $message);
            }
        } else {
            try {
                $this->user->rmFile($this->domain,
                    $account_info['name'], $dot_qmail_file);
            } catch (Net_Vpopmaild_Exception $e) {
            }
        }

        $url  = "./?module=Accounts&class=Modify&event=modifyAccount";
        $url .= "&domain={$this->domain}&account={$account_info['name']}&modified=1";
        header("Location: $url");
        return;
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
