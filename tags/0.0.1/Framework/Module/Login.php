<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Framework_Module_Login
 *
 * PHP Version 5.1.0+
 *
 * @category   Mail
 * @package    ToasterAdmin
 * @subpackage Module
 * @author     Bill Shupp <hostmaster@shupp.org>
 * @copyright  2007-2008 Bill Shupp <hostmaster@shupp.org>
 * @license    GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link       http://trac.merchbox.com/trac/toasteradmin
 */

/**
 * Framework_Module_Login
 *
 * @category   Mail
 * @package    ToasterAdmin
 * @subpackage Module
 * @author     Bill Shupp <hostmaster@shupp.org>
 * @copyright  2007-2008 Bill Shupp <hostmaster@shupp.org>
 * @license    GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link       http://trac.merchbox.com/trac/toasteradmin
 */
class Framework_Module_Login extends Framework_Auth_No
{

    /**
     * controllers 
     * 
     * We're using a custom controller here to add gettext support
     * 
     * @var string
     * @access public
     */
    public $controllers = array('ToasterAdmin');

    /**
     * __default
     *
     * Just display the login form.
     *
     * @access public
     * @return void
     */
    public function __default()
    {
        $form = $this->createLoginForm();
        $this->setData('QF_Form', $form->toHtml());
        $this->tplFile = 'Login.tpl';
    }

    /**
     * loginNow 
     * 
     * Try and log the user in.
     * 
     * @access public
     * @return void
     */
    public function loginNow()
    {
        $this->tplFile = 'Login.tpl';

        $form = $this->createLoginForm();
        if ($form->validate()) {
            $result = $this->user->authenticate($_POST['email'], $_POST['password']);
            if (!$result) {
                $this->setData('loginError', _('Login failed'));
                $this->setData('QF_Form', $form->toHtml());
                $this->session->email    = null;
                $this->session->password =  null;
                return;
            }

            $crypt = new Crypt_Blowfish((string)Framework::$site->config->mcryptKey);

            $emailArray                    = explode('@', $_POST['email']);
            $this->session->user           = $emailArray[0];
            $this->session->domain         = $emailArray[1];
            $this->session->email          = $_POST['email'];
            $this->session->password       = $crypt->encrypt($_POST['password']);
            $this->session->lastActionTime = time();
            header('Location: ./index.php?module=Home');
            return;
        } else {
            $this->setData('QF_Form', $form->toHtml());
        }
    }

    /**
     * createLoginForm 
     * 
     * Create HTML_QuickForm object for the login form
     * 
     * @access protected
     * @return object  HTML_QuickForm object
     */
    protected function createLoginForm()
    {
        $form = new HTML_QuickForm('formLogin', 'post',
            $_SERVER['REQUEST_URI'] . '&event=loginNow');

        $form->addElement('header', 'MyHeader', _('Login'));
        $form->addElement('text', 'email', _('Email'));
        $form->addElement('password', 'password', _('Password'));
        $form->addElement('submit', 'submit', _('Login'));

        $form->addRule('email', _('Please enter your email address'),
            'required', null, 'client');
        $form->addRule('email', _('Please enter a valid email address'),
            'email', null, 'client');
        $form->addRule('password', _('Please enter your password'),
            'required', null, 'client');
        $form->applyFilter('__ALL__', 'trim');

        return $form;
    }

    /**
     * logoutNow 
     * 
     * Log the user out.
     * 
     * @access public
     * @return void
     */
    public function logoutNow()
    {
        $this->session->destroy();
        $this->setData('message', _('Logged out successfully'));
        return $this->__default();
    }

    /**
     * logoutInactive 
     * 
     * Log the user out for inactivity
     * 
     * @access public
     * @return void
     */
    public function logoutInactive()
    {
        $this->session->destroy();
        $this->setData('message',
            _('You have been logged out automatically for inactivity'));
        return $this->__default();
    }
}

?>
