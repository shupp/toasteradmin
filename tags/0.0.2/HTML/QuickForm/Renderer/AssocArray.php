<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * HTML_QuickForm_Renderer_AssocArray 
 * 
 * @uses HTML_QuickForm
 * @package ToasterAdmin
 * @copyright 2007 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @license GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */
/**
 * HTML_QuickForm_Renderer_AssocArray 
 * 
 * @uses HTML_QuickForm_Renderer_Array
 * @package ToasterAdmin
 * @copyright 2007 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @license GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */
class HTML_QuickForm_Renderer_AssocArray extends HTML_QuickForm_Renderer_Array {

    public function toAssocArray() {
        $array = $this->toArray();
        $assocElements = array();
        foreach ($array['elements'] as $key => $ar) {
            $assocElements[$ar['name']] = $ar;
        }
        $array['assocElements'] = $assocElements;
        return $array;
    }
}
?>
