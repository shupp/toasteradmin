<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<title>Toaster Admin, v .01</title>
<link href="styles.css" rel="stylesheet" type="text/css" />
</head>

<body>
<!-- let me preface this by saying that my css-based layout skills still suck, but that this validates anyway.  I will go on roecord here and say that tables are still a lot easier for me to deal with.  Now excuse me while I get a glass of wine. -->
<div id="wrapper">
	<div id="container">
		<div id="head"></div>
		<div id="contentwrapper">
		<div align="center">
			<!-- box 1 -->
			<div class="boxtop"></div>
			<div class="box">box content</div>
			<div class="boxbottom"></div>
			<!-- eof box 1 -->
			<!-- box 2 -->
			<div class="boxtopDomains">
				<div class="boxtopDomainscontent">
				  <h1>Existing Domains: Page List </h1>
				</div>
			</div>
			<div class="box">
				<div class="clear"></div>
				<!-- using a table here because it seems to make sense to do so, since this is for data -->
				<table border="0" cellpadding="0" cellspacing="0" id="datatable">
		  			<tr>
    					<td class="domaincell">domain name </td>
    					<td class="editcell">edit domain | delete domain </td>
  					</tr>
 					<tr>
    					<td colspan="2" class="dividercell"></td>
    				</tr>
				</table>
				<!-- end of the table, which was also used partially because it's late and I'm tired -->
				<div class="clear"></div>
			</div>
			<div class="boxbottom"></div>
			<!-- eof box 2 -->
			</div>
		</div>
		<div id="bottom"></div>	
		<div>
		  <p align="center">signatures go here </p>
		</div>
	</div>
</div>
</body>
</html>
