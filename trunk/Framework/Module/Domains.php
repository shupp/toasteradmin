<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Framework_Module_Domains 
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
 * Framework_Module_Domains 
 * 
 * This module is for viewing and editing vpopmail domains
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
class Framework_Module_Domains extends ToasterAdmin_Auth_System
{

    /**
     * __default
     *
     * Run listDomains()
     *
     * @access public
     * @return mixed
     */
    public function __default()
    {
        $this->listDomains();
    }

    /**
     * listDomains 
     * 
     * List Domains
     * 
     * @access public
     * @return void
     */
    public function listDomains()
    {
        // Pagination setup
        $total = $this->user->domainCount();
        $this->paginate($total);

        // Build domain list
        $list = $this->user->listDomains($this->data['currentPage'],
            $this->data['limit']);
        $a    = array();
        $c    = 0;
        foreach ($list as $key => $val) {
            $lus = './?module=Main&class=Limits&domain=' . $key;
            $mus = './?module=Domains&class=Menu&domain=' . $key;
            $dus = './?module=Domains&event=delDomain&domain=' . $key;

            $a[$c]['name']       = $key;
            $a[$c]['limits_url'] = htmlspecialchars($lus);
            $a[$c]['menu_url']   = htmlspecialchars($mus);
            $a[$c]['delete_url'] = htmlspecialchars($dus);
            $c++;
        }
        $this->setData('domains', $a);
        $this->setData('add_domain_url',
            htmlspecialchars("./?module=Domains&event=addDomain"));
        $this->tplFile = 'listDomains.tpl';
        return;
    }

    /**
     * addDomain 
     * 
     * Display add domain form
     * 
     * @access public
     * @return void
     */
    public function addDomain()
    {
        $form = $this->addDomainForm();
        $this->_renderForm($form);
        return;
    }

    /**
     * addDomainNow 
     * 
     * Add Domain
     * 
     * @access public
     * @return void
     */
    public function addDomainNow()
    {
        $form = $this->addDomainForm();
        if (!$form->validate()) {
            $this->_renderForm($form);
            return;
        }

        // Add domain
        try {
            $result = $this->user->addDomain($form->getElementValue('domain'),
                $form->getElementValue('password'));
        } catch (Net_Vpopmaild_Exception $e) {
            $this->setData('message', _("Error: ") . $e->getMessage());
            return $this->addDomain();
        }
        header('Location: ./?module=Domains&class=Menu&success&domain='
            . $form->getElementValue('domain'));
        return;
    }

    /**
     * addDomainForm 
     * 
     * Create add domain form.
     * 
     * @access protected
     * @return object HTML_QuickForm object
     */
    protected function addDomainForm()
    {
        $form = new HTML_QuickForm('formLogin', 'post',
            './?module=Domains&event=addDomainNow');

        $form->addElement('text', 'domain', _('Domain'));
        $form->addElement('password', 'password', _('Password'));
        $form->addElement('submit', 'submit', _('Add Domain'));

        $form->addRule('domain', _('Please a domain name'),
            'required', null, 'client');
        $form->addRule('password', _('Please enter a postmaster password'),
            'required', null, 'client');
        $form->applyFilter('__ALL__', 'trim');

        return $form;
    }

    /**
     * delDomain 
     * 
     * Show delete domain link
     * 
     * @access public
     * @return void
     */
    public function delDomain()
    {
        // Make sure the domain was supplied
        if ($this->domain == null) {
            throw new Framework_Exception (_("Error: no domain supplied"));
        }

        $dus = "./?module=Domains&event=delDomainNow&domain=" . $this->domain;
        $cus = "./?module=Domains&event=cancelDelDomain";
        $this->setData('delete_url', htmlspecialchars($dus));
        $this->setData('cancel_url', htmlspecialchars($cus));
        $this->tplFile = 'domainConfirmDelete.tpl';

    }

    /**
     * delDomainNow 
     * 
     * Delete domain
     * 
     * @access public
     * @return void
     */
    public function delDomainNow()
    {
        // Make sure the domain was supplied
        if ($this->domain == null) {
            throw new Framework_Exception (_("Error: no domain supplied"));
        }

        // Delete domain
        try {
            $result = $this->user->delDomain($this->domain);
        } catch (Exception $e) {
            $this->setData('message', _("Error: ") . $e->getMessage());
            return $this->listDomains();
        }
        $this->setData('message', _("Domain deleted successfully"));
        return $this->listDomains();
    }

    /**
     * cancelDelDomain 
     * 
     * Cancel domain deletion
     * 
     * @access public
     * @return void
     */
    public function cancelDelDomain()
    {
        $this->setData('message', _("Domain deletion canceled"));
        return $this->listDomains();
    }

    /**
     * _renderForm 
     * 
     * Render Add Domain form
     * 
     * @param object $form HTML_QuickForm object
     * 
     * @access private
     * @return void
     */
    private function _renderForm($form)
    {
        $this->tplFile = 'addDomain.tpl';
        $this->setData('addDomainForm', $form->toHtml());
    }

}
    
?>
