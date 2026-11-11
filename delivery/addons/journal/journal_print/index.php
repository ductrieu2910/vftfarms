<?php
validUser($appSession);



$msg = $appSession->getTier()->createMessage();

$company_currency_id = $appSession->getConfig()->getProperty("currency_id");

$journal_id = '';
if(isset($_REQUEST['journal_id']))
{
	$journal_id = $_REQUEST['journal_id'];
}
$sql = "SELECT d1.id, d1.receipt_no, d1.receipt_date, d2.code AS partner_code, d2.name AS partner_name, d2.vat, d2.address, d1.rel_id FROM account_journal d1 LEFT OUTER JOIN res_partner d2 ON(d1.partner_id = d2.id) WHERE d1.id='".$journal_id."'";


$msg->add("query", $sql);
$dt_sale = $appSession->getTier()->getTable($msg);

if($dt_sale->getRowCount()>0)
{
	$receipt_no = $dt_sale->getString(0, "receipt_no");
	$partner_code = $dt_sale->getString(0, "partner_code");
	$partner_name = $dt_sale->getString(0, "partner_name");
	$vat = $dt_sale->getString(0, "vat");
	$address = $dt_sale->getString(0, "address");
	$order_no = $dt_sale->getString(0, "order_no");
	$receipt_date = $dt_sale->getString(0, "receipt_date");
	$sale_id = $dt_sale->getString(0, "rel_id");
	$sql = "SELECT DISTINCT d2.name FROM account_payment_line d1 LEFT OUTER JOIN account_payment d2 ON(d1.payment_id = d2.id) WHERE d1.status =0 AND (d1.line_id ='".$sale_id."' OR d1.rel_id='".$sale_id."')";
	$msg->add("query", $sql);
	$dt_payment = $appSession->getTier()->getTable($msg);
	$payment_name = "";
	for($j=0; $j<$dt_payment->getRowCount(); $j++)
	{
		if($payment_name != "")
		{
			$payment_name = $payment_name.", ";
		}
		$payment_name = $payment_name.$dt_payment->getString($j, "name");
	}

	$dd = "";
	$mm = "";
	$yy = "";
	if($receipt_date != "")
	{
		$dd =  date("d", strtotime($receipt_date));
		$mm =  date("m", strtotime($receipt_date));
		$yy =  date("Y", strtotime($receipt_date));
		
		$receipt_date = date("d/m/Y", strtotime($receipt_date));
		
		
	}
	

$sql = "SELECT d1.id, d1.product_id, d2.code AS product_code, d2.name AS product_name, d3.name AS unit_name, d1.quantity, d1.unit_price";
$sql = $sql." FROM account_journal_line d1";
$sql = $sql." LEFT OUTER JOIN product d2 ON(d1.product_id = d2.id)";
$sql = $sql." LEFT OUTER JOIN product_unit d3 ON(d1.unit_id = d3.id)";
$sql = $sql." WHERE d1.status =0 AND d1.journal_id='".$journal_id."' ORDER BY d1.sequence ASC, d1.create_date ASC";
$msg->add("query", $sql);

$dt_product = $appSession->getTier()->getTable($msg);				  



?>
<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style type="text/css">
<!--
body,td,th {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 10pt;
}
-->
</style><body margin="0">
<div style="background-image: url(<?php echo URL;?>assets/images/invoice_bg.gif); background-repeat: no-repeat; background-size: 100% 100%; width:725; height:1088; padding-left:40; padding-right:30">
  <table width="100%" height="64" border = "0">
	<tr>
	<td><table width="100%" border="0">
      <tr>
        <td height="28">&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td width="29%" height="78">&nbsp;</td>
        <td width="43%" align="center"><p style="line-height:140%"><span style="font-size:12pt">H&Oacute;A D&#416;N GI&Aacute; TR&#7882; GIA T&#258;NG</span><br>
		  <em>(Bản thể hiện của hóa đơn điện tử)</em><br>
		Ngày <?php echo $dd;?> tháng <?php echo $mm;?> năm <?php echo $yy;?></p> </td>
        <td width="28%" align="left" valign="top"><p style="line-height:140%; padding-top:10">Ký hiệu: 1C23TVT<br>Số: <?php echo $receipt_no;?> </p></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td>Mã CQT:</td>
        <td>&nbsp;</td>
      </tr>
    </table></td>
	</tr>
	<tr>
	<td>&nbsp;</td>
	</tr>
	<tr>
	  <td ><table width="100%" border="0">
        <tr>
          <td width="16%" align="left" valign="top">Đơn vị bán hàng </td>
          <td width="2%" align="left" valign="top">:</td>
          <td width="78%" align="left" valign="top"> <strong>CÔNG TY CỔ PHẦN VIFOTEC</strong></td>
        </tr>
        <tr>
          <td align="left" valign="top">Mã số thuế</td>
          <td align="left" valign="top">:</td>
          <td align="left" valign="top"> <strong>0109385090</strong></td>
        </tr>
        <tr>
          <td align="left" valign="top">Địa chỉ </td>
          <td align="left" valign="top">:</td>
          <td align="left" valign="top"> Tầng 1, số 130 Nguyễn Đức Cảnh, Phường Tương Mai, Quận Hoàng Mai, Thành phố<br>
            Hà Nội, Việt Nam</td>
        </tr>
        <tr>
          <td align="left" valign="top">Điện thoại</td>
          <td align="left" valign="top">:</td>
          <td align="left" valign="top">&nbsp;</td>
        </tr>
        <tr>
          <td align="left" valign="top">Số tài khoản</td>
          <td align="left" valign="top">:</td>
          <td align="left" valign="top"> 3666699999 - Ngân hàng TMCP Ngoại Thương Việt Nam</td>
        </tr>
      </table></td>
    </tr>
	<tr>
	  <td>&nbsp;</td>
    </tr>
	<tr>
	  <td ><table width="100%" border="0">
        <tr>
          <td colspan="3">Họ tên người mua hàng: <?php echo $partner_name;?></td>
          </tr>
        <tr>
          <td colspan="3">Tên đơn vị: <?php echo $partner_name;?></td>
          </tr>
        <tr>
          <td colspan="3">Mã số thuế: <?php echo $vat;?></td>
          </tr>
        <tr>
          <td colspan="3">Địa chỉ: <?php echo $address;?></td>
          </tr>
        <tr>
          <td width="47%">Hình thức thanh toán: <?php echo $payment_name;?></td>
          <td width="49%" colspan="2">Số tài khoản: </td>
          </tr>
      </table></td>
    </tr>
	<tr>
	  <td align="left" valign="top" style="height:590;"><table width="100%" border="0" cellpadding="2" cellspacing="0">
        <tr>
          <td width="30" align="center" style="border-top:solid;  border-left:solid; border-width:0.5px;"><strong>STT</strong></td>
          <td  align="center" style="border-top:solid;  border-left:solid; border-width:0.5px;"><strong>Tên hàng hóa, dịch vụ </strong></td>
          <td width="70" align="center" style="border-top:solid;  border-left:solid; border-width:0.5px;"><strong>Đơn vị tính </strong></td>
          <td width="80" align="center" style="border-top:solid;  border-left:solid; border-width:0.5px;"><strong>Số lượng </strong></td>
          <td width="100" align="center" style="border-top:solid;  border-left:solid; border-width:0.5px;"><strong>Đơn giá </strong></td>
          <td width="100" align="center" style="border-top:solid;  border-left:solid; border-right:solid; border-width:0.5px;"><strong>Thành tiền </strong></td>
        </tr>
        <tr>
          <td align="center" style="border-top:solid;  border-left:solid; border-bottom:solid; border-width:0.5px;"><strong>A</strong></td>
          <td align="center" style="border-top:solid;  border-left:solid; border-bottom:solid;border-width:0.5px;"><strong>B</strong></td>
          <td align="center" style="border-top:solid;  border-left:solid; border-bottom:solid;border-width:0.5px;"><strong>C</strong></td>
          <td align="center" style="border-top:solid;  border-left:solid; border-bottom:solid;border-width:0.5px;"><strong>1</strong></td>
          <td align="center" style="border-top:solid;  border-left:solid; border-bottom:solid;border-width:0.5px;"><strong>2 </strong></td>
          <td align="center" style="border-top:solid;  border-left:solid; border-bottom:solid; border-right:solid; border-width:0.5px;"><strong>3 = 1 x 2 </strong></td>
        </tr>
		<?php
		$no = 0;
		$total = 0;
		for($j =0; $j<$dt_product->getRowCount(); $j++)
		{
			$product_id = $dt_product->getString($j, "product_id");
			if($product_id == "")
			{
				continue;
			}
			$no = $no + 1;
			$product_code = $dt_product->getString($j, "product_code");
			$product_name = $dt_product->getString($j, "product_name");
			$unit_name = $dt_product->getString($j, "unit_name");
			$currency_id = $dt_product->getString($j, "currency_id");
			$quantity = $dt_product->getFloat($j, "quantity");
			$unit_price = $dt_product->getFloat($j, "unit_price");
			$amount =$quantity * $unit_price;
			$total = $total + $amount;
			$line_style = "dotted";
			if($j == $dt_product->getRowCount()-1)
			{
				$line_style = "solid";
			}
			
		?>
        <tr>
          <td align="center" style="border-bottom:<?php echo $line_style;?>; border-left:solid; border-width:0.5px; vertical-align:middle"><?php echo $no;?></td>
          <td style="border-bottom:<?php echo $line_style;?>; border-left:solid; border-width:0.5px; vertical-align:middle"><?php echo $product_name;?></td>
          <td align="center" style="border-bottom:<?php echo $line_style;?>; border-left:solid; border-width:0.5px; vertical-align:middle"><?php echo $unit_name;?></td>
          <td align="right" style="border-bottom:<?php echo $line_style;?>; border-left:solid; border-width:0.5px; vertical-align:middle"><?php echo $appSession->getFormats()->getDOUBLE()->format($quantity);?></td>
          <td align="right" style="border-bottom:<?php echo $line_style;?>; border-left:solid; border-width:0.5px; vertical-align:middle"><?php echo $appSession->getCurrency()->formatWithoutSymbol($company_currency_id, $unit_price);?></td>
          <td align="right" style="border-bottom:<?php echo $line_style;?>; border-left:solid; border-right:solid; border-width:0.5px; vertical-align:middle"><?php echo $appSession->getCurrency()->format($company_currency_id, $amount);?></td>
        </tr>
		<?php
		}
		$vat_percent = 0.05;
		$vat_amount = $total * $vat_percent;
		$total_total = $total + $vat_amount;
		?>
        <tr>
          <td colspan="2" align="center" style="border-top:solid;  border-left:solid; border-width:0.5px;">&nbsp;</td>
          <td colspan="3" align="left" style="border-top:solid;  border-width:0.5px;">Cộng tiền hàng: </td>
          <td align="right" style="border-top:solid;  border-left:solid; border-right:solid; border-width:0.5px;"><?php echo $appSession->getCurrency()->formatWithoutSymbol($company_currency_id, $total);?></td>
        </tr>
        <tr>
          <td colspan="2" style="border-top:solid;  border-left:solid; border-width:0.5px;">Thuế suất GTGT: <?php echo $appSession->getFormats()->getPERCENT()->format($vat_percent);?></td>
          <td colspan="3" style="border-top:solid; border-width:0.5px;">Tiền thuế GTGT:</td>
          <td align="right" style="border-top:solid;  border-left:solid; border-right:solid; border-width:0.5px;"><?php echo $appSession->getCurrency()->formatWithoutSymbol($company_currency_id, $vat_amount);?></td>
        </tr>
        <tr>
          <td style="border-top:solid;  border-left:solid; border-width:0.5px;">&nbsp;</td>
          <td style="border-top:solid;   border-width:0.5px;">&nbsp;</td>
          <td colspan="3" style="border-top:solid;  border-width:0.5px;">Tổng tiền thanh toán:</td>
          <td align="right" style="border-top:solid;  border-left:solid; border-right:solid; border-width:0.5px;"><?php echo $appSession->getCurrency()->formatWithoutSymbol($company_currency_id, $total_total);?></td>
        </tr>
        <tr>
          <td colspan="6" style="border-top:solid;  border-left:solid; border-right:solid; border-bottom:solid; border-width:0.5px;">Số tiền viết bằng chữ: <i><?php echo $appSession->getCurrency()->toword($company_currency_id, $total_total);?></i></td>
          </tr>
        
      </table>
	  <br>
	  <table width="100%" border="0">
          <tr>
            <td align="center"><strong>Người mua hàng</strong><br>
            (Ký, ghi rõ họ, tên)</td>
            <td align="center"><strong>Người bán hàng</strong><br>
            (Ký, ghi rõ họ, tên)</td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td><p>&nbsp;</p>
            <p>&nbsp;</p></td>
          </tr>
        </table>
	  </td>
    </tr>
	
	<tr>
	  <td align="center"><em>(Cần kiểm tra, đối chiếu trước khi lập, giao, nhận hóa đơn)</em></td>
    </tr>
	<tr>
	  <td ><em>Mã tra cứu hóa đơn:<br>
      Tra cứu tại Website: <a href="https://www.meinvoice.vn/tra-cuu/">https://www.meinvoice.vn/tra-cuu/</a></em> </td>
    </tr>
  </table>
</div>
</body>
</html>
<?php
}
?>
    
