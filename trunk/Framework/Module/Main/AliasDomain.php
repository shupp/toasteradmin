<?php

/**
 * Framework_Module_Main_AliasDomain 
 * 
 * PHP Version 5
 * 
 * @uses      ToasterAdmin_Auth_System
 * @package   ToasterAdmin
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2007 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://trac.merchbox.com/trac/toasteradmin
 */


/**
 * Framework_Module_Main_AliasDomain 
 * 
 * Add Alias Domains
 * 
 * @uses      ToasterAdmin_Auth_System
 * @package   ToasterAdmin
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2007 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://trac.merchbox.com/trac/toasteradmin
 */
class Framework_Module_Main_AliasDomain extends ToasterAdmin_Auth_System
{
    /**
     * __default 
     * 
     * Run add()
     * 
     * @access public
     * @return void
     */
    public function __default()
    {
        return $this->add();
    }

    /**
     * add 
     * 
     * Display add form
     * 
     * @param object $form HTML_QuickForm object, default null
     * 
     * @access public
     * @return void
     */
    public function add($form = null)
    {
        if (is_null($form)) {
            $form = $this->_addForm();
        }
        $this->_renderForm($form);
        $this->tplFile = 'addAliasDomain.tpl';
        return;
    }
    /**
     * addNow 
     * 
     * Try and really add the alias domain
     * 
     * @access public
     * @return void
     */
    public function addNow()
    {
        $form = $this->_addForm();
        if (!$form->validate()) {
            return $this->add($form);
        }
        $domain = $form->getElementValue('domain');
        $alias  = $form->getElementValue('alias');
        $this->setData('addForm', $form->toHtml());    
        $this->setData('alias', $domain);

        try {
            $result = $this->user->addAliasDomain($domain, $alias);
        } catch (Net_Vpopmaild_Exception $e) {
            $this->setData('message', _('Error:') . $e->getMessage());
            return $this->add($form);
        }

        // Display find form
        $this->tplFile = 'addAliasDomainSuccess.tpl';
        return;
    }
    /**
     * _addForm 
     * 
     * Create add form
     * 
     * @access private
     * @return void
     */
    private function _addForm()
    {
        $form = new HTML_QuickForm('addAliasDomainForm', 
            'post', './?module=Main&class=AliasDomain&event=addNow');

        $form->addElement('text', 'domain', _('Real Domain'));
        $form->addElement('text', 'alias', _('Alias Domain'));
        $form->addElement('submit', 'submit', _('Add'));

        $form->addRule('domain', 
            _('Please specify a Real Domain'), 'required', null, 'client');
        $form->addRule('alias', 
            _('Please specify an Alias Domain'), 'required', null, 'client');
        $form->applyFilter('__ALL__', 'trim');

        return $form;
    }

    private function _renderForm($form)
    {
        $this->setData('addForm', $form->toHtml());    
    }
}
?>
