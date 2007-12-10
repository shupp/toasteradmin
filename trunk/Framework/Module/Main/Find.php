<?php

class Framework_Module_Main_Find extends ToasterAdmin_Auth_System
{
    public function __default()
    {
        return $this->find();
    }

    public function find()
    {
        $this->setData('LANG_Main_Menu', _('Main Menu'));
        $this->setData('findForm', $this->findForm()->toHtml());    
        $this->tplFile = 'find.tpl';
        return;
    }
    public function findNow()
    {
        $this->setData('LANG_Main_Menu', _('Main Menu'));
        $form = $this->findForm();
        if (!$form->validate()) {
            return $this->find();
        }
        $this->setData('findForm', $this->findForm()->toHtml());    
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
        // Display find form
        $this->tplFile = 'find.tpl';
        return;
    }
    private function findForm()
    {
        $form = new HTML_QuickForm('formFind', 'post', './?module=Main&class=Find&event=findNow');

        $form->addElement('text', 'domain', _('Domain'));
        $form->addElement('submit', 'submit', _('Find Domain'));

        $form->addRule('domain', _('Please a domain name'), 'required', null, 'client');
        $form->applyFilter('__ALL__', 'trim');

        return $form;
    }
}
?>
