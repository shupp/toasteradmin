<?= _('Email Accounts in domain')?> {domain}<p>


<table border="0" cellpadding="15">
<tr><td class="border">

<table border="0" cellspacing="0" cellpadding="3">
<tr>
    <td colspan="3">
    <?php $this->display('pagination.tpl') ?>
    </td>
</tr>
</table>

<div align="right"><a href="{add_account_url}"><?= _('Add Account') ?></a></div>

<table border="0" cellspacing="0" cellpadding="4">
<tr bgcolor="#cccccc">
    <td class="title"><?= _('Account')?></td>
    <td class="title"><?= _('Comment')?></td>
    <td class="title"><?= _('Quota')?></td>
    <td class="title"><?= _('Edit')?></td>
    <td class="title"><?= _('Delete')?></td>
</tr>

<?php foreach($accounts as $account): ?>
<tr bgcolor="<?= $this->cycle('#dddddd,#eeeeee')?>">
    <td><?= $account['account']?></td>
    <td><?= $account['comment']?></td>
    <td><?= $account['quota']?></td>
    <td><a href="<?= $account['edit_url']?>"><?= _('edit')?></a></td>
    <td><a href="<?= $account['delete_url']?>"><?= _('delete')?></a></td>
</tr>
<?php endforeach ?>

</table>

<center><p><a href="{domain_url}"><?= _('Domain Menu')?></a></center>

</td></tr>
</table>
