<?php

/**
 * Bill's Template System
 *
 * A simple smarty-like template system aimed at simple tools that don't need
 * caching or comipiled templates for performance
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 * @version 0.4
 * @package BTS
 *
 */

/**
 * Location of template direcotry
 *
 * defaults to ./templates
 */

if(!defined(BTS_TEMPLATE_DIR))
    define(BTS_TEMPLATE_DIR, './templates/');

$short_test = ini_get('short_open_tag');
if(strlen($short_test == 0) || $short_test == 0) {
    die("BTS_ERROR: short_open_tag is set to Off");
}

/**
 * Bill's Template Class
 * 
 * @package BTS
 * @version $id$
 * @copyright 2005-2006 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @license GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */
class bts {

    var $var_array = array();

    /**
     * Constructor
     * 
     * @access public
     * @return void
     */
    function bts() {
        $this->var_array['php_self'] = $_SERVER['PHP_SELF'];
    }

    /**
     * assign 
     * 
     * @param mixed $name 
     * @param mixed $value 
     * @access public
     * @return void
     */
    function assign($name, $value) {
        $this->var_array[$name] = $value;
    }

    /**
     * get_contents 
     * 
     * @param mixed $file 
     * @access public
     * @return void
     */
    function get_contents($file) {
        $full_path = BTS_TEMPLATE_DIR . '/' . $file;
        if(!is_readable($full_path)) {
            die("BTS Error: Could not retrieve $full_path");
        } else {
            // Get contents and parse PHP
            foreach($this->var_array as $key => $val) {
                $$key = $val;
            }
            ob_start();
            include($full_path);
            $buffer = ob_get_contents();
            ob_end_clean();
            return $buffer;
        }
    }

    /**
     * parse 
     * 
     * @param mixed $data 
     * @access public
     * @return void
     */
    function parse($data) {
        // Replace Tags
        foreach ($this->var_array as $key => $value) {
            if(is_array($value)) {
                $data = eregi_replace("{[$]*" . trim($key) . "}", 'Array', $data);
                foreach ($value as $ar_key => $ar_val) {
                    if(!is_array($ar_val)) {
                        $data = eregi_replace("{[$]*" . trim($key) . "." . trim($ar_key) . "}", 
                            $ar_val, $data);
                    }
                }
            } else {
                $data = eregi_replace("{[$]*" . trim($key) . "}", $value, $data);
            }
        }
        return $data;
    }

    /**
     * display 
     * 
     * @param mixed $file 
     * @param int $return 
     * @access public
     * @return void
     */
    function display($file, $return = 0) {
        $data = $this->get_contents($file);
        $data = $this->parse($data);
        if($return == 1) return $data;
        print($data);
    }

    /**
     * btsSelectOptions 
     * 
     * Cycles through $opt_array, and returns <option>$value
     * If $value = $selected_option, it returns
     * <option selected>$value
     * 
     * @param mixed $opt_array 
     * @param mixed $selected_option 
     * @access public
     * @return string
     */
    function btsSelectOptions($opt_array, $selected_option) {
    
        $out = '';
        foreach($opt_array as $key => $val) {
            $selected = '';
            if($val == $selected_option) {
                $selected = ' selected';
            }
            $out .= "<option$selected>$val\n";
        }
        return $out;
    }
    
    
    /**
     * cycle 
     * 
     * This function is derived from the Smarty Plugin by
     * Monte Ohrt.  Used for cycling elements of a comma delimeted
     * string "values"
     *
     * @param mixed $values 
     * @param string $name 
     * @access public
     * @return void
     */
    function cycle($values, $name = 'default') {
        static $cycle_vars;
    
        if(isset($cycle_vars[$name]['values'])
            && $cycle_vars[$name]['values'] != $values) {
            $cycle_vars[$name]['index'] = 0;
        }
        $cycle_vars[$name]['values'] = $values;
        $cycle_array = explode(',', $cycle_vars[$name]['values']);
       
        if(!isset($cycle_vars[$name]['index'])) {
            $cycle_vars[$name]['index'] = 0;
        }
    
        $retval = $cycle_array[$cycle_vars[$name]['index']];
    
        if ( $cycle_vars[$name]['index'] >= count($cycle_array) -1 ) {
            $cycle_vars[$name]['index'] = 0;
        } else {
            $cycle_vars[$name]['index']++;
        }
    
        return $retval;
    }

}

?>
