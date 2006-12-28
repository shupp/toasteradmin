<?php
/**
 * Framework_User_Lang 
 * 
 * @uses I18Nv2_Negotiator
 * @package Framework
 * @copyright 2005-2006 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @license GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */
/**
 * Framework_User_Lang 
 * 
 * @uses I18Nv2_Negotiator
 * @package Framework
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
        $lang = $this->getLanguageMatch();
        $baseLocaleDir = FRAMEWORK_BASE_PATH . '/' . (string)Framework::$site->config->user->userLocaleDir;
        $messagesFile = $baseLocaleDir . '/' . $lang . '/' . 'LC_MESSAGES' . '/' . "messages.mo";
        if(!file_exists($messagesFile)) $lang = (string)Framework::$site->config->user->userDefaultLocale;
        $lang='es';
        putenv("LANGUAGE=$lang");
        setlocale(LC_ALL, $lang);
        bindtextdomain('messages', $baseLocaleDir);
        // textdomain('messages');
        // echo _('logged in as');exit;
        return;
    }

}
