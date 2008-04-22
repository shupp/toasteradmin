<div class="boxtop"></div>
<div class="box">
{t}AutoResponders in domain{/t} {$domain}
</div>
<div class="boxbottom"></div>

<div class="boxtopDomains">
<div class="boxtopDomainscontent">
    <h1>{t}AutoResponders Page{/t} {$currentPage} {t}of{/t} {$totalPages}</h1>
{framework_pager start=$start limit=$limit total=$total} 
<a href="{$add_url}">{t}Add AutoResponder{/t}</a>
</div>
</div>

<div class="box">

<table border="0" cellspacing="0" cellpadding="0" id="datatable">
<tr>
    <td class="domaincell">{t}AutoResponder{/t}</td>
    <td class="domaincell">{t}Edit{/t}</td>
    <td class="domaincell">{t}Delete{/t}</td>
</tr>

{foreach from=$autoresponders item=autoresponder}
<tr bgcolor="<?= $this->cycle('#dddddd,#eeeeee')?>">
    <td>{$autoresponder.autoresponder}</td>
    <td><a href="{$autoresponder.edit_url}">{t}edit{/t}</a></td>
    <td><a href="{$autoresponder.delete_url}">{t}delete{/t}</a></td>
</tr>
{/foreach}
</table>
<center><a href="{$domain_url}">{t}Domain Menu{/t}</a></center>
</div>
<div class="boxbottom"></div>
