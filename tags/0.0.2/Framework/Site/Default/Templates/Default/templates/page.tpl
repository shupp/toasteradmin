<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>{t}ToasterAdmin{/t}</title>
<link href="styles.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div id="wrapper">
	<div id="container">
		<div id="head"></div>
		<div id="contentwrapper">
        {if $smarty.session.email}
        <center>
        <table width="514" border="0">
            <tr>
                <td align="left">
            {t}logged in as{/t} {$smarty.session.email}
                </td>
                <td align="right">
            <a href='./?module=Login&event=logoutNow'>{t}logout{/t}</a>
                </td>
            </tr>
        </table>
        </center>
        {/if}
		<div align="center">
        {if $message}{$message}{/if}
        {include file="$modulePath/$tplFile"}
		</div>
		</div>
		<div id="bottom"></div>	
		<div>
		  <p align="center"></p>
		</div>
	</div>
</div>
</body>
</html>
