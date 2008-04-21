<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * HTML_QuickForm_Renderer_AssocArray 
 * 
 * PHP Version 5.1.0+
 * 
 * @uses      HTML_QuickForm_Renderer_Array
 * @category  HTML
 * @package   ToasterAdmin
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2007-2008 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://trac.merchbox.com/trac/toasteradmin
 */
/**
 * HTML_QuickForm_Renderer_AssocArray 
 * 
 * Simple extension to the Array rendere to add associative keys
 * 
 * @uses      HTML_QuickForm_Renderer_Array
 * @category  HTML
 * @package   ToasterAdmin
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2007-2008 Bill Shupp
 * @license   GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 * @link      http://trac.merchbox.com/trac/toasteradmin
 */
class HTML_QuickForm_Renderer_AssocArray extends HTML_QuickForm_Renderer_Array
{
    /**
     * toAssocArray 
     * 
     * Add return toArray() with associative elements
     * 
     * @access public
     * @return array  output of toArray() + assocElements
     */
    public function toAssocArray()
    {
        $array         = $this->toArray();
        $assocElements = array();
        foreach ($array['elements'] as $key => $ar) {
            $assocElements[$ar['name']] = $ar;
        }
        $array['assocElements'] = $assocElements;
        return $array;
    }
}
?>
