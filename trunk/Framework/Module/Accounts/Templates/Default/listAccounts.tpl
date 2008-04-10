<div class="boxtop"></div>
<div class="box">
{t}Email Accounts in domain{/t} {$domain}
</div>
<div class="boxbottom"></div>

<div class="boxtopDomains">
<div class="boxtopDomainscontent">
    <h1>{t}Accounts Page{/t} {$currentPage} {t}of{/t} {$totalPages}</h1>
{framework_pager start=$start limit=$limit total=$total} 
<a href="{$add_account_url}">{t}Add Account{/t}</a>
</div>
</div>

<div class="box">

<table border="0" cellspacing="0" cellpadding="0" id="datatable">
<tr>
    <td class="domaincell">{t}Account{/t}</td>
    <td class="domaincell">{t}Comment{/t}</td>
    <td class="domaincell">{t}Quota{/t}</td>
    <td class="domaincell">{t}Edit{/t}</td>
    <td class="domaincell">{t}Delete{/t}</td>
</tr>

{foreach from=$accounts item=account}
<tr bgcolor="<?= $this->cycle('#dddddd,#eeeeee')?>">
    <td>{$account.account}</td>
    <td>{$account.comment}</td>
    <td>{$account.quota}</td>
    <td><a href="{$account.edit_url}">{t}edit{/t}</a></td>
    <td><a href="{$account.delete_url}">{t}delete{/t}</a></td>
</tr>
{/foreach}
</table>
<center><a href="{$domain_url}">{t}Domain Menu{/t}</a></center>
</div>
<div class="boxbottom"></div>
