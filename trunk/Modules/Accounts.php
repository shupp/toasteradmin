<?php

/**
 *
 * Accounts Module
 *
 * This module is for viewing and editing vpopmail accounts
 * succeeds
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
$tpl->assign('domain', $domain);
$tpl->assign('domain_url', $base_url . htmlspecialchars('?module=Domains&event=domain_menu&domain=' . $domain));

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
    $user_count = $vp->UserCount($domain);
    if($vp->Error) {
        $tpl->set_msg_err(_("Error: ") . $vp->Error);
        $tpl->wrap_exit('back.tpl');
    }
    $pages = $tpl->round_up($user_count / $max_per_page);
    if(isset($_REQUEST['page'])) {
        if(!ereg('[^0-9]', $_REQUEST['page'])) $page = $_REQUEST['page'];
    }
    if(!isset($page)) $page = 1;
    $tpl->paginate($page, $pages, $base_url . '?module=Accounts&domain=' . urlencode($domain));

    // List Accounts
    $account_array = $vp->ListUsers($domain, $page, $max_per_page);
    $accounts = array();
    $count = 0;
    while(list($key,$val) = each($account_array)) {
        $accounts[$count]['account'] = $key;
        $accounts[$count]['comment'] = $val['comment'];
        $accounts[$count]['quota'] = $vp->get_quota($val['quota']);
        $accounts[$count]['edit_url'] = htmlspecialchars("$base_url?module=Accounts&domain=$domain&account=$key&event=modify");
        $accounts[$count]['delete_url'] = htmlspecialchars("$base_url?module=Accounts&domain=$domain&account=$key&event=delete");
        $count++;
    }
    $tpl->assign('accounts', $accounts);
    $tpl->assign('add_account_url', htmlspecialchars("$base_url?module=Accounts&event=add_account&domain=$domain"));
    $tpl->wrap_exit('list_accounts.tpl');

} else if($_REQUEST['event'] == 'modify') {

    // Make sure account was supplied
    if(!isset($_REQUEST['account'])) {
        $tpl->set_msg_err(_('Error: no account supplied'));
        $tpl->wrap_exit();
    }
    $account = $_REQUEST['account'];

    // Check privs
    if(!$vp->has_user_privs($account, $domain)) {
        $tpl->set_msg_err(_('Error: you do not have edit privileges on domain ') . $domain);
        $tpl->wrap_exit();
    }

    // See what user_info to use
    if($_REQUEST['account'] == $_SESSION['user'] && $domain == $_SESSION['domain']) {
        $account_info = $user_info;
    } else {
        $account_info = $vp->UserInfo($domain, $_REQUEST['account']);
        if($vp->Error) {
            $tpl->set_msg_err(_("Error: ") . $vp->Error);
            $tpl->wrap_exit('back.tpl');
        }
    }

    // Get .qmail info if it exists
    $dot_qmail = $vp->ReadFile($domain, $_REQUEST['account'], '.qmail');
    if($vp->Error && $vp->Error != 'command failed - -ERR XXX No such file or directory') {
        $tpl->set_msg_err(_("Error: ") . $vp->Error);
        $tpl->wrap_exit('back.tpl');
    }
    $vp->parse_home_dotqmail($dot_qmail, $account_info);
    

    // Set template data
    $tpl->assign('account', $_REQUEST['account']);
    $tpl->assign('comment', $account_info['comment']);

    $tpl->wrap_exit('modify_account.tpl');

} else if($_REQUEST['event'] == 'modify_now') {

    // Make sure account was supplied
    if(!isset($_REQUEST['account'])) {
        $tpl->set_msg_err(_('Error: no account supplied'));
        $tpl->wrap_exit();
    }
    $account = $_REQUEST['account'];

    // Check privs
    if(!$vp->has_user_privs($account, $domain)) {
        $tpl->set_msg_err(_('Error: you do not have edit privileges on domain ') . $domain);
        $tpl->wrap_exit();
    }

    // See what user_info to use
    if($_REQUEST['account'] == $_SESSION['user'] && $domain == $_SESSION['domain']) {
        $account_info = $user_info;
    } else {
        $account_info = $vp->UserInfo($domain, $_REQUEST['account']);
        if($vp->Error) {
            $tpl->set_msg_err(_("Error: ") . $vp->Error);
            $tpl->wrap_exit('back.tpl');
        }
    }

    // Detect password changing
    $password_changing = 0;
    if(!empty($_REQUEST['password1']) || !empty($_REQUEST['password2'])) {
        if($_REQUEST['password1'] != $_REQUEST['password2']) {
            $tpl->set_msg_err(_('Error: passwords to not match'));
            $tpl->wrap_exit('back.tpl');
        }
        $password_changing = 1;
    }

    // Get routing
    if(!isset($_REQUEST['routing'])) {
        $tpl->set_msg_err(_('Error: message routing is not set'));
        $tpl->wrap_exit('back.tpl');
    }

    $routing = '';
    $save_a_copy = 0;
    if($_REQUEST['routing'] == 'routing_standard') {
        $routing = 'standard';
    } else if($_REQUEST['routing'] == 'routing_deleted') {
        $routing = 'deleted';
    } else if($_REQUEST['routing'] == 'routing_forwarded') {
        if(empty($_REQUEST['forward'])) {
            $tpl->set_msg_err(_('Error: you must supply a forward address'));
            $tpl->wrap_exit('back.tpl');
        } else {
            $forward = $_REQUEST['forward'];
            if(!checkEmailFormat($forward)) {
                $forward = $forward . '@' . $domain;
            }
        }
        $routing = 'forwarded';
        if(isset($_REQUEST['save_a_copy'])) $save_a_copy = 1;
    } else {
        $tpl->set_msg_err(_('Error: unsupported routing selection'));
        $tpl->wrap_exit('back.tpl');
    }

    // Check for vacation
    $vacation = 0;
    if(isset($_REQUEST['vacation'])) {
        $vacation = 1;
        $vacation_subject = $_REQUEST['vacation_subject'];
        $vacation_body = $_REQUEST['vacation_body'];
    }

    // Build .qmail contents
    $dot_qmail_contents = '';
    if($routing == 'deleted') {
        $dot_qmail_contents = "# delete";
    } else if($routing == 'forwarded') {
        $dot_qmail_contents = "&$forward";
        if($save_a_copy = 1) $dot_qmail_contents .= "\n./Maildir/";
    }

    if($vacation == 1) {
        if(strlen($dot_qmail_contents) > 0) $dot_qmail_contents .= "\n";
        $vacation_dir = $account_info['user_dir'] . '/vacation';
        $dot_qmail_contents .= "| $autorespond 86400 3 $vacation_dir/message $vacation_dir";
        // Example:
        // | /usr/local/bin/autorespond 86400 3 /home/vpopmail/domains/shupp.org/test/vacation/message /home/vpopmail/domains/shupp.org/test/vacation
    }

    $dot_qmail_file = $account_info['user_dir'] . '/.qmail';
    if(strlen($dot_qmail_contents) > 0) {
        $contents = explode("\n", $dot_qmail_contents);
        // Delete existing file
        $vp->RmFile($domain, $account_info['name'], $dot_qmail_file);
        // Write .qmail file
        $vp->WriteFile(explode("\n", $dot_qmail_contents), '', '', $dot_qmail_file);

        // Add vacation files
        if($vacation == 1) {
            $vcontents = "From: " . $account_info['name'] . "@$domain\n";
            $vcontents .= "Subject: $vacation_subject\n\n";
            $vcontents .= $vacation_body;
            $contents = explode("\n", $vcontents);
            $vdir = 'vacation';
            $message = 'vacation/message';
            // Delete existing file
            $vp->RmDir($domain, $account_info['name'], $vdir);
            // Make vacation directory
            $vp->MkDir($domain, $account_info['name'], $vdir);
            // Write vacation message
            $vp->WriteFile($contents, $domain, $account_info['name'], $message);
        }
    } else {
        $vp->RmDir($domain, $account_info['name'], 'vacation');
        $vp->RmFile('', '', $dot_qmail_file);
    }

    $url = $base_url . "?module={$_REQUEST['module']}&domain=$domain&account={$_REQUEST['account']}&event=modify";
    $tpl->set_msg(_('Account Modified Successfully'));
    header("Location: $url");
    exit;

} else if($_REQUEST['event'] == 'add_account') {

    $tpl->assign('account', '');
    $tpl->assign('comment', '');
    $tpl->assign('password', '');
    $tpl->wrap_exit('add_account.tpl');

} else if($_REQUEST['event'] == 'add_account_now') {

    $error = '';
    if(!isset($_REQUEST['account'])) {
        $tpl->assign('account', '');
        $error .= _("account");
    } else {
        $tpl->assign('account', $_REQUEST['account']);
    }
    if(!isset($_REQUEST['comment'])) {
        $tpl->assign('comment', '');
        $error .= _(" comment");
    } else {
        $tpl->assign('comment', $_REQUEST['comment']);
    }
    if(!isset($_REQUEST['password'])) {
        $tpl->assign('password', '');
        $error .= _(" password");
    } else {
        $tpl->assign('password', $_REQUEST['password']);
    }

    if(strlen($error) > 0) {
        $tpl->set_msg_err(_("Error: missing fields: ") . $error);
        $tpl->wrap_exit('add_account.tpl');
    }

    if(!checkEmailFormat($_REQUEST['account'] . '@' . $domain)) {
        $tpl->set_msg_err(_("Error: invalid account name") . $error);
        $tpl->wrap_exit('add_account.tpl');
    }

    $vp->AddUser($domain, $_REQUEST['account'], $_REQUEST['password'], $_REQUEST['comment']);
    if($vp->Error) {
        $tpl->set_msg_err(_("Error: ") . $vp->Error);
        $tpl->wrap_exit('back.tpl');
    }

    $tpl->set_msg(_("Account Added Successfully"));
    header("Location: $base_url?module=Accounts&domain=" . urlencode($domain));
    exit;

} else if($_REQUEST['event'] == 'delete') {

    if(!isset($_REQUEST['account'])) {
        $tpl->set_msg_err(_("Error: no account supplied"));
        header("Location: $base_url?module=Accounts&domain=" . urlencode($domain));
        exit;
    }

    $tpl->assign('account', $_REQUEST['account']);
    $tpl->assign('cancel_url', "$base_url?module=Accounts&event=cancel_delete&domain=" . urlencode($domain));
    $tpl->assign('delete_now_url', "$base_url?module=Accounts&event=delete_now&domain=" . urlencode($domain) . "&account=" . $_REQUEST['account']);
    $tpl->wrap_exit('account_confirm_delete.tpl');

} else if($_REQUEST['event'] == 'delete_now') {

    if(!isset($_REQUEST['account'])) {
        $tpl->set_msg_err(_("Error: no account supplied"));
        header("Location: $base_url?module=Accounts&domain=" . urlencode($domain));
        exit;
    }

    $vp->DelUser($domain, $_REQUEST['account']);
    if($vp->Error) {
        $tpl->set_msg_err(_("Error: ") . $vp->Error);
        $tpl->wrap_exit('back.tpl');
    }

    $tpl->set_msg(_("Account Deleted Successfully"));
    header("Location: $base_url?module=Accounts&domain=" . urlencode($domain));
    exit;

} else if($_REQUEST['event'] == 'cancel_delete') {

    $tpl->set_msg(_("Delete Canceled"));
    header("Location: $base_url?module=Accounts&domain=" . urlencode($domain));
    exit;

} else {
    $tpl->set_msg_err(_("Error: unknown event"));
    $tpl->wrap_exit();
}

?>
