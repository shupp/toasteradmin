<div class="boxtop"></div>
<div class="box">
<strong>{t}IP Maps{/t}</strong>
</div>
<div class="boxbottom"></div>


<div class="boxtop"></div>
<div class="box">
<table border="0" cellpadding="15">
<tr><td class="border">
<center>
<a href="./?module=Main&amp;class=IPMaps&amp;event=add">{t}Add IP Map{/t}</a>
</center>
<table border="0">
    <tr bgcolor="#cccccc"><td align="center">{t}IP{/t}</td><td align="center">{t}Domain{/t}</td><td align="center">{t}Delete{/t}</tr>
{foreach from=$mapList item=map}
    {foreach from=$map.domains item=domain}
    <tr><td>{$map.ip}</td><td>{$domain}</td><td><a href="./?module=Main&amp;class=IPMaps&amp;event=delete&amp;ip={$map.ip}&amp;domain={$domain}">delete</a></td></td>
    {/foreach}
{/foreach}
</table>
</td>
</tr>
</table>

<a href="./?module=Main">{t}Main Menu{/t}</a>
</div>
<div class="boxbottom"></div>
