<?php

/**
 *
 * Forward Module
 *
 * This module is for viewing and editing vpopmail forwards
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 * @package TA_Modules
 * @version 1.0
 *
 */

// Make sure doamin was supplied
if(!isset($_REQUEST['domain'])) {
    $tpl->set_msg_err(_("Error: no domain supplied"));
    $tpl->wrap_exit();
}
$domain = $_REQUEST['domain'];

// Verify that they have access to this domain
if(!$vp->has_sysadmin_privs()) {
    if(!$vp->has_domain_privs($domain)) {
        if(!isset($_REQUEST['event'])) {
            $tpl->set_msg_err(_('Error: you do not have edit privileges on domain ') . $domain);
            $tpl->wrap_exit();
        }
    }
}


if(!isset($_REQUEST['event'])) {

    // Pagintation setup
    $full_alias_array = $vp->ListAlias($domain);
    if($vp->Error) die ("Error: {$vp->Error}");
    $alias_count = count($full_alias_array);
    $pages = $tpl->round_up($alias_count / $max_per_page);
    if(isset($_REQUEST['page'])) {
        if(!ereg('[^0-9]', $_REQUEST['page'])) $page = $_REQUEST['page'];
    }
    if(!isset($page)) $page = 1;
    $tpl->paginate($page, $pages, htmlspecialchars("$base_url?module=Forwards&domain=$domain"));

    // List Accounts
    $alias_array = $vp->ListAliases($full_alias_array, $page, $max_per_page);

    if(count($alias_array) == 0) {
        $tpl->set_msg_err(_('No forwards to display') . $domain);
        $tpl->wrap_exit();
    }

    $aliases = array();
    $count = 0;
    while(list($key,$val) = each($alias_array)) {
        $aliases[$count]['name'] = ereg_replace('^.qmail-', '', $val);
        $aliases[$count]['contents'] = $vp->GetAliasContents($val, $domain);
        $aliases[$count]['edit_url'] = htmlspecialchars("$base_url?module=Forwards&domain=$domain&forward=$val&event=modify");
        $aliases[$count]['delete_url'] = htmlspecialchars("$base_url?module=Forwards&domain=$domain&forward=$val&event=delete");
        $count++;
    }
    $tpl->assign('domain_url', htmlspecialchars("$base_url?module=Domains&domain=$domain"));
    $tpl->assign('domain', $domain);
    $tpl->assign('forwards', $aliases);
    $tpl->wrap_exit('list_forwards.tpl');

} else if($_REQUEST['event'] == 'modify') {

    // Make sure account was supplied
    if(!isset($_REQUEST['forward'])) {
        $tpl->set_msg_err(_('Error: no forward supplied'));
        $tpl->wrap_exit();
    }
    $forward = ereg_replace('^.qmail-', '', $_REQUEST['forward']);

    // Get forward info if it exists
    $contents = $vp->ReadFile($domain, '', ".qmail-$forward");
    if($vp->Error && $vp->Error != 'command failed - -ERR XXX No such file or directory') 
        die ("Error: {$vp->Error}");

    $count = 0;
    while(list($key,$val) = each($contents)) {
        $forward_array[$count]['destination'] = $vp->display_forward_line($val);
        $forward_array[$count]['delete_url'] = htmlspecialchars("$base_url?module=Forwards&event=delete_forward_line&domain=$domain&forward=" . $_REQUEST['forward'] . "&line=$val");
        $count++;
    }

    // Set template data
    $tpl->assign('forward', $forward);
    $tpl->assign('forward_contents', $forward_array);
    $tpl->assign('forwards_url', htmlspecialchars("$base_url?module=Forwards&domain=$domain"));
    $tpl->assign('domain', $domain);

    $tpl->wrap_exit('modify_forward.tpl');

} else if($_REQUEST['event'] == 'delete_forward_line') {

    // Make sure forward was supplied
    if(!isset($_REQUEST['forward'])) {
        $tpl->set_msg_err(_('Error: no forward supplied'));
        $tpl->wrap_exit('back.tpl');
    }
    $forward = ereg_replace('^.qmail-', '', $_REQUEST['forward']);

    // Make sure forward line was supplied
    if(!isset($_REQUEST['line'])) {
        $tpl->set_msg_err(_('Error: no forward destination line supplied'));
        $tpl->wrap_exit();
    }
    $line = $_REQUEST['line'];

    // Get forward info if it exists
    $contents = $vp->ReadFile($domain, '', ".qmail-$forward");
    if($vp->Error && $vp->Error != 'command failed - -ERR XXX No such file or directory') 
        die ("Error: {$vp->Error}");

    // Now build a new array without that forward
    $new_contents = array();
    $count = 0;
    while(list($key,$val) = each($contents)) {
        if($val != $line) {
            $new_contents[$count] = $val;
            $count++;
        }
    }

    if(count($new_contents) == 0) {
        $vp->RmFile($domain, '', ".qmail-$forward");
        $message = $tpl->set_msg(_("Forward Deleted Successfully"));
        $redirect = "$base_url?module=Forwards&domain=" . urlencode($domain);
    } else {
        $vp->WriteFile($new_contents, $domain, '', ".qmail-$forward");
        if($vp->Error && $vp->Error != 'command failed - -ERR XXX No such file or directory') 
            die ("Error: {$vp->Error}");
        $tpl->set_msg(_("Forward Modified Successfully"));
        $redirect = "$base_url?module=Forwards&domain=" . urlencode($domain) 
            . '&forward=' . urlencode($_REQUEST['forward']) . '&event=modify';
    }
    header("Location: $redirect");
    exit;

} else if($_REQUEST['event'] == 'add_forward_line') {

    // Make sure forward was supplied
    if(!isset($_REQUEST['forward'])) {
        $tpl->set_msg_err(_('Error: no forward supplied'));
        $tpl->wrap_exit();
    }
    // Make sure destination was supplied
    if(!isset($_REQUEST['destination'])) {
        $tpl->set_msg_err(_('Error: no destination supplied'));
        $tpl->wrap_exit();
    }
    $forward = ereg_replace('^.qmail-', '', $_REQUEST['forward']);
    $destination = $_REQUEST['destination'];
    if(!checkEmailFormat($destination))
        $destination = $destination . '@' . $domain;
    if(!checkEmailFormat($destination)) {
        $tpl->set_msg_err(_('Error: invalid forward supplied'));
        $tpl->wrap_exit('back.tpl');
    }

    // Add it!
    $contents = $vp->ReadFile($domain, '', ".qmail-$forward");
    if($vp->Error && $vp->Error != 'command failed - -ERR XXX No such file or directory') 
        die ("Error: {$vp->Error}");

    // Now build a new array without that forward
    array_push($contents, "&$destination");
    $vp->WriteFile($contents, $domain, '', ".qmail-$forward");
    if($vp->Error && $vp->Error != 'command failed - -ERR XXX No such file or directory') 
        die ("Error: {$vp->Error}");
    $tpl->set_msg(_("Forward Modified Successfully"));
    $redirect = "$base_url?module=Forwards&domain=" . urlencode($domain) 
        . '&forward=' . urlencode($_REQUEST['forward']) . '&event=modify';
    header("Location: $redirect");

    exit;

} else {
    $tpl->set_msg_err("Error: unknown event");
    $tpl->wrap_exit();
}

?>
