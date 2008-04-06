<!-- box 1 -->
<div class="boxtop"></div>
<div class="box">
<strong><a href="{$add_domain_url}">{$LANG_Add_Domain}</a></strong>
</div>
<div class="boxbottom"></div>
<!-- eof box 1 -->
<!-- box 2 -->
<div class="boxtopDomains">
    <div class="boxtopDomainscontent">
        <h1>{$LANG_Domains_Page} {$currentPage} {$LANG_of} {$totalPages}</h1>
    {framework_pager start=$start limit=$limit total=$total} 
    </div>
</div>


<div class="box">
    <div class="clear"></div>
    <!-- using a table here because it seems to make sense to do so, since this is for data -->
    <table border="0" cellpadding="0" cellspacing="0" id="datatable">
        {foreach from=$domains item=domain}
        <tr>
            <td class="domaincell">{$domain.name}</td>
            <td class="editcell"><a href="{$domain.limits_url}">{t}limits{/t}</a> | <a href="{$domain.edit_url}">{t}edit{/t}</a> | <a href="{$domain.delete_url}">{t}delete{/t}</a></td>
        </tr>
        <tr>
            <td colspan="2" class="dividercell"></td>
        </tr>
        {/foreach}
    </table>
    <!-- end of the table, which was also used partially because it's late and I'm tired -->
    <!-- eof box 2 -->
    <div class="clear"></div>
    <a href="./?module=Main">Main Menu</a>
</div>
<div class="boxbottom"></div>
