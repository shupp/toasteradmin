<?php
/**
 *
 * Domains Module
 *
 * This module is for viewing and editing vpopmail domains
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 * @package TA_Modules
 * @version 1.0
 *
 */


if(!isset($_REQUEST['event'])) {

    if(!$vp->has_sysadmin_privs()) {
        // Redirect to appropriate page
        if($vp->has_domain_privs($_REQUEST['domain'])) {
            header("Location: $base_url?module=Domains&event=domain_menu&domain=" . urlencode($_SESSION['domain']));
            exit;
        } else {
            $url = "$base_url?module=Accounts&domain=" . urlencode($_SESSION['domain']) . '&account=' . urlencode($_SESSION['user']) . '&event=modify';
            header("Location: $url");
            exit;
        }
    }

    // If it's a sysadmin, list domains

    // Pagintation setup
    $domain_count = $vp->DomainCount();
    if($vp->Error) die ("Error: {$vp->Error}");
    $pages = $tpl->round_up($domain_count / $max_per_page);
    if(isset($_REQUEST['page'])) {
        if(!ereg('[^0-9]', $_REQUEST['page'])) $page = $_REQUEST['page'];
    }
    if(!isset($page)) $page = 1;
    $tpl->paginate($page, $pages, $base_url . '?module=Domains');


    // Build domain list
    $domain_array = $vp->ListDomains($page,$max_per_page);
    $domains = array();
    $count = 0;
    while(list($key,$val) = each($domain_array)) {
        $domains[$count]['name'] = $key;
        $domains[$count]['edit_url'] = htmlspecialchars($base_url . '?module=Domains&event=domain_menu&domain=' . $key);
        $domains[$count]['delete_url'] = htmlspecialchars($base_url . '?module=Domains&event=del_domain&domain=' . $key);
        $count++;
    }
    $tpl->assign('domains', $domains);
    $tpl->assign('add_domain_url', htmlspecialchars("$base_url?module=Domains&event=add_domain"));
    $tpl->wrap_exit('list_domains.tpl');

} else if($_REQUEST['event'] == 'domain_menu') {

    // Make sure the domain was supplied
    if(!isset($_REQUEST['domain'])) {
        $tpl->set_msg_err(_('Error: no domain supplied'));
        $tpl->wrap_exit();
    }

    if(!$vp->has_domain_privs($_REQUEST['domain'])) {
        $tpl->set_msg_err(_('Error: you do not have edit privileges on domain ') . $_REQUEST['domain']);
        $tpl->wrap_exit();
    }
    // Setup URLs
    $tpl->assign('domain', $_REQUEST['domain']);
    $tpl->assign('list_accounts_url', htmlspecialchars($base_url . "?module=Accounts&domain=" . $_REQUEST['domain']));
    $tpl->assign('list_forwards_url', htmlspecialchars($base_url . "?module=Forwards&domain=" . $_REQUEST['domain']));
    $tpl->assign('list_responders_url', htmlspecialchars($base_url . "?module=Responders&domain=" . $_REQUEST['domain']));
    $tpl->assign('list_lists_url', htmlspecialchars($base_url . "?module=Lists&domain=" . $_REQUEST['domain']));
    $tpl->wrap_exit('domain_menu.tpl');

} else if($_REQUEST['event'] == 'add_domain') {

    if(!$vp->has_sysadmin_privs()) {
        $tpl->set_msg_err(_('Error: you do not have add domain privileges'));
        $tpl->wrap_exit();
    }

    $tpl->wrap_exit('add_domain.tpl');

} else if($_REQUEST['event'] == 'add_domain_now') {

    if(!$vp->has_sysadmin_privs()) {
        $tpl->set_msg_err(_('Error: you do not have add domain privileges'));
        $tpl->wrap_exit();
    }

    if(!isset($_REQUEST['domain']) || !isset($_REQUEST['password'])) {
        $tpl->set_msg_err(_("Error: domain or password missing"));
        $tpl->wrap_exit('back.tpl');
    }

    // Add domain
    $vp->AddDomain($_REQUEST['domain'], $_REQUEST['password']);
    if($vp->Error) {
        $tpl->set_msg_err(_("Error: ") . $vp->Error);
        $tpl->wrap_exit('back.tpl');
    }
    $tpl->set_msg(_("Domain added successfully"));
    $url = $base_url . "?module=Domains&event=domain_menu&domain=" . $_REQUEST['domain'];
    header("Location: $url");
    exit;

} else if($_REQUEST['event'] == 'del_domain') {

    if(!isset($_REQUEST['domain'])) {
        $tpl->set_msg_err(_("Error: domain missing"));
        $tpl->wrap_exit('back.tpl');
    }

    if(!$vp->has_sysadmin_privs()) {
        $tpl->set_msg_err(_('Error: you do not have delete domain privileges'));
        $tpl->wrap_exit();
    }

    $tpl->assign('domain', $_REQUEST['domain']);
    $tpl->assign('delete_url', htmlspecialchars("$base_url?module=Domains&event=del_domain_now&domain=" . $_REQUEST['domain']));
    $tpl->assign('cancel_url', htmlspecialchars("$base_url?module=Domains&event=cancel_del_domain"));
    $tpl->wrap_exit('domain_confirm_delete.tpl');

} else if($_REQUEST['event'] == 'del_domain_now') {

    if(!$vp->has_sysadmin_privs()) {
        $tpl->set_msg_err(_('Error: you do not have delete domain privileges'));
        $tpl->wrap_exit();
    }

    if(!isset($_REQUEST['domain'])) {
        $tpl->set_msg_err(_("Error: domain missing"));
        $tpl->wrap_exit('back.tpl');
    }

    // Delete domain
    $vp->DelDomain($_REQUEST['domain'], $_REQUEST['password']);
    if($vp->Error) {
        $tpl->set_msg_err(_("Error: ") . $vp->Error);
        $tpl->wrap_exit('back.tpl');
    }
    $tpl->set_msg(_("Domain deleted successfully"));
    $url = $base_url . "?module=Domains";
    header("Location: $url");
    exit;

} else if($_REQUEST['event'] == 'cancel_del_domain') {

    $tpl->set_msg(_("Domain deletion canceled"));
    header("Location: $base_url?module=Domains");
    exit;

} else {

    $tpl->set_msg_err(_("Error: unkown event"));
    $tpl->wrap_exit();

}
?>
