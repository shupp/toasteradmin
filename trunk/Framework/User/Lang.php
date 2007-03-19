<?php
/**
 * Framework_User_Lang 
 * 
 * @uses I18Nv2_Negotiator
 * @package ToasterAdmin
 * @copyright 2005-2006 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @license GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */
/**
 * Framework_User_Lang 
 * 
 * @uses I18Nv2_Negotiator
 * @package ToasterAdmin
 * @copyright 2005-2006 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @license GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */
class Framework_User_Lang extends I18Nv2_Negotiator
{

    /**
     * setLanguage 
     * 
     * @static
     * @access public
     * @return void
     */
    public function setLanguage()
    {
        $baseLocaleDir = FRAMEWORK_BASE_PATH . '/' . (string)Framework::$site->config->user->userLocaleDir;
        I18Nv2::setLocale($this->getLocaleMatch());
        bindtextdomain("messages", $baseLocaleDir);
        textdomain("messages");
        return;
    }

}
