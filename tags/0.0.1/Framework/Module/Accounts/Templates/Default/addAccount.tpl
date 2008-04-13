<div class="boxtop"></div>
<div class="box">{t}Add Account to domain{/t} {$domain}</div>
<div class="boxbottom"></div>

<div class="boxtop"></div>
<div class="box">
<p>{$form.requirednote}</p>
<p>{$form.javascript}</p>

<form {$form.attributes}>
<table border="0">

    <tr>
        <td align="right" valign="top"><font color="red">*</font><span class="label">{$form.elements.0.label}</span></td>
        <td valign="top" align="left">{$form.elements.0.html}{$form.elements.0.error}</td>
    </tr>
    <tr>
        <td align="right" valign="top"><font color="red">*</font><span class="label">{$form.elements.1.label}</span></td>
        <td valign="top" align="left">{$form.elements.1.html}{$form.elements.1.error}</td>
    </tr>
    <tr>
        <td align="right" valign="top"><font color="red">*</font><span class="label">{$form.elements.2.label}</span></td>
        <td valign="top" align="left">{$form.elements.2.html}{$form.elements.2.error}</td>
    </tr>
    <tr>
        <td></td><td align="center">{$form.elements.3.html}</td>
    </tr>
</table>
</form>
</div>
<div class="boxbottom"></div>

<div class="boxtop"></div>
<div class="box"><a href="{$domain_url}">{t}Domain Menu{/t}</a></div>
<div class="boxbottom"></div>
