<div class="boxtop"></div>
<div class="box">
{$LANG_AutoResponders_in_domain} {$domain}
</div>
<div class="boxbottom"></div>

<div class="boxtopDomains">
<div class="boxtopDomainscontent">
    <h1>{$LANG_AutoResponders_Page} {$currentPage} {$LANG_of} {$totalPages}</h1>
{framework_pager start=$start limit=$limit total=$total} 
<a href="{$add_url}">{$LANG_Add_AutoResponder}</a>
</div>
</div>

<div class="box">

<table border="0" cellspacing="0" cellpadding="0" id="datatable">
<tr>
    <td class="domaincell">{$LANG_AutoResponder}</td>
    <td class="domaincell">{$LANG_Edit}</td>
    <td class="domaincell">{$LANG_Delete}</td>
</tr>

{foreach from=$autoresponders item=autoresponder}
<tr bgcolor="<?= $this->cycle('#dddddd,#eeeeee')?>">
    <td>{$autoresponder.autoresponder}</td>
    <td><a href="{$autoresponder.edit_url}">{$LANG_edit}</a></td>
    <td><a href="{$autoresponder.delete_url}">{$LANG_delete}</a></td>
</tr>
{/foreach}
</table>
<center><a href="{$domain_url}">{$LANG_Domain_Menu}</a></center>
</div>
<div class="boxbottom"></div>
