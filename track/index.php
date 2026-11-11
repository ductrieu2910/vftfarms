<?php
	$qrcode = "";
	if(isset($_REQUEST['qrcode']))
	{
		$qrcode = $_REQUEST['qrcode'];
	}
	$msg = $appSession->getTier()->createMessage();
	
	$sql = "SELECT d1.id, d2.code, d2.name, d21.name AS supplier_name, d1.mfg_date, d1.exp_date, d1.create_date, d30.name AS attribute_name, d31.name AS type_name, d20.description FROM stock_product d1 LEFT OUTER JOIN product d2 ON(d1.product_id = d2.id) LEFT OUTER JOIN stock_product d3 ON(d1.rel_id = d3.id) LEFT OUTER JOIN stock_product d4 ON(d3.rel_id = d4.id) LEFT OUTER JOIN stock_product d5 ON(d4.rel_id = d5.id)  LEFT OUTER JOIN stock_product d6 ON(d5.rel_id = d6.id) LEFT OUTER JOIN stock_product d7 ON(d6.rel_id = d7.id) LEFT OUTER JOIN purchase_receive_product_local d19 ON(d1.rel_id = d19.id OR d3.rel_id = d19.id OR d4.rel_id = d19.id OR d5.rel_id = d19.id OR d6.rel_id = d19.id OR d7.rel_id = d19.id) LEFT OUTER JOIN purchase_receive_local d20 ON(d19.receive_id= d20.id) LEFT OUTER JOIN supplier d21 ON(d20.supplier_id = d21.id) LEFT OUTER JOIN attribute d30 ON(d1.attribute_id = d30.id) LEFT OUTER JOIN product_type d31 ON(d1.type_id = d31.id) WHERE d1.barcode ='".$qrcode."'";
	
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	
	if($dt->getRowCount()>0)
	{
		$product_code = $dt->getString(0, "code");
		$product_name = $dt->getString(0, "name");
		$supplier_name = $dt->getString(0, "supplier_name");
		$attribute_name = $dt->getString(0, "attribute_name");
		$type_name = $dt->getString(0, "type_name");
		$description = $dt->getString(0, "description");
		$mfg_date = $dt->getString(0, "mfg_date");
		$exp_date = $dt->getString(0, "exp_date");
		if($mfg_date != "")
		{
			$mfg_date = $appSession->getFormats()->getDATE()->formatWith($appSession->getTool()->toDateTime($mfg_date),  "DD/MM/YYYY");
		}
		if($exp_date != "")
		{
			$exp_date = $appSession->getFormats()->getDATE()->formatWith($appSession->getTool()->toDateTime($exp_date), "DD/MM/YYYY");
		}
?>
<!DOCTYPE html>
<html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?php echo META_TITLE;?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" href="<?php echo URL;?>favicon.ico" />
<link rel="stylesheet" href="<?php echo URL;?>track/assets/css/css.css">
<script src="<?php echo URL;?>track/assets/js/jquery.min.js"></script>
<script src="<?php echo URL;?>track/assets/js/controller.js"></script>
<script src="<?php echo URL;?>track/assets/js/tool.js"></script>
<script src="<?php echo URL;?>track/assets/js/back_to_top.js"></script>
			
<style>
html,body,h1,h2,h3,h4,h5,h6 {font-family: "Roboto", sans-serif;}
.sidebar {
  z-index: 3;
  width: 250px;
  top: 42px;
  bottom: 0;
  height: inherit;
}
</style>
</head>
<body>
<div class="main">
	<div id="contentView" style="margin:10px;">
		
		<div class="row padding">
			<div class="col m12">
				Sản phẩm: <b><?php echo $product_name;?></b>
			</div>
			
		</div>
		<div class="row padding">
			<div class="col m12">
				Mã sản phẩm: <b><?php echo $product_code;?></b>
			</div>
			
		</div>
		<div class="row padding">
			<div class="col m12">
				Khối lượng: <b><?php echo $attribute_name;?></b>
			</div>
			<div class="col m6 right" style="text-align:right">
				
			</div>
		</div>
		<div class="row padding">
			<div class="col m12">
				Tiêu chuẩn chứng nhận: <b><?php echo $type_name;?><?php echo " ".$description;?></b>
			</div>
			<div class="col m6 right" style="text-align:right">
				
			</div>
		</div>
		<div class="row padding">
			<div class="col m12">
				Ngày sản xuất: <b><?php echo $mfg_date;?></b>
			</div>
			<div class="col m6 right" style="text-align:right">
				
			</div>
		</div>
		<div class="row padding">
			<div class="col m12">
				Hạn sử dụng: <b><?php echo $exp_date;?></b>
			</div>
			<div class="col m6 right" style="text-align:right">
				
			</div>
		</div>
		<div class="row padding">
			<div class="col m12">
				Nhà cung cấp: <b><?php echo $supplier_name;?></b>
			</div>
			
		</div>
		<div class="row padding">
			<div class="col m12">
				Kham khảo thông tin: <b>www.market.vfsc.vn</b>
			</div>
			
		</div>
		<div class="row padding">
			<div class="col m12" style="text-align:center">
				Mời quý khách tải ứng dụng để ưu đãi khi mua hàng: <a href="https://vftfarms.vn">https://vftfarms.vn</a>
			</div>
			
		</div>
		
		
	</div>
</div>
</body>
</html>
<?php
}
?>