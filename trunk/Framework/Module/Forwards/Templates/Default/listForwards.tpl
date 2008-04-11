<div class="boxtop"></div>
<div class="box">
<strong>{t}Forwards for domain{/t} {$domain}</strong>
</div>
<div class="boxbottom"></div>

<div class="boxtopDomains">
<div class="boxtopDomainscontent">
    <h1>{t}Forwards Page:{/t} {$currentPage} {t}of{/t} {$totalPages}</h1>
    {framework_pager start=$start limit=$limit total=$total} 
<a href="{$add_forward_url}">{t}Add Forward{/t}</a>
</div>
</div>

<div class="box">
<table border="0" cellspacing="0" cellpadding="4">
<tr>
    <td class="title">{t}Forward{/t}</td>
    <td class="title" wrap>{t}Recipient{/t}</td>
    <td class="title">{t}Edit{/t}</td>
    <td class="title">{t}Delete{/t}</td>
</tr>
{foreach from=$forwards item=forward}
<tr>
    <td>{$forward.name}</td>
    <td>{$forward.contents}</td>
    <td align="center"><a href="{$forward.edit_url}">{t}edit{/t}</a></td>
    <td align="center"><a href="{$forward.delete_url}">{t}delete{/t}</a></td>
</tr>
{/foreach}
</table>
<a href="{$domain_url}">{t}Domain Menu{/t}</a>
</div>
<div class="boxbottom"></div>
