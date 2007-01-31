<div class="boxtop"></div>
<div class="box">
<strong>{$LANG_Forwards_for_domain} {$domain}</strong>
</div>
<div class="boxbottom"></div>

<div class="boxtopDomains">
<div class="boxtopDomainscontent">
    <h1>{$LANG_Forwards_Page} {$currentPage} {$LANG_of} {$totalPages}</h1>
    {framework_pager start=$start limit=$limit total=$total} 
<a href="{$add_forward_url}">{$LANG_Add_Forward}</a>
</div>
</div>

<div class="box">
<table border="0" cellspacing="0" cellpadding="4">
<tr>
    <td class="title">{$LANG_Forward}</td>
    <td class="title" wrap>{$LANG_Recipient}</td>
    <td class="title">{$LANG_Edit}</td>
    <td class="title">{$LANG_Delete}</td>
</tr>
{foreach from=$forwards item=forward}
<tr>
    <td>{$forward.name}</td>
    <td>{$forward.contents}</td>
    <td align="center"><a href="{$forward.edit_url}">{$LANG_edit}</a></td>
    <td align="center"><a href="{$forward.delete_url}">{$LANG_delete}</a></td>
</tr>
{/foreach}
</table>
<a href="{$domain_url}">{$LANG_Domain_Menu}</a>
</div>
<div class="boxbottom"></div>
