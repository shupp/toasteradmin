<div class="framework_pager"{if strlen($params.id)} id="{$params.id}"{/if}>
{if $nav->start > 1}
    <a class="begin" href="{$url}{$s}start={$nav->getBeginning()}">&laquo;</a>
    <a class="prev" href="{$url}{$s}start={$nav->getPrevPage()}">&laquo; Prev</a>
{/if}
{foreach key=key item=val from=$nav->getPageList()}
    {if $start == $val}
        <a class="page current" href="{$url}{$s}start={$val+1}">{$key}</a> | 
    {else}
        <a class="page" href="{$url}{$s}start={$val+1}">{$key}</a> | 
    {/if}
{/foreach}
{if $nav->getNextPage() > 1}
    <a class="next" href="{$url}{$s}start={$nav->getNextPage()}"><img src="images/bt-nextpage.gif" border="0"></a>
{/if}
{if $start < $nav->getEnd()}
    <a class="end" href="{$url}{$s}start={$nav->getEnd()}"><img src="images/bt-lastpage.gif" border="0"></a>
{else}
    <span class="end disabled">&raquo;</span>
{/if}
</div>
