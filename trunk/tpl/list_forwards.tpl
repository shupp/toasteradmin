<center><strong><?= _('Forwards in domain')?> {domain}</strong></center><p>

<table border="0" cellpadding="15">
<tr><td class="border" align="center">
<table border="0" cellspacing="0" cellpadding="3">
<tr>
    <td colspan="3">
    <?php $this->display('pagination.tpl') ?>
    </td>
</tr>
</table>

<table border="0" cellspacing="0" cellpadding="4">
<tr bgcolor="#cccccc">
    <td class="title"><?= _('Forward')?></td>
    <td class="title"><?= _('Recipient')?></td>
    <td class="title"><?= _('Edit')?></td>
    <td class="title"><?= _('Delete')?></td>
</tr>

<?php foreach($forwards as $forward): ?>
<tr bgcolor="<?= $this->cycle('#dddddd,#eeeeee')?>">
    <td><?= $forward['name']?></td>
    <td><?= $forward['contents']?></td>
    <td align="center"><a href="<?= $forward['edit_url']?>"><?= _('edit')?></a></td>
    <td align="center"><a href="<?= $forward['delete_url']?>"><?= _('delete')?></a></td>
</tr>
<?php endforeach ?>

</table>

<center><p><a href="{domain_url}"><?= _('Domain Menu')?></a></center>

</td></tr>
</table>
