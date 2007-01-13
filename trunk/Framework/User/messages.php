<?php
/**
 * Framework_User_messages 
 * 
 * 
 * @package Framework
 * @version $id$
 * @copyright 2005-2007 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @license GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */

/**
 * Framework_User_messages 
 * 
 * Simple message storage and display in _SESSION.  Useful when using Location
 * header redirects
 * 
 * @package Framework
 * @version $id$
 * @copyright 2005-2007 Bill Shupp
 * @author Bill Shupp <hostmaster@shupp.org> 
 * @license GPL 2.0  {@link http://www.gnu.org/licenses/gpl.txt}
 */
class Framework_User_messages
{

    /**
    * Set Message Error
    *
    * Identical to {@link set_msg}, except it wraps the message in span 
    * class=error
    */

    function setMsgErr($error) {
        $_SESSION['message'] = '<span class="error">' . $error . '</span>';
    }

    /**
    * Set Message
    *
    * Places $msg in _SESSION['message'], which is later parsed by 
    * {@link displayMsg()}
    */

    function setMsg($msg) {
        $_SESSION['message'] = $msg;
    }

    /**
    * Display Message
    *
    * Displays _SESSION['message'] and then unsets it.  Works with 
    * {@link setMsg()} and {@link setMsgErr()}
    */
    function displayMsg() {
        if(isset($_SESSION['message'])) {
            echo stripslashes($_SESSION['message']);
            unset($_SESSION['message']);
        }
    }

}

?>
