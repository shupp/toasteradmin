<?php

/**
 * ToasterAdmin_Form 
 * 
 * PHP Version 5.1.0+
 * 
 * @abstract
 * @category  Infrastructure
 * @package   ToasterAdmin
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2008 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://trac.merchbox.com/trac/toasteradmin
 */
/**
 * ToasterAdmin_Form 
 * 
 * Simple HTML_QuickForm wrapper to wrap the required note text in gettext()
 * 
 * @abstract
 * @category  Infrastructure
 * @package   ToasterAdmin
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2008 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://trac.merchbox.com/trac/toasteradmin
 */
abstract class ToasterAdmin_Form
{
    /**
     * factory 
     * 
     * Factory method
     * 
     * @param mixed $name name of the form
     * @param mixed $url  action url
     * 
     * @static
     * @access public
     * @return Object HTML_QuickForm Object
     */
    static function factory($name, $url)
    {
            $form = new HTML_QuickForm($name, 'post', $url);
            $star = '<span style="color: #ff0000">*</span> ';
            $form->setRequiredNote($star . _('denotes required field'));
            return $form;
    }
}
?>
