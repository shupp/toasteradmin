<?= _('Add Domain')?><p>


<table border="0" " cellpadding="15">
<tr><td class="border">

<form action="{php_self}" method="post">
<input type="hidden" name="module" value="Domains">
<input type="hidden" name="event" value="add_domain_now">


<table border="0" cellspacing="0" cellpadding="4">
<tr>
    <td class="title"><?= _('Domain')?></td>
    <td class="title"><?= _('Password')?></td>
</tr>
<tr>
    <td><input type="text" name="domain"></td>
    <td><input type="text" name="password"></td>
</tr>

<tr>
    <td colspan="2" align="middle"><input type="submit" value="<?= _("Add Domain") ?>"></td>
</tr>
</table>
</form>

</td></tr>
</table>
