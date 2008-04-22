{if $nav->total > $nav->limit}
    <div class="framework_pager">
    <table border="0"><tr valign="center">
    {if $nav->start > 1}
        <td>
        <a class="begin" href="{$url}{$s}start={$nav->getBeginning()}"><img src="images/bt-back-all.gif" border="0" alt="first page" /></a>
        </td>
        <td>
        <a class="prev" href="{$url}{$s}start={$nav->getPrevPage()}"><img src="images/bt-back1.gif" border="0" alt="previous page" /></a>
        </td>
    {/if}
    {foreach key=key item=val from=$nav->getPageList()}
        <td>
        {if $nav->start == $val}
            <a class="page current" href="{$url}{$s}start={$val}">{$key}</a>
        {else}
            <a class="page" href="{$url}{$s}start={$val}">{$key}</a>
        {/if}
        </td>
    {/foreach}
    {if $nav->getNextPage() > 1}
    <td>
        <a class="next" href="{$url}{$s}start={$nav->getNextPage()}"><img src="images/bt-nextpage.gif" border="0" alt="next page" /></a>
    </td>
    {/if}
    {if $nav->start < $nav->getEnd()}
    <td>
        <a class="end" href="{$url}{$s}start={$nav->getEnd()}"><img src="images/bt-lastpage.gif" border="0" alt="last page" /></a>
    </td>
    {/if}
    </tr></table>
    </div>
{/if}
