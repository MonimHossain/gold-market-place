<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
	
	<title>Vault Create Invoice</title>
	
	<link rel='stylesheet' type='text/css' href='css/pdf-style.css' />
	<link rel='stylesheet' type='text/css' href='css/print.css' media="print" />
	<script type='text/javascript' src='js/jquery-1.3.2.min.js'></script>
	<script type='text/javascript' src='js/pdf-example.js'></script>

</head>

<body>

	<div id="page-wrap">

		<textarea id="header">INVOICE</textarea>
		
		<div id="identity">
		
            <textarea id="address"> 
                Chris Coyier
                123 Appleseed Street
                Appleville, WI 53719

                Phone: (555) 555-5555
            </textarea>
		
		</div>
		
		<div style="clear:both"></div>
		
		<div id="customer">
		
		</div>
		
		<table id="items">
		
		  <tr>
		      <th>Title</th>
		      <th>Type</th>
		      <th>Quantity</th>
		  </tr>
		  
		  <tr class="item-row">
		      <td class="item-name"></td>
		      <td class="description"></td>
		      <td><span class="qty"></span></td>
		  </tr>
		  
		  
		  <tr>

          <td colspan="2" class="blank"> </td>
		      <td colspan="2" class="total-line"></td>
		      <td class="total-value"><div id="total"></div></td>
		  </tr>
		
		</table>
		
		
	
	</div>
	
</body>

</html>