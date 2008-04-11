<div class="boxtop"></div>
<div class="box">
<b>{t}Modify Account{/t} {$account}@{$domain}</b>
</div>
<div class="boxbottom"></div>
<div class="boxtop"></div>
<div class="box">
{$form.javascript}

<form {$form.attributes}>
<div>
<table border="0">

    <tr>

        <td align="right" valign="top"><b>{$form.elements.0.label}</b></td>
        <td valign="top" align="left">{$form.elements.0.html}</td>
    </tr>
    <tr>
        <td align="right" valign="top"><b>{$form.elements.1.label}</b></td>
        <td valign="top" align="left">{$form.elements.1.html} {$form.elements.1.error}</td>
    </tr>

    <tr>
        <td align="right" valign="top"><b>{$form.elements.2.label}</b></td>
        <td valign="top" align="left">{$form.elements.2.html} {$form.elements.1.error}</td>
    </tr>
    <tr><td colspan="2"><hr /></td></tr>
    <tr>
        <td align="right" valign="top"><b>{$form.elements.3.label}</b></td>
        <td valign="top" align="left">{$form.elements.3.html}<hr /></td>

    </tr>
    <tr>
        <td align="right" valign="top"><b>{$form.elements.4.label}</b></td>
        <td valign="top" align="left">{$form.elements.4.html}<hr /></td>
    </tr>
    <tr>
        <td align="right" valign="top"><b>{$form.elements.5.label}</b></td>

        <td valign="top" align="left">{$form.elements.5.html} {$form.elements.6.html} </td>
    </tr>
    <tr>
        <td align="right" valign="top"></td>
        <td valign="top" align="left">
</td>
    </tr>
    <tr>

        <td align="right" valign="top"></td>
        <td valign="top" align="left">{$form.elements.7.html} {$form.elements.7.label} </td>
    </tr>
    <tr><td colspan="2"><hr /></td></tr>
    <tr>
        <td align="right" valign="top"><b>{$form.elements.8.label}</b></td>
        <td valign="top" align="left">{$form.elements.8.html}</td>
    </tr>
    <tr>
        <td align="right" valign="top"><b>{$form.elements.9.label}</b></td>
        <td valign="top" align="left">{$form.elements.9.html}</td>
    </tr>
    <tr>
        <td align="right" valign="top"><b>{$form.elements.10.label}</b></td>
        <td valign="top" align="left">{$form.elements.10.html}</td>

    </tr>
    <tr>
        <td align="right" valign="top"><b></b></td>
        <td valign="top" align="left">{$form.elements.11.html}</td>
    </tr>
</table>
</div>
</form>

{if $isDomainAdmin}<a href="./?module=Domains&amp;class=Menu&amp;domain={$domain}"><br /><b>{t}Main Menu{/t}</b>{/if}

</div>
<div class="boxbottom"></div>
