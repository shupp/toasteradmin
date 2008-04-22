<?php

/**
 * Framework_Module_Accounts_Limits 
 * 
 * PHP Version 5.1.0+
 * 
 * @uses       ToasterAdmin_Auth_System
 * @category   Mail
 * @package    ToasterAdmin
 * @subpackage Module
 * @author     Bill Shupp <hostmaster@shupp.org> 
 * @copyright  2007-2008 Bill Shupp
 * @license    GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link       http://trac.merchbox.com/trac/toasteradmin
 */

/**
 * Framework_Module_Accounts_Limits 
 * 
 * Modify Account Limits
 * 
 * @uses       ToasterAdmin_Auth_System
 * @category   Mail
 * @package    ToasterAdmin
 * @subpackage Module
 * @author     Bill Shupp <hostmaster@shupp.org> 
 * @copyright  2007-2008 Bill Shupp
 * @license    GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link       http://trac.merchbox.com/trac/toasteradmin
 */
class Framework_Module_Accounts_Limits extends ToasterAdmin_Auth_System
{

    /**
     * __construct 
     * 
     * Make sure an account was supplied
     * 
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        if (!isset($_GET['account'])) {
            throw new Framework_Exception(_('Error: no account supplied'));
        }
        if (!preg_match('/[a-zA-Z0-9_.-]+/i', $_GET['account'])) {
            throw new Framework_Exception(_('Error: invalid account supplied'));
        }
        $this->account = $_GET['account'];
    }

    /**
     * __default 
     * 
     *  run modifyLimits();
     * 
     * @access public
     * @return void
     */
    public function __default()
    {
        return $this->modifyLimits();
    }

    /**
     * modifyLimits
     * 
     * Modify Account Limits
     * 
     * @access public
     * @return void
     */
    public function modifyLimits()
    {
        $form = $this->limitsForm();
        $this->_renderForm($form);
        return;
    }

    /**
     * modifyLimitsNow 
     * 
     * Modify domain limits
     * 
     * @access public
     * @return void
     */
    public function modifyLimitsNow()
    {
        $form = $this->limitsForm();
        if (!$form->validate()) {
            return $this->_renderForm($form);
        }
        try {
            $form->process(array($this, 'processForm'));
        } catch (Net_Vpopmaild_Exception $e) {
            $this->setData('message', $e->getMessage);
            return $this->_renderForm($form);
        }
        $this->setData('message', 'Limits Modified Successfully');
        return $this->_renderForm($form);
    }


    /**
     * limitsForm 
     * 
     * Create limits form
     * 
     * @access protected
     * @return HTML_QuickForm object
     */
    protected function limitsForm()
    {
        $defaults = array();
        $params   = $this->user->getModUserParms();

        $this->userInfo = $this->user->userInfo($this->domain, $this->account);
        foreach ($params['flagParms'] as $param) {
            $defaults[$param] = $this->user->getGidBit($this->userInfo['gidflags'],
            $param);
        }
        // To MB
        $quota = (int)$this->userInfo['quota'];
        if ($quota > 0) {
            $quota = ((int)$quota / 1024) / 1024;
        }
        $defaults['quota'] = $quota;

        $url  = './?module=Accounts&class=Limits&event=modifyLimitsNow&';
        $url .= "&account={$this->account}&domain={$this->domain}";
        $form = ToasterAdmin_Form::factory('limitsForm', $url);
        $form->setDefaults($defaults);

        $form->addElement('text', 'quota',
            _('Quota in MB (0 for unlimited)'), array('size' => 4));
        $form->addElement('checkbox', 'no_pop', _('Disable POP'));
        $form->addElement('checkbox', 'no_imap', _('Disable IMAP'));
        $form->addElement('checkbox', 'no_dialup', _('Disable Dial-Up'));
        $form->addElement('checkbox', 'bounce_mail', _('Bounce Mail'));
        $form->addElement('checkbox', 'no_password_changing',
            _('Disable Password Changing'));
        $form->addElement('checkbox', 'no_webmail',
            _('Disable Webmail (SqWebmail)'));
        $form->addElement('checkbox', 'no_external_relay',
            _('Disable Relaying'));
        $form->addElement('checkbox', 'no_smtp',
            _('Disable SMTP-AUTH'));
        $form->addElement('checkbox', 'system_admin_privileges',
            _('SysAdmin Privileges'));
        $form->addElement('checkbox', 'system_expert_privileges',
            _('System Expert Privileges'));
        $form->addElement('checkbox', 'domain_admin_privileges',
            _('Domain Admin Privileges'));
        $form->addElement('checkbox', 'override_domain_limits',
            _('Override Domain Limits'));
        $form->addElement('checkbox', 'no_spamassassin',
            _('Disable SpamAssassin'));
        $form->addElement('checkbox', 'no_maildrop',
            _('Disable Maildrop Processing'));
        $form->addElement('checkbox', 'delete_spam',
            _('Delete Spam'));
        $form->addElement('checkbox', 'user_flag_0',
            _('User Flag 0'));
        $form->addElement('checkbox', 'user_flag_1',
            _('User Flag 1'));
        $form->addElement('checkbox', 'user_flag_2',
            _('User Flag 2'));
        $form->addElement('checkbox', 'user_flag_3',
            _('User Flag 3'));
        $form->addElement('submit', 'submit', _('Modify'));

        $form->registerRule('zero', 'regex', '/^(0|[1-9][0-9]+)$/');
        $form->addRule('quota',
            _('Error: only integers of 0 and greater are allowed for quotas'),
            'zero', null, 'client');
        $form->addRule('quota',
            _('Error: only integers of 0 and greater are allowed for quotas'),
            'numeric', null, 'client');
        $form->applyFilter('__ALL__', 'trim');

        return $form;
    }

    /**
     * processForm 
     * 
     * Process the form
     * 
     * @param array $limits array of form elements
     * 
     * @access public
     * @return void
     */
    public function processForm($limits)
    {
        $params    = $this->user->getModUserParms();
        $flagParms = $params['flagParms'];
        $userInfo  = $this->userInfo;
        // Don't pass submit to vpopmaild
        if (isset($limits['submit'])) {
            unset($limits['submit']);
        }
        // From MB
        if ($limits['quota'] > 0) {
            $limits['quota'] = ($limits['quota'] * 1024) * 1024;
        }
        // Convert 0 to NOQUOTA
        $userInfo['quota'] = $limits['quota'] > 0 ? $limits['quota'] : 'NOQUOTA';
        unset($limits['quota']);
        // Set disable flags to zero if not set
        foreach ($flagParms as $flag) {
            if (isset($limits[$flag])) {
                $value = 1;
            } else {
                $value = 0;
            }
            $this->user->setGidBit($userInfo['gidflags'], $flag, $value);
        }
        // Now process
        $this->user->modUser($this->domain, $this->account, $userInfo);
    }

    /**
     * _renderForm 
     * 
     * @param object $form Instance of HTML_QuikForm
     * 
     * @access private
     * @return void
     */
    private function _renderForm($form)
    {
        $this->setData('limitsForm', $form->toHtml());
        // Display find form
        $this->tplFile = 'limits.tpl';
    }

}
?>
