<div class="boxtop"></div>
<div class="box">
<strong>{$domain}</strong>
</div>
<div class="boxbottom"></div>


<div class="boxtop"></div>
<div class="box">
<table border="0" cellpadding="15">
<tr><td class="border">
<ul>
    <li><a href="{$list_accounts_url}">{t}Email Accounts{/t}</a></li>
    <li><a href="{$list_forwards_url}">{t}Forwards{/t}</a></li>
    <li><a href="{$list_responders_url}">{t}Auto Responders{/t}</a></li>
    <!-- <li><a href="{$list_lists_url}">{t}Mailing Lists{/t}</a></li> -->
</ul>
</td>
</tr>
</table>
{if $isSysAdmin}<a href="./?module=Domains">{t}Domain List{/t}</a>{/if}
</div>
<div class="boxbottom"></div>
