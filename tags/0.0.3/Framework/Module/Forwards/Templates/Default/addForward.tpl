<div class="boxtop"></div>
<div class="box">{t}Add Forward{/t}</div>
<div class="boxbottom"></div>

<div class="boxtop"></div>
<div class="box">

{$form.javascript}
<form {$form.attributes}>
{$form.assocElements.domain.html}
<div>
<table border="0">

    <tr>
        <td align="right" valign="top"><span style="color: #ff0000">*</span><b>{$form.assocElements.forward.label}</b></td>
        <td valign="top" align="left">{$form.assocElements.forward.html}@{$domain} {$form.assocElements.forward.error}</td>
    </tr>

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

<br />
<br />
<a href="{$domain_url}">{t}Domain Menu{/t}</a>


</div>
<div class="boxbottom"></div>
