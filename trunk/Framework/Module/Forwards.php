<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Framework_Module_Forwards 
 * 
 * This module is for viewing and editing vpopmail forwards
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
 * Framework_Module_Forwards 
 * 
 * This module is for viewing and editing vpopmail forwards
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
class Framework_Module_Forwards extends ToasterAdmin_Auth_Domain
{

    /**
     * __construct 
     * 
     * Check that a domain was supplied
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
     * Run listForwards() by default
     * 
     * @access public
     * @return void
     */
    public function __default()
    {
        return $this->listForwards();
    }

    /**
     * listForwards 
     * 
     * List Forwards
     * 
     * @access public
     * @return void
     */
    public function listForwards()
    {
        // Pagintation setup
        $full_alias_array = $this->user->listAlias($this->domain);
        // Format the valias outpt from vpopmaild
        $parsed = $this->user->parseAliases($full_alias_array, 'forwards');
        $total  = count($parsed);
        $this->paginate($total);

        // List Accounts
        $alias_array = $this->user->paginateArray($parsed,
            $this->data['currentPage'], $this->data['limit']);
        if (count($alias_array) == 0) {
            $this->setData('message', _("No Forwards.  Care to add one?"));
            return $this->addForward();
        }
    
        $a = array();
        $c = 0;
        foreach ($alias_array as $key => $val) {
            $fn    = ereg_replace('@.*$', '', $key);
            $eurl  = "./?module=Forwards&domain={$this->domain}";
            $eurl .= "&forward=$fn&event=modifyForward";
            $durl  = "./?module=Forwards&domain={$this->domain}";
            $durl .= "&forward=$fn&event=deleteForward";

            $a[$c]['name']       = $fn;
            $a[$c]['contents']   = $this->user->getAliasContents($val);
            $a[$c]['edit_url']   = htmlspecialchars($eurl);
            $a[$c]['delete_url'] = htmlspecialchars($durl);
            $c++;
        }
        $this->setData('forwards', $a);

        $furl = "./?module=Forwards&event=addForward&domain={$this->domain}";
        $this->setData('add_forward_url', htmlspecialchars($furl));
        $this->tplFile = 'listForwards.tpl';
    }

    /**
     * addForward 
     * 
     * Show Add Forward form
     * 
     * @access public
     * @return mixed void on success, PEAR_Error on failure
     */
    public function addForward()
    {
        $form     = $this->addForwardForm();
        $renderer = new HTML_QuickForm_Renderer_AssocArray();
        $form->accept($renderer);
        $this->setData('form', $renderer->toAssocArray());
        $this->tplFile = 'addForward.tpl';
        return;
    }

    /**
     * addForwardForm 
     * 
     * Create Add Forward Form
     * 
     * @access protected
     * @return object $form HTML_QuickForm Object
     */
    protected function addForwardForm()
    {
        $form = ToasterAdmin_Form::factory('addForwrdForm',
            './?module=Forwards&event=addForwardNow');

        $form->registerRule('validForwardName', 'regex',
            "/^[a-z0-9]+([_\\.-][a-z0-9]+)*$/i");

        $form->addElement('hidden', 'domain', $this->domain);
        $form->addElement('text', 'forward', _("Forward Name"));
        $form->addElement('text', 'destination', _("Destination Address"));
        $form->addElement('submit', 'submit', _("Add"));

        $form->addRule('forward',
            _("Forward is required"), 'required', null, 'client');
        $form->addRule('forward',
            _("Forward name is invalid (should be forward name only, not full email address"), 'validForwardName');
        $form->addRule('destination',
            _("Destination is required"), 'required', null, 'client');
        $form->addRule('destination',
            _("Destination must be a full email address"), 'email', null, 'client');

        return $form;
    }

    /**
     * addForwardNow 
     * 
     * Try and actually add a forward
     * 
     * @access public
     * @return mixed PEAR_Error on failure, listForwards() on success
     */
    public function addForwardNow()
    {
        $form = $this->addForwardForm();
        if (!$form->validate()) {
            $renderer =& new HTML_QuickForm_Renderer_AssocArray();
            $form->accept($renderer);
            $this->setData('form', $renderer->toAssocArray());
            $this->tplFile = 'addForward.tpl';
            return;
        }

        $this->setData('forward', $_REQUEST['forward']);
        $this->setData('destination', $_REQUEST['destination']);

        try {
            $this->addNewForward();
        } catch (Exception $e) {
            $this->setData('message', $e->getMessage());
            return $this->addForward();
        }
        $this->setData('message', _("Forward Added Successfully"));
        return $this->listForwards();
    }

    /**
     * addNewForward 
     * 
     * Add new forward file
     * 
     * @access protected
     * @return void
     */
    protected function addNewForward()
    {
        $file = '.qmail-' . $this->data['forward'];
        // Verify that it doesn't exist
        try {
            $contents = $this->user->readFile($this->domain, '', $file);
        } catch (Net_Vpopmaild_Exception $e) {
            if ($e->getCode() != 0.2102) {
                throw new Framework_Exception($e->getMessage(), $e->getCode());
            }
        }

        $contents = array('&' . $this->data['destination']);
        $result   = $this->user->writeFile($contents, $this->domain, '', $file);
        return true;
    }

    /**
     * addForwardLine 
     * 
     * Add forward line to existing forward
     * 
     * @access protected
     * @return bool true on success, false if forward exists
     */
    protected function addForwardLine()
    {
        $file = '.qmail-' . $this->data['forward'];
        // Let exception bubble up
        $contents    = $this->user->readFile($this->domain, '', $file);
        $destination = '&' . $this->data['destination'];
        // Now build a new array without that forward
        if (in_array($destination, $contents)) {
            return false;
        }
        array_push($contents, $destination);
        $result = $this->user->writeFile($contents, $this->domain, '', $file);
        return true;
    }

    /**
     * deleteForwardLine 
     * 
     * @access protected
     * @return mixed true on success, PEAR_Error on failure
     */
    protected function deleteForwardLine()
    {
        $contents = $this->user->readFile($this->domain, '',
            ".qmail-" . $this->data['forward']);
    
        // Now build a new array without that forward
        if (!in_array($this->data['line'], $contents)) {
            throw new Framewor_Exception(_('Error: forward line does not exist'), 2);
        }

        if (count($contents) == 1) {
            // tell caller to delete instead
            throw new Framework_Exception(_("Only one line, use delete instead"), 3);
        }
        $newContents = array();
        $count       = 1;
        while (list($key, $val) = each($contents)) {
            if ($val == $this->data['line']) continue;
            $newContents[$count] = $val;
            $count++;
        }
        $result = $this->user->writeFile($newContents,
            $this->domain, '', ".qmail-" . $this->data['forward']);
        return true;
    }
    
    
    /**
     * modifyForward 
     * 
     * @access public
     * @return mixed void on success, PEAR_Error on failure
     */
    function modifyForward()
    {
        // Make sure forward was supplied
        if (!isset($_REQUEST['forward'])) {
            $this->setData('message', _("Error: no forward provided"));
            return $this->listForwards();
        }

        $forward = preg_replace('/^.qmail-/', '', $_REQUEST['forward']);
    
        // Get forward info if it exists
        $contents = $this->user->ReadFile($this->domain, '', ".qmail-$forward");
    
        // Set template data
        $this->setData('forward', $forward);
        $this->setData('forward_contents', $this->returnForwardArray($contents));

        $form     = $this->modifyForwardForm();
        $renderer = new HTML_QuickForm_Renderer_AssocArray();
        $form->accept($renderer);
        $this->setData('form', $renderer->toAssocArray());
        $this->tplFile = 'modifyForward.tpl';
    }
    
    /**
     * returnForwardArray 
     * 
     * @param mixed $contents dot-qmail file contents
     * 
     * @access protected
     * @return array $forward_array
     */
    protected function returnForwardArray($contents)
    {
        $c = 0;
        $a = array();
        foreach ($contents as $key => $val) {
            $durl  = "./?module=Forwards&event=deleteForwardLineNow";
            $durl .= "&domain={$this->domain}&forward=" . $_REQUEST['forward'];
            $durl .= "&line=" . urlencode($val);

            $a[$c]['destination'] = $this->user->displayForwardLine($val);
            $a[$c]['delete_url']  = htmlspecialchars($durl);
            $c++;
        }
        return $a;
    }

    /**
     * modifyForwardNow 
     * 
     * @access public
     * @return mixed void on success, PEAR_Error on failure
     */
    function modifyForwardNow()
    {
        $forward = ereg_replace('^.qmail-', '', $_REQUEST['forward']);
        $this->setData('forward', $forward);

        // Get forward info if it exists
        $contents = $this->user->ReadFile($this->domain, '', ".qmail-$forward");

        $form = $this->modifyForwardForm();
        if (!$form->validate()) {

            // Set template data
            $this->setData('forward', $forward);
            $this->setData('forward_contents', $this->returnForwardArray($contents));

            $renderer =& new HTML_QuickForm_Renderer_AssocArray();
            $form->accept($renderer);
            $this->setData('form', $renderer->toAssocArray());
            $this->tplFile = 'modifyForward.tpl';
            return;
        }
        $this->setData('destination', $_REQUEST['destination']);
    
        if (!$this->addForwardLine()) {
            $this->setData('message', _("Error: destination already exists"));
            return $this->modifyForward();
        }
        $this->setData('message', _("Destination Added Successfully"));
        return $this->modifyForward();
    }

    /**
     * modifyForwardForm 
     * 
     * Display modify forward form
     * 
     * @access public
     * @return object $form HTML_QuickForm object
     */
    function modifyForwardForm()
    {
        $this->setData('forwards_url',
            htmlspecialchars("./?module=Forwards&domain={$this->domain}"));

        // Form
        $form = ToasterAdmin_Form::factory('modifyForwardForm',
            './?module=Forwards&event=modifyForwardNow');

        $form->addElement('hidden', 'domain', $this->domain);
        $form->addElement('hidden', 'forward', $this->data['forward']);
        $form->addElement('text', 'destination', _("Destination Address"));
        $form->addElement('submit', 'submit', _("Add"));

        $form->addRule('forward', _("Forward is required"), 'required');
        $form->addRule('destination',
            _("Destination is required"), 'required', null, 'client');
        $form->addRule('destination',
            _("Destination must be a full email address"), 'email', null, 'client');

        return $form;
    }

    /**
     * deleteForwardLineNow 
     * 
     * @access public
     * @return mixed result of modifyForward on success, PEAR_Error on failure
     */
    function deleteForwardLineNow()
    {
        // Make sure forward was supplied
        if (!isset($_REQUEST['forward'])) {
            $this->setData('message', _("Error: no forward provided"));
            return $this->listForwards();
        }
        // Make sure line was supplied
        if (!isset($_REQUEST['line'])) {
            $this->setData('message', _("Error: no forward line provided"));
            return $this->modifyForward();
        }

        $forward = ereg_replace('^.qmail-', '', $_REQUEST['forward']);
        $this->setData('forward', $forward);
        $this->setData('line', $_REQUEST['line']);

        try {
            $result = $this->deleteForwardLine();
        } catch (Framework_Exception $e) {
            if ($e->getCode() == 1) {
                $this->setData('message', $e->getMessage());
                return $this->listAliases();
            } else if ($e->getCode() == 2) {
                $this->setData('message', $e->getMessage());
                return $this->modifyForward();
            } else if ($e->getCode() == 3) {
                return $this->deleteForward();
            } else {
                throw new Framework_Exception($e->getMessage(), $e->getCode());
            }
        }
        $this->setData('message', _("Destination Deleted Successfully"));
        return $this->modifyForward();
    }

    /**
     * deleteForward 
     * 
     * @access protected
     * @return mixed listForwards() on success, PEAR_Error on failure
     */
    protected function deleteForward()
    {
        // Make sure forward was supplied
        if (!isset($_REQUEST['forward'])) {
            $this->setData('message', _("Error: no forward provided"));
            return $this->listForwards();
        }
        $forward  = ereg_replace('^.qmail-', '', $_REQUEST['forward']);
        $contents = $this->user->readFile($this->domain, '', ".qmail-" . $forward);
        $result   = $this->user->rmFile($this->domain, '', '.qmail-' . $forward);
        $this->setData('message', _("Forward Deleted Successfully"));
        return $this->listForwards();
    }

}
?>
