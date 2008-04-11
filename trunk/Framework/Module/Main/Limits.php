<?php

/**
 * Framework_Module_Main_Limits 
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
 * Framework_Module_Main_Limits 
 * 
 * Modify IP Maps
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
class Framework_Module_Main_Limits extends ToasterAdmin_Auth_System
{

    /**
     * __construct 
     * 
     * Make sure a domain was supplied
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
     * Modify Domain Limits
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
        $defaults = $this->user->getLimits($this->domain);
        // To MB
        if ($defaults['default_quota'] > 0) {
            $defaults['default_quota'] = ($defaults['default_quota'] / 1024) / 1024;
        }
        $url  = './?module=Main&class=Limits&event=modifyLimitsNow&domain=';
        $url .= $this->domain;
        $form = new HTML_QuickForm('limitsForm', 'post', $url);
        $form->setDefaults($defaults);

        $form->addElement('text', 'max_aliases',
            _('Maximum Aliases (-1 for unlimited)'), array('size' => 4));
        $form->addElement('text', 'max_forwards',
            _('Maximum Forwards (-1 for unlimited)'), array('size' => 4));
        $form->addElement('text', 'max_autoresponders',
            _('Maximum Mail Robots (-1 for unlimited)'), array('size' => 4));
        $form->addElement('text', 'max_mailinglists',
            _('Maximum EZMLM-IDX Mailing Lists (-1 for unlimited)'),
            array('size' => 4));
        $form->addElement('text', 'default_quota',
            _('Default Quota in MB (0 for unlimited)'), array('size' => 4));
        $form->addElement('text', 'default_maxmsgcount',
            _('Default Message Count Limit (0 for unlimited)'),
            array('size' => 4));
        $form->addElement('checkbox', 'disable_pop', _('Disable POP'));
        $form->addElement('checkbox', 'disable_imap', _('Disable IMAP'));
        $form->addElement('checkbox', 'disable_dialup', _('Disable Dial-Up'));
        $form->addElement('checkbox', 'disable_password_changing',
            _('Disable Password Changing'));
        $form->addElement('checkbox', 'disable_webmail',
            _('Disable Webmail (SqWebmail)'));
        $form->addElement('checkbox', 'disable_external_relay',
            _('Disable Relaying'));
        $form->addElement('checkbox', 'disable_smtp',
            _('Disable SMTP-AUTH'));
        $form->addElement('submit', 'submit', _('Modify'));

        $form->registerRule('minusOne', 'regex', '/^(-1|[0-9]+)$/');
        $form->registerRule('zero', 'regex', '/^(0|[1-9][0-9]+)$/');
        $form->addRule('max_aliases',
            _('Error: only integers of -1 and greater allowed here'),
            'minusOne', null, 'client');
        $form->addRule('max_forwards',
            _('Error: only integers of -1 and greater allowed here'),
            'minusOne', null, 'client');
        $form->addRule('max_autoresponders',
            _('Error: only integers of -1 and greater allowed here'),
            'minusOne', null, 'client');
        $form->addRule('max_mailinglists',
            _('Error: only integers of -1 and greater allowed here'),
            'minusOne', null, 'client');
        $form->addRule('default_quota',
            _('Error: only integers of 0 and greater allowed here'),
            'zero', null, 'client');
        $form->addRule('default_maxmsgcount',
            _('Error: only integers of 0 and greater allowed here'),
            'zero', null, 'client');
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
        $disabled = array(
            'disable_pop',
            'disable_imap',
            'disable_dialup',
            'disable_password_changing',
            'disable_webmail',
            'disable_external_relay',
            'disable_smtp'
        );
        // Don't pass submit to vpopmaild
        if (isset($limits['submit'])) {
            unset($limits['submit']);
        }
        // From MB
        if (isset($limits['default_quota'])) {
            $limits['default_quota'] = ($limits['default_quota'] * 1024) * 1024;
        }
        // Set disable flags to zero if not set
        foreach ($disabled as $item) {
            if (!isset($limits[$item])) {
                $limits[$item] = 0;
            }
        }
        // Now process
        $this->user->setLimits($this->domain, $limits);
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
        // $renderer = new HTML_QuickForm_Renderer_Tableless();
        // $form->accept($renderer);
        // $this->setData('limitsForm', $renderer->toHtml());
        $this->setData('LANG_Main_Menu', _('Main Menu'));
        $this->setData('limitsForm', $form->toHtml());
        // Display find form
        $this->tplFile = 'limits.tpl';
    }

}
?>
