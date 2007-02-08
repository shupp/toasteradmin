<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * HTML_QuickForm_Renderer_AssocArray 
 * 
 * @package ToasterAdmin
 * @copyright 2005-2006 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @license GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */
/**
 * HTML_QuickForm_Renderer_AssocArray 
 * 
 * @package ToasterAdmin
 * @copyright 2005-2006 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @license GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */
class HTML_QuickForm_Renderer_AssocArray {

    static public function toAssocArray($array) {
        $assocElements = array();
        foreach ($array['elements'] as $key => $ar) {
            $assocElements[$ar['name']] = $ar;
        }
        $array['assocElements'] = $assocElements;
        return $array;
    }

}
