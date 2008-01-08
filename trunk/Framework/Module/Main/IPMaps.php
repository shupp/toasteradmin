<?php

/**
 * Framework_Module_Main_IPMaps 
 * 
 * @uses      ToasterAdmin_Auth_System
 * @package   ToasterAdmin
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2007 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://trac.merchbox.com/trac/toasteradmin
 */

/**
 * Framework_Module_Main_IPMaps 
 * 
 * Modify IP Maps
 * 
 * @uses      ToasterAdmin_Auth_System
 * @package   ToasterAdmin
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2007 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://trac.merchbox.com/trac/toasteradmin
 */
class Framework_Module_Main_IPMaps extends ToasterAdmin_Auth_System
{
    /**
     * __default 
     * 
     *  run listMaps()
     * 
     * @access public
     * @return void
     */
    public function __default()
    {
        return $this->listMaps();
    }

    /**
     * listMaps 
     * 
     * List existing IP Maps
     * 
     * @access public
     * @return void
     */
    public function listMaps()
    {
        $rawList = $this->user->showIPMap();
        $count = 0;
        foreach ($rawList as $ip => $domains) {
            $maps[$count]['ip'] = $ip;
            $maps[$count]['domains'] = explode(", ", $domains);
            $count++;
        }
        $this->setData('mapList', $maps);
        $this->tplFile = 'listMaps.tpl';
    }

    /**
     * delete 
     * 
     * Confirm IP Map Delete
     * 
     * @access public
     * @return void
     */
    public function delete()
    {
        if (empty($_REQUEST['domain']) || empty($_REQUEST['ip'])) {
            throw new Framework_Exception(_("Error: missing domain or ip"));
        }

        $this->setData('domain', $_REQUEST['domain']);
        $this->setData('ip', $_REQUEST['ip']);
        $this->tplFile = 'deleteIPMap.tpl';
    }

    /**
     * deleteNow 
     * 
     * Really delete an IP Map
     * 
     * @access public
     * @return void
     */
    public function deleteNow() {
        if (empty($_REQUEST['domain']) || empty($_REQUEST['ip'])) {
            throw new Framework_Exception(_("Error: missing domain or ip"));
        }
        try {
            $result = $this->user->delIPMap($_REQUEST['ip'], $_REQUEST['domain']);
        } catch (Net_Vpopmaild_Exception $e) {
            $this->setData('message', $e->getMessage());
            return $this->delete();
        }
        $this->tplFile = 'deleteIPMapSuccess.tpl';
    }

    /**
     * add 
     * 
     * Display add IP Map form
     * 
     * @access public
     * @return void
     */
    public function add()
    {
        $form = $this->_addForm();
        $this->_renderForm($form);
    }

    /**
     * addNow 
     * 
     * Try an add an IP Map
     * 
     * @access public
     * @return void
     */
    public function addNow()
    {
        $form = $this->_addForm();
        if (!$form->validate()) {
            $this->_renderForm($form);
            return;
        }
        try {
            $this->user->addIPMap($form->getElementValue('ip'), $form->getElementValue('domain'));
        } catch (Net_Vpopmaild_Exception $e) {
            $this->setData('message', _('Error adding IP Map'));
            $this->_renderForm($form);
            return;
        }
        $this->tplFile = 'addIPMapSuccess.tpl';
    }

    /**
     * _addForm 
     * 
     * Create Add IP Map form
     * 
     * @access private
     * @return void
     */
    private function _addForm()
    {
        $form = new HTML_QuickForm('addIPMapForm',
            'post', './?module=Main&class=IPMaps&event=addNow');

        $form->addElement('text', 'ip', _('IP Address'));
        $form->addElement('text', 'domain', _('Domain'));
        $form->addElement('submit', 'submit', _('Add'));

        $form->addRule('ip',
            _('Please specify an IP'), 'required', null, 'client');
        $form->addRule('domain',
            _('Please specify a Domain'), 'required', null, 'client');
        $form->applyFilter('__ALL__', 'trim');

        return $form;
    }

    private function _renderForm($form)
    {
        $this->setData('addForm', $form->toHtml());
        $this->tplFile = 'addIPMap.tpl';
    }
}
?>
