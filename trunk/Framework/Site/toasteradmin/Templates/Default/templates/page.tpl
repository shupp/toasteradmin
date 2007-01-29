<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<title>Toaster Admin</title>
<link href="styles.css" rel="stylesheet" type="text/css" />
</head>

<body>
<!-- let me preface this by saying that my css-based layout skills still suck, but that this validates anyway.  I will go on roecord here and say that tables are still a lot easier for me to deal with.  Now excuse me while I get a glass of wine. -->
<div id="wrapper">
	<div id="container">
		<div id="head"></div>
		<div id="contentwrapper">
        {if $logged_in_as}
        <div id="boxnobg">
        <center>
        <table width="514" border="0">
            <tr>
                <td align="left">
            {$LANG_logged_in_as} {$logged_in_as}
                </td>
                <td align="right">
            <a href='./?module=Logout'>{$LANG_logout}</a>
                </td>
            </tr>
        </table>
        </center>
        </div>
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
