<div style="vertical-align: middle;">
{if $nav->start > 1}
    <a class="begin" href="{$url}{$s}start={$nav->getBeginning()}"><img src="images/bt-back-all.gif" border="0" alt="first page" /></a>
    <a class="prev" href="{$url}{$s}start={$nav->getPrevPage()}"><img src="images/bt-back1.gif" border="0" alt="previous page" /></a>
{/if}
{foreach key=key item=val from=$nav->getPageList()}
    {if $start == $val}
        <a class="page current" href="{$url}{$s}start={$val+1}">{$key}</a> | 
    {else}
        <a class="page" href="{$url}{$s}start={$val+1}">{$key}</a> | 
    {/if}
{/foreach}
{if $nav->getNextPage() > 1}
    <a class="next" href="{$url}{$s}start={$nav->getNextPage()}"><img src="images/bt-nextpage.gif" border="0" alt="next page" /></a>
{/if}
{if $start < $nav->getEnd()}
    <a class="end" href="{$url}{$s}start={$nav->getEnd()}"><img src="images/bt-lastpage.gif" border="0" alt="last page" /></a>
{else}
    <span class="end disabled">&raquo;</span>
{/if}
</div>
