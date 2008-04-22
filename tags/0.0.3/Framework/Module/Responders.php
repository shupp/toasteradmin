<?php

/**
 * Responders Module
 * 
 * PHP Version 5.1.0+
 * 
 * @uses       ToasterAdmin_Auth_Domain
 * @category   Mail
 * @package    ToasterAdmin
 * @subpackage Module
 * @author     Bill Shupp <hostmaster@shupp.org> 
 * @copyright  2007-2008 Bill Shupp
 * @license    GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link       http://trac.merchbox.com/trac/toasteradmin
 */

/**
 * Framework_Module_Responders 
 * 
 * Manage vpopmail auto responders
 * 
 * @uses       ToasterAdmin_Auth_Domain
 * @category   Mail
 * @package    ToasterAdmin
 * @subpackage Module
 * @author     Bill Shupp <hostmaster@shupp.org> 
 * @copyright  2007-2008 Bill Shupp
 * @license    GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link       http://trac.merchbox.com/trac/toasteradmin
 */
class Framework_Module_Responders extends ToasterAdmin_Auth_Domain
{

    /**
     * __construct 
     * 
     * Check that a domain as supplied
     * 
     * @access protected
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
     * Run $this->listResponders()
     * 
     * @access public
     * @return void
     */
    public function __default()
    {
        $this->listResponders();
    }

    /**
     * listResponders 
     * 
     * List all auto responders for a domain
     * 
     * @access public
     * @return void
     */
    public function listResponders()
    {
        $f   = $this->user->listAlias($this->domain);
        $raw = $this->user->parseAliases($f, 'responders');

        // Pagintation setup
        $total = count($raw);
        $this->paginate($total);
        
        // List Responders
        $p = $this->user->paginateArray($raw,
            $this->data['currentPage'], $this->data['limit']);
        $a = array();
        $c = 0;
        foreach ($p as $key => $val) {
            $eurl  = "./?module=Responders&domain={$this->domain}";
            $eurl .= "&autoresponder=$key&event=modifyResponder";
            $durl  = "./?module=Responders&domain={$this->domain}";
            $durl .= "&autoresponder=$key&event=delete";

            $a[$c]['autoresponder'] = $key;
            $a[$c]['edit_url']      = htmlspecialchars($eurl);
            $a[$c]['delete_url']    = htmlspecialchars($durl);
            $c++;
        }
        $aurl = "./?module=Responders&event=addResponder&domain={$this->domain}";
        $this->setData('add_url', htmlspecialchars($aurl));
        $this->setData('autoresponders', $a);

        $this->tplFile = 'listResponders.tpl';
        return;
    }

    /**
     * addResponder
     * 
     * Show add responder form
     * 
     * @access public
     * @return void
     */
    public function addResponder()
    {
        $form     = $this->responderForm();
        $renderer = new HTML_QuickForm_Renderer_AssocArray();
        $form->accept($renderer);
        $this->setData('form', $renderer->toAssocArray());
        $this->tplFile = 'responderForm.tpl';
        return;
    }

    /**
     * addResponderNow 
     * 
     * Add autoresponder
     * 
     * @access public
     * @return mixed addResponder() on failure, listResponders() on success
     */
    public function addResponderNow()
    {
        $this->setData('responder_submit', _("Add Auto-Responder"));
        $this->setData('responder_header', _("Add Auto-Responder to domain "));
        $form = $this->responderForm();
        if (!$form->validate()) {
            $renderer = new HTML_QuickForm_Renderer_AssocArray();
            $form->accept($renderer);
            $this->setData('form', $renderer->toAssocArray());
            $this->tplFile = 'responderForm.tpl';
            return;
        }

        // Split autoresponder to get user
        $email_array = explode('@', $_REQUEST['autoresponder']);
        try {
            $responder = $this->user->robotGet($this->domain, $email_array[0]);
            $this->setData('message',
                _('Error: autoresponder already exists: ' . $email_array[0]));
            return $this->addResponder();
        } catch (Net_Vpopmaild_Exception $e) {
        }

        $result = $this->user->robotSet($this->domain,
            $email_array[0], $_POST['subject'], $_POST['body'], $_POST['copy']);
        $this->setData('message', _('Auto-Responder Added Successfully'));
        return $this->listResponders();
    }

    /**
     * responderForm 
     * 
     * @param string $type     add or modify
     * @param string $defaults default responder data
     * 
     * @access protected
     * @return object    HTML_Quickform object
     */
    protected function responderForm($type = 'add', $defaults = '')
    {
        $this->setData('type', $type);
        if ($type == 'add') {
            $this->setData('responder_submit', _("Add"));
            $this->setData('responder_header',
                _("Add Auto-Responder to domain ") . $this->domain);
        } else {
            $this->setData('responder_name', $defaults['autoresponder']);
            $this->setData('responder_submit', _("Modify"));
            $this->setData('responder_header',
                _("Modify Auto-Responder ") . $_REQUEST['autoresponder']);
        }

        $form = ToasterAdmin_Form::factory('formAddAccount',
            "./?module=Responders&event=${type}ResponderNow&domain={$this->domain}");

        if ($defaults == '') {
            $form->setDefaults(array('autoresponder' => '@' . $this->domain));
        } else {
            $form->setDefaults($defaults);
        }

        if ($type == 'modify') {
            $form->addElement('hidden', 'autoresponder');
            $form->addRule('autoresponder',
                _("Auto-Responder is required"), 'required', null);
            $form->addRule('autoresponder',
                _("Auto-Responder must be a full address"), 'email', null);
        } else {
            $form->addElement('text', 'autoresponder', _('Auto-Responder'));
            $form->addRule('autoresponder',
                _("Auto-Responder is required"), 'required', null, 'client');
            $form->addRule('autoresponder',
                _("Auto-Responder must be a full address"), 'email', null, 'client');
        }
        $form->addElement('text', 'copy', _("Send Copy To"));
        $form->addElement('text', 'subject', _("Subject"));
        $form->addElement('textarea', 'body', _("Body"), 'cols="40" rows="10"');
        $form->addElement('submit', 'submit', $this->responder_submit);

        $form->registerRule('sameDomain', 'regex', "/@$this->domain$/i");

        $form->addRule('autoresponder',
            _('Error: wrong domain in Auto-Responder'), 'sameDomain');
        $form->addRule('copy',
            _("'Save a copy' must be an email address"), 'email', null, 'client');
        $form->addRule('subject',
            _("Subject is required"), 'required', null, 'client');
        $form->addRule('body', _("Body is required"), 'required', null, 'client');
        return $form;
    }

    /**
     * delete 
     * 
     * Show delete responder page
     * 
     * @throws Framework_Exception if autorsponder is not supplied
     * @access public
     * @return void
     */
    public function delete()
    {
        if (!isset($_REQUEST['autoresponder'])) {
            throw new Framework_Exception(_('Error: no responder supplied'));
        }
        $this->setData('autoresponder', $_REQUEST['autoresponder']);
        $this->setData('cancel_url',
            "./?module=Responders&event=cancelDelete&domain=" . $this->domain);
        $durl  = "./?module=Responders&event=deleteNow&domain={$this->domain}";
        $durl .= "&autoresponder=" . $_REQUEST['autoresponder'];
        $this->setData('delete_now_url', $durl);
        $this->tplFile = 'responderConfirmDelete.tpl';
    }

    /**
     * deleteNow 
     * 
     * Try and delete a responder
     * 
     * @throws Framework_Exception if autoresponder is not supplied,
     * or on failure
     * @access public
     * @return void
     */
    public function deleteNow()
    {
        if (!isset($_REQUEST['autoresponder'])) {
            throw new Framework_Exception(_('Error: no responder supplied'));
        }
        $array  = explode('@', $_REQUEST['autoresponder']);
        $result = $this->user->robotDel($this->domain, $array[0]);
        $this->setData('message', _("Responder Deleted Successfully"));
        return $this->listResponders();
    }

    /**
     * cancelDelete 
     * 
     * Cancel delete responder, list responders
     * 
     * @access public
     * @return void
     */
    public function cancelDelete()
    {
        $this->setData('message', _("Delete Canceled"));
        return $this->listResponders();
    }

    /**
     * modifyResponder 
     * 
     * Show modify responder page
     * 
     * @access public
     * @return void
     */
    public function modifyResponder()
    {
        // Make sure account was supplied
        if (!isset($_REQUEST['autoresponder'])) {
            throw new Framework_Exception(_("Error: no Auto-Responder supplied"));
        }
        $array     = explode('@', $_REQUEST['autoresponder']);
        $responder = $this->user->robotGet($this->domain, $array[0]);

        // Setup defaults
        $defaults                  = array();
        $defaults['subject']       = $responder['Subject'];
        $defaults['body']          = implode("\n", $responder['Message']);
        $defaults['autoresponder'] = $_REQUEST['autoresponder'];
        if (isset($responder['Forward'])) {
            $defaults['copy'] = $responder['Forward'];
        }

        $form     = $this->responderForm('modify', $defaults);
        $renderer = new HTML_QuickForm_Renderer_AssocArray();
        $form->accept($renderer);
        $this->setData('form', $renderer->toAssocArray());
        $this->tplFile = 'responderForm.tpl';
        return;
    }

    /**
     * modifyResponderNow 
     * 
     * Actually modify the responder
     * 
     * @access public
     * @return void
     */
    public function modifyResponderNow()
    {
        // Make sure account was supplied
        if (!isset($_REQUEST['autoresponder'])) {
            throw new Framework_Exception(_("Error: no Auto-Responder supplied"));
        }
        $array     = explode('@', $_REQUEST['autoresponder']);
        $responder = $this->user->robotGet($this->domain, $array[0]);
        // Setup defaults
        $defaults                  = array();
        $defaults['subject']       = $responder['Subject'];
        $defaults['body']          = implode("\n", $responder['Message']);
        $defaults['autoresponder'] = $_REQUEST['autoresponder'];

        $form = $this->responderForm('modify', $defaults);
        if (!$form->validate()) {
            $renderer = new HTML_QuickForm_Renderer_AssocArray();
            $form->accept($renderer);
            $this->setData('form', $renderer->toAssocArray());
            $this->tplFile = 'responderForm.tpl';
            return;
        }
        $result = $this->user->robotDel($this->domain, $array[0]);
        $result = $this->user->robotSet($this->domain,
            $array[0], $_POST['subject'], $_POST['body'], $_POST['copy']);
        $this->setData('message', _("Auto-Responder modified successfully"));
        return $this->listResponders();
    }
}
?>
