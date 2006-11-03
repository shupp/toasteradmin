<table border="0" cellpadding="15">

<tr>
    <td class="border">


<center><br><?= _('please log in')?>:<p></center>

<form action="{php_self}" method="post">
<input type="hidden" name="{$session_name}" value="{$session_id}">
<input type="hidden" name="module" value="Login">
<input type="hidden" name="event" value="login_now">

<table border=0>
    <tr>
        <td><?= _('Email Address')?></td>
        <td><input type="text" name="email_address"></td>
    </tr>
    <tr>
        <td><?= _('Password')?></td>
        <td><input type="password" name="password"></td>
    </tr>
    <tr>
        <td colspan=2 align="right"><input type="submit" value="<?= _('Login')?>"></td>
    </tr>
</table>

</form>

</td>
</tr>
</table>
