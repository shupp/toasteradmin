<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
    <HEAD>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
        <TITLE><?= _('Toaster Administration')?></TITLE>
        <link rel="STYLESHEET" type="text/css" href="core-style.css">
    </HEAD>
    <body>
     
<center>

<table border=0 width="100%">
    <tr valign="top">

    <?php if(isset($_SESSION['email'])): ?>
    <td width="5%" nowrap><?php $this->display_logged_in_msg()?></td>
    <?php endif ?>

    <td align="center">

    <?php if(!isset($_SESSION['email'])): ?>
    <br><img src="images/toasteradmin.jpg" border="0" alt="<?= _('ToasterAdmin Logo')?>"><br>
{$version}
    <p>
    <?php endif ?>
    <b><?php $this->display_msg()?></b><br>
