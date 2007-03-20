<div class="boxtop"></div>
<div class="box">{$LANG_Add_AutoResponder_to_domain} {$domain}</div>
<div class="boxbottom"></div>

<div class="boxtop"></div>
<div class="box">
<p>{$form.requirednote}</p>
<p>{$form.javascript}</p>

<form {$form.attributes}>
<table border="0">

    <tr>
        <td align="right" valign="top"><font color="red">*</font><span class="label">{$form.assocElements.autoresponder.label}</span></td>
        <td valign="top" align="left">{$form.assocElements.autoresponder.html}{$form.assocElements.autoresponder.error}</td>
    </tr>
    <tr>
        <td align="right" valign="top"><span class="label">{$form.assocElements.copy.label}</span></td>
        <td valign="top" align="left">{$form.assocElements.copy.html}{$form.assocElements.copy.error}</td>
    </tr>
    <tr>
        <td align="right" valign="top"><font color="red">*</font><span class="label">{$form.assocElements.subject.label}</span></td>
        <td valign="top" align="left">{$form.assocElements.subject.html}{$form.assocElements.subject.error}</td>
    </tr>
    <tr>
        <td align="right" valign="top"><font color="red">*</font><span class="label">{$form.assocElements.body.label}</span></td>
        <td valign="top" align="left">{$form.assocElements.body.html}{$form.assocElements.body.error}</td>
    </tr>
    <tr>
        <td></td><td align="center">{$form.assocElements.submit.html}</td>
    </tr>
</table>
</form>
</div>
<div class="boxbottom"></div>

<div class="boxtop"></div>
<div class="box"><a href="{$domain_url}">{$LANG_Domain_Menu}</a></div>
<div class="boxbottom"></div>
