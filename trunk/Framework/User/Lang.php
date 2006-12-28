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
        $baseLocaleDir = (string)Framework::$site->config->localeBaseDir;
        if(!file_exists($baseLocaleDir . "/$lang/LC_MESSAGES/$lang.mo"))
            $lang = (string)Framework::$site->config->userDefaultLocale;

        putenv("LANGUAGE=$lang");
        bindtextdomain('messages', $baseLocaleDir);
        textdomain('messages');
        return;
    }

}
