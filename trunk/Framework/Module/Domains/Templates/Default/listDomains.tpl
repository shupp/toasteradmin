<a href='./?module=Logout'>Logout</a>
<!-- box 1 -->
<div class="boxtop"></div>
<div class="box">
<strong><a href="{$add_domain_url}">{php} echo  _('Add Domain'){/php}</a></strong>
</div>
<div class="boxbottom"></div>
<!-- eof box 1 -->
<!-- box 2 -->
<div class="boxtopDomains">
    <div class="boxtopDomainscontent">
        <h1>Existing Domains: Page List </h1>
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
            <td class="editcell"><a href="{$domain.edit_url}">{php} echo _('edit'){/php}</a> | <a href="{$domain.delete_url}">{php} echo _('delete'){/php}</a></td>
        </tr>
        <tr>
            <td colspan="2" class="dividercell"></td>
        </tr>
        {/foreach}
    </table>
    <!-- end of the table, which was also used partially because it's late and I'm tired -->
    <!-- eof box 2 -->
    <div class="clear"></div>
</div>
<div class="boxbottom"></div>
