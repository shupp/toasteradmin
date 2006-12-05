<?php

/**
 *
 * Language settings for ToasterAdmin
 *
 * This is where language info is setup
 *
 * @author Bill Shupp <hostmaster@shupp.org>
 * @package ToasterAdmin
 * @version 1.0
 *
 */

// echo $_SERVER['HTTP_ACCEPT_LANGUAGE'];exit;
// This needs to be slicker, for now here's what I'll do:
$lang = ereg_replace('([^-]*)-.*$', '\1', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
// echo $lang;exit;

$locale = $lang; // Pretend this came from the Accept-Language header
$locale_dir = $base_dir . '/locale'; // your .po and .mo files should be at $locale_dir/$locale/LC_MESSAGES/messages.{po,mo}

putenv("LANGUAGE=$locale");
bindtextdomain('messages', $locale_dir);
textdomain('messages');

// $language = 'en';
// putenv("LANG=$language"); 
// setlocale(LC_ALL, $language);

// Set the text domain as 'messages'
// $domain = 'messages';
// bindtextdomain($domain, $base_dir . "/locale"); 
// textdomain($domain);

// echo _('First Page');exit;



?>
