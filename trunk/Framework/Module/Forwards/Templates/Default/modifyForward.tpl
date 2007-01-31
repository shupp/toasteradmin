<div class="boxtop"></div>
<div class="box"><b>{$LANG_Modify_Forward} "{$forward}@{$domain}"</b></div>
<div class="boxbottom"></div>

<div class="boxtop"></div>
<div class="box">
<table border="0" cellspacing="0" cellpadding="3">
<tr>
<td>{$LANG_Destination}</td>
<td>{$LANG_Delete}</td>
</tr>

{foreach from=$forward_contents item=line}
<tr>
    <td>{$line.destination}</td>
    <td align="center"><a href="{$line.delete_url}"><img src="images/trash.png" border="0" alt="{$LANG_delete}" /></a></td>
</tr>
{/foreach}

</table>

{$form.javascript}
<form {$form.attributes}>
{$form.assocElements.domain.html}
{$form.assocElements.forward.html}
<hr>
<div>
<b>{$LANG_Add_Destination}</b>
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
<a href="{$forwards_url}">{$LANG_Forwards_Menu}</a>
</div>
<div class="boxbottom"></div>
