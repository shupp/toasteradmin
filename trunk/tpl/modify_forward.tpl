<?= _("Modify Forward")?> {forward}<p>

<table border="0" cellpadding="15">
<tr><td class="border">

<table border="0" cellspacing="0" cellpadding="3">
<tr>
<td class="title"><?= _("Destination") ?></td>
<td class="title"><?= _("Delete") ?></td>
</tr>

<?php foreach($this->var_array['forward_contents'] as $line): ?>

<tr bgcolor="<?= $this->cycle('#dddddd,#eeeeee') ?>">
    <td><?= $line['destination'] ?></td>
    <td align="middle"><a href="<?= $line['delete_url'] ?>"><img src="images/trash.png" border="0" alt="<?= _("delete") ?>"></a></td>
</tr>

<?php endforeach ?>

<form action="{php_self}" action="post">
<input type="hidden" name="forward" value="{forward}">
<input type="hidden" name="module" value="Forwards">
<input type="hidden" name="event" value="add_forward_line">
<input type="hidden" name="domain" value="{domain}">

<tr><td colspan="2">&nbsp<br><p></td></tr>
<tr>
<td class="title" colspan="2"><?= _("Add Address to Forward") ?></td>
</tr>
<td align="right"><?= _("Address")  ?>:</td>
<td><input type=text name="destination"></td>
</tr>
<tr><td colspan="2" align="middle"><input type="submit" value="<?= _("Add Address") ?>"></td></tr>
</table>
</form>


<p><center><a href="{forwards_url}"><?= _("Forwards Menu") ?><a></center>
<p>
