<strong><?= _('Admin Main') ?></strong>

<table border="0" cellpadding="15">
<tr>
<td class="border">
<table border="0" cellspacing="0" cellpadding="3">
<tr>
    <td colspan="3">
    {framework_pager start=$start limit=$limit total=$total}
    </td>
<tr>
    <td colspan="3" align="right"><a href="{$add_domain_url}">{php} echo  _('Add Domain'){/php}</a></td>
</tr>

<tr>
    <td class="title"><?= _('Domain') ?></td>
    <td class="title"><?= _('Edit') ?></td>
    <td class="title"><?= _('Delete') ?></td>
</tr>
{foreach from=$domains item=domain}
<tr bgcolor="{cycle values="#dddddd,#eeeeee'"}">
    <td>{$domain.name}</td>
    <td align="center"><a href="{$domain.edit_url}">{php} echo _('edit'){/php}</a></td>
    <td align="center"><a href="{$domain.delete_url}">{php} echo _('delete'){/php}</a></td>
</tr>
{/foreach}

</table>

</td>
</tr>
</table>
