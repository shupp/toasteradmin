<div class="boxtop"></div>
<div class="box"><b>{t}Modify Forward{/t} "{$forward}@{$domain}"</b></div>
<div class="boxbottom"></div>

<div class="boxtop"></div>
<div class="box">
<table border="0" cellspacing="0" cellpadding="3">
<tr>
<td>{t}Destination{/t}</td>
<td>{t}Delete{/t}</td>
</tr>

{foreach from=$forward_contents item=line}
<tr>
    <td>{$line.destination}</td>
    <td align="center"><a href="{$line.delete_url}"><img src="images/trash.png" border="0" alt="{t}delete{/t}" /></a></td>
</tr>
{/foreach}

</table>

{$form.javascript}
<form {$form.attributes}>
{$form.assocElements.domain.html}
{$form.assocElements.forward.html}
<hr>
<div>
<b>{t}Add Destination{/t}</b>
<table border="0">

    <tr>
        <td align="right" valign="top"><span style="color: #ff0000">*</span><b>{$form.assocElements.destination.label}</b></td>
        <td valign="top" align="left">{$form.assocElements.destination.html} {$form.assocElements.destination.error}</td>
    </tr>
    <tr>
        <td align="right" valign="top"><b></b></td>
        <td valign="top" align="left">{$form.assocElements.submit.html}</td>

    </tr>
    <tr>
        <td></td>
    <td align="left" valign="top"> {$form.requirednote} </td>
    </tr>
</table>
</div>
</form>
<a href="{$forwards_url}">{t}Forwards Menu{/t}</a>
</div>
<div class="boxbottom"></div>
