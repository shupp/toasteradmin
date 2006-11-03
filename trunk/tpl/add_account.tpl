<?= _('Add Account to domain')?> {domain}<p>


<table border="0" " cellpadding="15">
<tr><td class="border">

<form action="{php_self}" method="post">
<input type="hidden" name="module" value="Accounts">
<input type="hidden" name="event" value="add_account_now">
<input type="hidden" name="domain" value="{domain}">


<table border="0" cellspacing="0" cellpadding="4">
<tr bgcolor="#cccccc">
    <td class="title"><?= _('Account')?></td>
    <td class="title"><?= _('Real Name')?></td>
    <td class="title"><?= _('Password')?></td>
</tr>

<tr>
    <td><input type="text" name="account" value="{account}"></td>
    <td><input type="text" name="comment" value="{comment}"></td>
    <td><input type="text" name="password" value="{password}"></td>
</tr>
<tr>
    <td colspan="3" align="middle"><input type="submit" value="<?= _("Add Account") ?>"></td>
</tr>
</table>
</form>

<center><p><a href="{domain_url}"><?= _('Domain Menu')?></a></center>

</td></tr>
</table>
