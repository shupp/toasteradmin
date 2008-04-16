<?php
/**
 * Framework_Request_ToasterAdmin 
 * 
 * PHP Version 5.1.0+
 * 
 * @uses       Framework_Request_Web
 * @category   Mail
 * @package    ToasterAdmin
 * @subpackage Request
 * @author     Bill Shupp <hostmaster@shupp.org> 
 * @copyright  2008 Bill Shupp
 * @license    GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link       http://trac.merchbox.com/toasteradmin/trac
 */
/**
 * Framework_Request_ToasterAdmin 
 * 
 * Simple extension to complete internationalization support
 * 
 * @uses       Framework_Request_Web
 * @category   Mail
 * @package    ToasterAdmin
 * @subpackage Request
 * @author     Bill Shupp <hostmaster@shupp.org> 
 * @copyright  2008 Bill Shupp
 * @license    GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link       http://trac.merchbox.com/toasteradmin/trac
 */
class Framework_Request_ToasterAdmin extends Framework_Request_Web
{
    /**
     * __construct 
     * 
     * Set locale for gettext
     * 
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $baseLocaleDir  = FRAMEWORK_BASE_PATH . '/';
        $baseLocaleDir .= (string)Framework::$site->config->user->userLocaleDir;
        // Fix for missing _COUNTRY in negotiated locale string
        if (strlen($this->locale) == 2) {
            $this->locale = $this->locale . '_' . strtoupper($this->locale);
        }
        I18Nv2::setLocale($this->locale);
        bindtextdomain("messages", $baseLocaleDir);
        bind_textdomain_codeset("messages", 'UTF-8');
        textdomain("messages");
    }
}
?>
