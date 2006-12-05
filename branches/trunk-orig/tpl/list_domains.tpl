<strong><?= _('Admin Main') ?></strong>

<table border="0" cellpadding="15">
<tr>
<td class="border">
<table border="0" cellspacing="0" cellpadding="3">
<tr>
    <td colspan="3">
    <?php $this->display('pagination.tpl') ?>
    </td>
<tr>
    <td colspan="3" align="right"><a href="{add_domain_url}"><?= _('Add Domain') ?></a></td>
</tr>

<tr>
    <td class="title"><?= _('Domain') ?></td>
    <td class="title"><?= _('Edit') ?></td>
    <td class="title"><?= _('Delete') ?></td>
</tr>
<?php foreach($domains as $domain): ?>
<tr bgcolor="<?= $this->cycle('#dddddd,#eeeeee')?>">
    <td><?= $domain['name']?></td>
    <td align="center"><a href="<?= $domain['edit_url']?>"><?= _('edit') ?></a></td>
    <td align="center"><a href="<?= $domain['delete_url']?>"><?= _('delete') ?></a></td>
</tr>
<?php endforeach ?>

</table>

</td>
</tr>
</table>
