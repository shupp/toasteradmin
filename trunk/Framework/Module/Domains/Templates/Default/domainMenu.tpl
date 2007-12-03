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
    <li><a href="{$list_accounts_url}">{$LANG_Email_Accounts}</a></li>
    <li><a href="{$list_forwards_url}">{$LANG_Forwards}</a></li>
    <li><a href="{$list_responders_url}">{$LANG_Auto_Responders}</a></li>
    <!-- <li><a href="{$list_lists_url}">{$LANG_Mailing_Lists}</a></li> -->
</ul>
</td>
</tr>
</table>
{if $isSysAdmin}<a href="./?module=Domains">{$LANG_Main_Menu}</a>{/if}
</div>
<div class="boxbottom"></div>
