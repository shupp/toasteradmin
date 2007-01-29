<div class="boxtop"></div>
<div class="box">
{$LANG_Email_Accounts_in_domain} {$domain}
</div>
<div class="boxbottom"></div>

<div class="boxtopDomains">
<div class="boxtopDomainscontent">
    <h1>{$LANG_Accounts_Page} {$currentPage} {$LANG_of} {$totalPages}</h1>
{framework_pager start=$start limit=$limit total=$total} 
<a href="{$add_account_url}">{$LANG_Add_Account}</a>
</div>
</div>

<div class="box">

<table border="0" cellspacing="0" cellpadding="0" id="datatable">
<tr>
    <td class="domaincell">{$LANG_Account}</td>
    <td class="domaincell">{$LANG_Comment}</td>
    <td class="domaincell">{$LANG_Quota}</td>
    <td class="domaincell">{$LANG_Edit}</td>
    <td class="domaincell">{$LANG_Delete}</td>
</tr>

{foreach from=$accounts item=account}
<tr bgcolor="<?= $this->cycle('#dddddd,#eeeeee')?>">
    <td>{$account.account}</td>
    <td>{$account.comment}</td>
    <td>{$account.quota}</td>
    <td><a href="{$account.edit_url}">{$LANG_edit}</a></td>
    <td><a href="{$account.delete_url}">{$LANG_delete}</a></td>
</tr>
{/foreach}
</table>
<center><a href="{$domain_url}">{$LANG_Domain_Menu}</a></center>
</div>
<div class="boxbottom"></div>
