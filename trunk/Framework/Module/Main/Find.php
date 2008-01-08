<?php

/**
 * Framework_Module_Main_Find 
 * 
 * @uses      ToasterAdmin_Auth_System
 * @package   ToasterAdmin
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2007 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://trac.merchbox.com/trac/toasteradmin
 */

/**
 * Framework_Module_Main_Find 
 * 
 * Find domains
 * 
 * @uses      ToasterAdmin_Auth_System
 * @package   ToasterAdmin
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2007 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://trac.merchbox.com/trac/toasteradmin
 */
class Framework_Module_Main_Find extends ToasterAdmin_Auth_System
{
    /**
     * __default 
     * 
     * Run $this->find();
     * 
     * @access public
     * @return void
     */
    public function __default()
    {
        return $this->find();
    }

    /**
     * find 
     * 
     * Diplay find form
     * 
     * @access public
     * @return void
     */
    public function find()
    {
        $this->setData('LANG_Main_Menu', _('Main Menu'));
        $form = $this->_findForm();
        $this->_renderForm($form);
        return;
    }
    /**
     * findNow 
     * 
     * Actually try to find a domain
     * 
     * @access public
     * @return void
     */
    public function findNow()
    {
        $this->setData('LANG_Main_Menu', _('Main Menu'));
        $form = $this->_findForm();
        if (!$form->validate()) {
            $this->_renderForm($form);
            return;
        }
        $this->_renderForm($form);
        $domain = $form->getElementValue('domain');
        $this->setData('domain', $domain);

        $result = $this->user->findDomain($domain);
        if ($result == null) {
            $this->setData('message', _('Error: does not exist'));
        } else {
            $this->setData('found', 1);
            $this->setData('delete_url', "./?module=Domains&amp;event=delDomain&amp;domain=$domain");
            $this->setData('edit_url', "./?module=Domains&amp;class=Menu&amp;domain=$domain");
        }
    }
    /**
     * _findForm 
     * 
     * Generate find form
     * 
     * @access private
     * @return void
     */
    private function _findForm()
    {
        $form = new HTML_QuickForm('formFind', 'post', './?module=Main&class=Find&event=findNow');

        $form->addElement('text', 'domain', _('Domain'));
        $form->addElement('submit', 'submit', _('Find Domain'));

        $form->addRule('domain', _('Please a domain name'), 'required', null, 'client');
        $form->applyFilter('__ALL__', 'trim');

        return $form;
    }

    /**
     * _renderForm 
     * 
     * @param mixed $form 
     * @access private
     * @return void
     */
    private function _renderForm($form)
    {
        $this->setData('findForm', $form->toHtml());
        // Display find form
        $this->tplFile = 'find.tpl';
    }
}
?>
