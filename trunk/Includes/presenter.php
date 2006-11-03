<?php

/**
 *
 * Presenter
 *
 * This class extends BTS.  It mainly adds some useful functions like setting
 * messages in _SESSION, as well as some display functions for login/logout
 * URLs, menu links, and the like.
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 * @package BTS
 * @version 1.0
 *
 */

require_once('bts_class.php');

/**
 *
 * Presenter
 *
 * This class extends BTS.  It mainly adds some useful functions like setting
 * messages in _SESSION, as well as some display functions for login/logout
 * URLs, menu links, and the like.
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 * @package BTS
 * @version 1.0
 *
 */
class Presenter extends bts {


/**
 * Set Message Error
 *
 * Identical to {@link set_msg}, except it wraps the message in span class=error
 */

function set_msg_err($error) {
    $_SESSION['message'] = '<span class="error">' . $error . '</span>';
}

/**
 * Set Message
 *
 * Places $msg in _SESSION['message'], which is later parsed by {@link display_msg()}
 */

function set_msg($msg) {
    $_SESSION['message'] = $msg;
}

/**
 * Display Message
 *
 * Displays _SESSION['message'] and then unsets it.  Works with {@link set_msg()} and {@link set_msg_err()}
 */
function display_msg() {
    if(isset($_SESSION['message'])) {
        echo stripslashes($_SESSION['message']);
        unset($_SESSION['message']);
    }
}

/**
 * Wrap Template, Exit
 *
 * Wrap a template file in header.tpl and footer.tpl, then exit.  
 * Such a common task, I finally made it a function
 */
function wrap_exit($file = '') {

    global $tpl;
    $tpl->display('header.tpl');
    if($file != '') $tpl->display($file);
    $tpl->display('footer.tpl');
    exit;

}

/**
 * Display Logged In Message
 *
 * If the user is logged in, display logged_in_msg.tpl
 *
 */
function display_logged_in_msg() {

    global $tpl;
    if(isset($_SESSION['email']) && $_SESSION['email'] != '') {
        $tpl->assign('logout_url', $_SERVER['PHP_SELF'] . '?module=Logout');
        $tpl->display('logged_in_msg.tpl');
    }
    
}

/**
 * Display Main Menu Link
 *
 * If the user is logged in, display menu_link.tpl
 *
 */
function display_menu_link() {

    global $tpl, $vp;
    if(isset($_SESSION['email']) && isset($_REQUEST['module'])) {
        if($vp->has_sysadmin_privs()) {
            if($_REQUEST['module'] == 'Domains' && isset($_REQUEST['event'])) {
                $tpl->display('menu_link.tpl');
            } else if($_REQUEST['module'] != 'Domains') {
                $tpl->display('menu_link.tpl');
            }
            return;
        }
    }
    
}


/**
 * Paginate
 *
 * Pagination
 *
 */
function paginate($page,$pages,$url) {
    global $tpl;    
    $tpl->assign('current_page', trim($page));
    $tpl->assign('total_pages', trim($pages));
    if($page == 1) {
        $tpl->assign('first_page_link', _('First Page'));
    } else {
    $tpl->assign('first_page_link', '<a href="' . htmlspecialchars($url . '&page=1') . '">'._('First Page').'</a>');
    }
    if($page == $pages) {
        $tpl->assign('last_page_link', _('Last Page'));
    } else {
        $tpl->assign('last_page_link', '<a href="' . htmlspecialchars($url . '&page=' . $pages) . '">'._('Last Page').'</a>');
    }
    if($page >= $pages) {
        $tpl->assign('next_page_link', _('Next Page'));
    } else {
        $tpl->assign('next_page_link', '<a href="' . htmlspecialchars($url . '&page=' . ($page + 1)) . '">'._('Next Page').'</a>');
    }
    if($page <= 1) {
        $tpl->assign('previous_page_link', _('Previous Page'));
    } else {
        $tpl->assign('previous_page_link', '<a href="' . htmlspecialchars($url . '&page=' . ($page - 1)) . '">'._('Previous Page').'</a>');
    }
}

/**
 * Round Up
 *
 * Simple function to round up.  For page numbers.
 *
 */
function round_up($value) {
   if(is_float($value)) {
       $exploded = explode(".",$value);
       return $exploded[0] + 1;
   } else {
       return $value;
   }
}



}


?>
