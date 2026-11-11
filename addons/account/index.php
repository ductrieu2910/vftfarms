<?php
require_once(ABSPATH.'api/Sale.php' );

$type = "order";
$user_id = $appSession->getUserInfo()->getId();
$company_currency_id = $appSession->getConfig()->getProperty("currency_id");

for($i =0; $i<count($arr); $i++)
{
	$index = $appSession->getTool()->indexOf($arr[$i], '=');
	if($index != -1)
	{
		
		$k = substr($arr[$i], 0, $index);
		$v = substr($arr[$i], $index + 1);
		
		if($k == "type")
		{
			$type = $v;
			
		}
	}
}
if($appSession->getUserInfo()->getId() == "")
{
	$type = "order";
}


$banklist = [];
if($type== "wallet"){

}

  
$msg = $appSession->getTier()->createMessage();



?>
<!-- page-header-section start -->
<div class="page-header-section">
    <div class="container">
        <div class="row">
            <div class="col-12 d-flex justify-content-between justify-content-md-end">
                <ul class="breadcrumb">
                    <li><a href="<?php echo URL; ?>"><?php echo $appSession->getLang()->find("Home");?></a></li>
                    <li><span>/</span></li>
                    <li><?php echo $appSession->getLang()->find("Check Out");?></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- dashboard-section start -->
<section id="dashboard-nav" class="dashboard-section">
	<div class="container">
		<ul class="dashbord-nav d-lg-flex flex-wrap align-items-center justify-content-between">
			
			<?php
			if($appSession->getUserInfo()->getId() != "")
			{
			?>
			<li><a <?php if($type== "profile"){?> class="profile" <?php }?> href="<?php echo URL;?>account/?type=profile"><i class="far fa-list-alt"></i><?php echo $appSession->getLang()->find("Profile");?></a></li>
			<?php
			}
			?>
			<li><a <?php if($type== "order"){?> class="active" <?php }?> href="<?php echo URL;?>account/?type=order"><i class="fas fa-shipping-fast"></i><?php echo $appSession->getLang()->find("Orders");?></a></li>
			<?php
			if($appSession->getUserInfo()->getId() != "")
			{
			?>
			<li><a <?php if($type== "wallet"){?> class="active" <?php }?> href="<?php echo URL;?>account/?type=wallet"><i class="far fa-list-alt"></i><?php echo $appSession->getLang()->find("Wallet");?></a></li>
			<li><a <?php if($type== "loyalty"){?> class="active" <?php }?> href="<?php echo URL;?>account/?type=loyalty"><i class="far fa-list-alt"></i><?php echo $appSession->getLang()->find("Loyalty");?></a></li>
			<li><a <?php if($type== "voucher"){?> class="active" <?php }?> href="<?php echo URL;?>account/?type=voucher"><i class="far fa-list-alt"></i><?php echo $appSession->getLang()->find("Voucher");?></a></li>
			<?php
			}
			?>
			
		</ul>
	</div>

	<div class="container">
		<?php
		if($type == "profile")
		{
			$customer_id = $appSession->getConfig()->getProperty("customer_id");
			$sql = "SELECT code, name, barcode FROM customer WHERE id='".$customer_id."'";
			$msg->add("query", $sql);
			
			$dt = $appSession->getTier()->getTable($msg);
			if($dt->getRowCount()>0){
			$customer_code = $dt->getString(0, "code");
			$customer_name = $dt->getString(0, "name");
			$customer_barcode = $dt->getString(0, "barcode");
		?>
		<div class="track-order-item bg-color-white">
		
			<div class="d-flex justify-content-between track-number-link align-items-center">
				<div>
					<h6 class="order-number"><?php echo $appSession->getLang()->find("Code");?>: <?php echo $customer_code;?></h6>
					<h6 class="order-number"><?php echo $appSession->getLang()->find("Name");?>: <?php echo $customer_name;?></h6>
					<h6 class="order-number"><?php echo $appSession->getLang()->find("Barcode");?>: <?php echo $customer_barcode;?></h6>
					
				</div>
			</div>
			<div class="order-details">
				<div class="order-details-container d-none d-md-block" style="padding:40px; text-align:center">
					<div style="padding:40px;border-style: dotted;">
					<img src = "<?php echo URL;?>api/account/?ac=barcode&customer_id=<?php echo $customer_id;?>">
					</div>
				</div>
			</div>
			
			<div class="order-details">
				<div class="order-details-container d-none d-md-block" style="padding:40px; text-align:center">
					<div style="padding:40px;border-style: dotted;">
					<img src = "<?php echo URL;?>api/account/?ac=barcode&customer_id=<?php echo $customer_id;?>&type=qrcode">
					</div>
				</div>
			</div>
		</div>
		<?php
			}
		}
		else if($type == "order")
		{
			$sql = "SELECT d1.id, d1.order_no, d1.order_date, (SELECT COUNT(id) FROM sale_product_local WHERE status =0 AND sale_id=d1.id) AS items, (SELECT SUM(quantity * unit_price) FROM sale_product_local WHERE status =0 AND sale_id=d1.id) AS amount, (SELECT name FROM res_status_line WHERE rel_id=d1.id AND status =0 ORDER BY create_date DESC LIMIT 1) AS status_name, d1.status, d2.commercial_name FROM sale_local d1 LEFT OUTER JOIN res_company d2 ON(d1.company_id = d2.id) WHERE (d1.status=0 OR d1.status=3)";
			if($user_id != "")
			{
				$sql = $sql." AND (d1.session_id='".$session_id."' OR d1.create_uid='".$user_id."')";
			}else{
				$sql = $sql." AND d1.session_id='".$session_id."'";
			}
			$sql = $sql." ORDER BY d1.order_date DESC";
			$sql = $sql." LIMIT 10";
			
			$msg->add("query", $sql);
			
			$dt = $appSession->getTier()->getTable($msg);
	
		?>
		<?php
		for($i =0; $i<$dt->getRowCount(); $i++)
		{
			$sale_id = $dt->getString($i, "id");
			$order_no = $dt->getString($i, "order_no");
			$order_date = $dt->getString($i, "order_date");
			$status_name = $dt->getString($i, "status_name");
			$total = $appSession->getTool()->toDouble($dt->getString($i, "amount"));
			$sale = new Sale($appSession);
			$dt_product = $sale->productListSaleId($sale_id);
		?>
		<div class="track-order-item bg-color-white">
		
			<div class="d-flex justify-content-between track-number-link align-items-center">
				<div>
					<h6 class="order-number">#<?php echo $order_no;?> - <?php echo $status_name;?></h6>
					<p class="date"><?php echo $order_date;?></p>
					<p class="price"><?php echo $appSession->getCurrency()->format($company_currency_id, $total);?></p>
				</div>
				<div>
					<a href="javascript:openPopup('<?php echo URL;?>addons/order_detail/?id=<?php echo $sale_id;?>')" class="order-btn"><?php echo $appSession->getLang()->find("Order Details");?></a>
				</div>
			</div>
			<div class="order-details">
				<div class="order-details-head">
					<h6><?php echo $appSession->getLang()->find("Order Details");?></h6>
				</div>
				<div class="order-details-container d-none d-md-block">
					<?php
					for($j =0; $j<$dt_product->getRowCount(); $j++)
					{
						$name = $dt_product->getString($j, "name_lg");
						if($name == "")
						{
							$name = $dt_product->getString($j, "name");
						}
						$document_id = $dt_product->getString($j, "document_id");
						$currency_id = $dt_product->getString($j, "currency_id");
						$unit_name = $dt_product->getString($j, "unit_name");
						$quantity = $appSession->getTool()->toDouble($dt_product->getString($j, "quantity"));
						$unit_price = $appSession->getTool()->toDouble($dt_product->getString($j, "unit_price"));
						$amount = $quantity * $unit_price;
						
					?>
					<div class="order-details-item d-sm-flex flex-wrap text-center text-sm-left align-items-center justify-content-between">
						<div class="thumb d-sm-flex flex-wrap align-items-center">
							<img src="<?php echo IMAGE_URL; ?>document/?id=<?php echo $document_id; ?>&w=270&h=230"
                                        alt="<?php echo $name;?>">
							<div class="product-content">
								<?php echo $name;?>
							</div>
						</div>
						
						<div class="product-content pl-0">
							<div class="product-cart-info">
								<?php echo $quantity;?> x <?php echo $appSession->getCurrency()->format($currency_id, $unit_price);?> <?php echo $unit_name;?>
							</div>
						</div>
						<div class="product-content pl-0">
							<div class="product-price">
								<span class="ml-4"><?php echo $appSession->getCurrency()->format($currency_id, $amount);?></span>
							</div>
						</div>
					</div>
					<?php
					}
					?>

				</div>
			</div>
			<div class="track-order-info">
				<ul class="to-list">
				
					<li class="inc-vat d-flex flex-wrap justify-content-between">
						<span class="t-title"><?php echo $appSession->getLang()->find("Total");?></span>
						<span class="desc"><?php echo $appSession->getCurrency()->format($company_currency_id, $total);?></span>
					</li>
				</ul>
			</div>
		</div>
		<?php
		}
		?>
		<?php
		
		}else if($type == "wallet")
		{
			$total = 0;
			$customer_id = $appSession->getConfig()->getProperty("customer_id");
			$sql = "SELECT d2.id, d2.name, d2.sequence, SUM(d1.amount * d1.factor) AS amount FROM wallet d1 LEFT OUTER JOIN wallet_category d2 ON(d1.category_id = d2.id) WHERE d1.status =0 AND d1.customer_id='".$customer_id."'";
			$sql = $sql." GROUP BY d2.id, d2.name, d2.sequence";
			$sql = $sql." ORDER BY d2.sequence ASC";
			
			$msg->add("query", $sql);
			$dt = $appSession->getTier()->getTable($msg);
			for($i =0; $i<$dt->getRowCount(); $i++)
			{
				$total = $total + $appSession->getTool()->toDouble($dt->getString($i, "amount"));
			}
			$holder_id = "";
			$sql = "select id from wallet_holder where rel_id ='".$customer_id."'";
			$msg->add("query", $sql);
			$dt_holder = $appSession->getTier()->getTable($msg);
			if($dt_holder->getRowCount()>0)
			{
				$holder_id = $dt_holder->getString(0, "id");
			}
	
		?>
		<div class="track-order-item bg-color-white">
		
			<div class="d-flex justify-content-between track-number-link align-items-center">
				<div>
					<h4 class="order-number"><?php echo $appSession->getLang()->find("Balance Wallet");?>: <?php echo $appSession->getCurrency()->format($company_currency_id, $total);?></h4>
				</div>
			
			</div>
			<div class="order-details">
				<div class="order-details-container d-none d-md-block">
					<table class="table">
						<?php
						for($i =0; $i<$dt->getRowCount(); $i++)
						{
							
							$name = $dt->getString($i, "name");
							$amount = $appSession->getTool()->toDouble($dt->getString($i, "amount"));
						?>
						<tr>
							<td><?php echo $name;?></td>
							<td width="120" align="right"> <?php echo $appSession->getCurrency()->format($company_currency_id, $amount);?></td>
						</tr>
						<?php
						}
						?>
					</table>
				</div>
			</div>
		</div>
		
		<section class="login-section section-ptb">
			
			<div class="container">
				<div class="row align-items-center">
					<div class="col-lg-12">
						<div class="eflux-login-form-area">
							<h4 class="order-number"><?php echo $appSession->getLang()->find("Deposit");?></h4>
							<form action="#" class="eflux-login-form" id="frmCreateOrder" method="post">
								<div class="row">
									<div class="col-lg-12">
										<div class="input-item">
											<label><?php echo $appSession->getLang()->find("Bank name");?></label>
											
												<select name="bankcode" id="bankcode" class="form-control">      
												<option value="VNPAYQR">VNPAYQR</option>
												<option value="VNBANK">LOCAL BANK</option>
												<option value="IB">INTERNET BANKING</option>
												<option value="ATM">ATM CARD</option>
												<option value="INTCARD">INTERNATIONAL CARD</option>
												<option value="VISA">VISA</option>
												<option value="MASTERCARD"> MASTERCARD</option>
												<option value="JCB">JCB</option>
												<option value="UPI">UPI</option>
												<option value="VIB">VIB</option>
												 <option value="VIETCAPITALBANK">VIETCAPITALBANK</option>
												<option value="SCB">Ngan hang SCB</option>
												<option value="NCB">Ngan hang NCB</option>
												<option value="SACOMBANK">Ngan hang SacomBank  </option>
												<option value="EXIMBANK">Ngan hang EximBank </option>
												<option value="MSBANK">Ngan hang MSBANK </option>
												<option value="NAMABANK">Ngan hang NamABank </option>
												<option value="VNMART"> Vi dien tu VnMart</option>
												<option value="VIETINBANK">Ngan hang Vietinbank  </option>
												<option value="VIETCOMBANK">Ngan hang VCB </option>
												<option value="HDBANK">Ngan hang HDBank</option>
												<option value="DONGABANK">Ngan hang Dong A</option>
												<option value="TPBANK">Ngân hàng TPBank </option>
												<option value="OJB">Ngân hàng OceanBank</option>
												<option value="BIDV">Ngân hàng BIDV </option>
												<option value="TECHCOMBANK">Ngân hàng Techcombank </option>
												<option value="VPBANK">Ngan hang VPBank </option>
												<option value="AGRIBANK">Ngan hang Agribank </option>
												<option value="MBBANK">Ngan hang MBBank </option>
												<option value="ACB">Ngan hang ACB </option>
												<option value="OCB">Ngan hang OCB </option>
												<option value="IVB">Ngan hang IVB </option>
												<option value="SHB">Ngan hang SHB </option>
											  </select>
										</div>
									</div>
								</div>
								<div class="row">
									

									<div class="col-lg-12">
										<div class="input-item">
											<label><?php echo $appSession->getLang()->find("Deposit Amount");?></label>
											<input type="text" id="Amount" name="Amount" class="form-control"/>
										</div>
									</div>
								</div>
								<div class="row">

									<div class="col-lg-12">
										<div class="input-item">
											<label><?php echo $appSession->getLang()->find("Deposit Description");?></label>
											<textarea id="OrderDescription"  name="OrderDescription" class="form-control" maxlength="200">NAP TIEN VAO VI</textarea>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-lg-12">
									<button type="button" class="submit" onclick="addDeposit()"><?php echo $appSession->getLang()->find("Deposit");?></button>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</section>
		<script>
			<?php
			$wallet_id = "WEB_WALLET_".$appSession->getTool()->getId()."_".$appSession->getConfig()->getProperty("customer_id")."_".$holder_id;
			?>
			function addDeposit()
			{
				var ctr = document.getElementById('bankcode');
				var bankcode = ctr.value;
				
				var ctr = document.getElementById('Amount');
				if(ctr.value == '')
				{
					alert('<?php echo $appSession->getLang()->find("Please enter amount");?>');
					ctr.focus();
					return;
				}
				var amount = ctr.value;
				var ctr = document.getElementById('OrderDescription');
				var description = ctr.value;
				if(description == '')
				{
					alert('<?php echo $appSession->getLang()->find("Please enter description");?>');
					ctr.focus();
					return;
				}
				
				var params = "ac=wallet_add";
				params = params + "&user_id=<?php echo $appSession->getConfig()->getProperty("user_id");?>";
			    params = params + "&holder_id=<?php echo $holder_id;?>";
				params = params + "&customer_id=<?php echo $customer_id;?>";
				params = params + "&session_id=<?php echo $appSession->getConfig()->getProperty("session_id");?>";
				params = params + "&amount=" + amount;
				params = params + "&description=" + description + " DEPOSIT";
				_url ='<?php echo URL;?>api/account/?' + params
				loadPage('contentView', _url, function(status, message) 
				{
					if (status == 0) {
						if(message.length == 36)
						{
							var return_action ='APP_ORDER_' + message;
							var postData = "order_id=" + return_action + "&order_desc="+ description;
							postData = postData + "&order_type=billpayment";
							postData = postData + "&amount=" + amount;
							postData = postData + "&language=vn";
							postData = postData + "&bank_code=" + bankcode;
							
							var _url = '<?php echo URL;?>vnpay_php/?' + postData;
							
							loadPage('contentView', _url, function(status, message) {
								if (status == 0) {
									var x = JSON.parse(message);
								
									if (x.code === '00') {   
										if (window.vnpay)
										{
											vnpay.open({ width: 768, height: 600, url: x.data });
										}
										else
										{
											
											window.location = x.data;
										}
										return false;
									} else {
										alert("Error:" + x.Message);
									}
								}
							}, true);
						}
					}
				}, true);
					
		  
			}
		</script>
		<?php
		}
		else if($type == "loyalty")
		{
			$customer_id = $appSession->getConfig()->getProperty("customer_id");
			$total = 0;
			$sql = "SELECT d2.id, d2.name, d2.sequence, SUM(d1.point * d1.factor) AS point FROM loyalty_point d1 LEFT OUTER JOIN loyalty_point_category d2 ON(d1.category_id = d2.id) WHERE d1.status =0 AND d1.customer_id='".$customer_id."'";
			$sql = $sql." GROUP BY d2.id, d2.name, d2.sequence ORDER BY d2.sequence ASC";
			
			$msg->add("query", $sql);
			$dt = $appSession->getTier()->getTable($msg);
			for($i =0; $i<$dt->getRowCount(); $i++)
			{
				$total = $total + $appSession->getTool()->toDouble($dt->getString($i, "point"));
			}
	
		?>
		<div class="track-order-item bg-color-white">
		
			<div class="d-flex justify-content-between track-number-link align-items-center">
				<div>
					<h6 class="order-number"><?php echo $appSession->getLang()->find("Points");?>: <?php echo $appSession->getFormats()->getDOUBLE()->format($total);?></h6>
					
				</div>
			</div>
			<div class="order-details">
				<div class="order-details-container d-none d-md-block">
					<table class="table">
						<?php
						for($i =0; $i<$dt->getRowCount(); $i++)
						{
							
							$name = $dt->getString($i, "name");
							$point = $appSession->getTool()->toDouble($dt->getString($i, "point"));
						?>
						<tr>
							<td><?php echo $name;?></td>
							<td width="120" align="right"> <?php echo $appSession->getFormats()->getDOUBLE()->format($point);?></td>
						</tr>
						<?php
						}
						?>
					</table>
				</div>
			</div>
		</div>
		<?php
		}else if($type == "voucher")
		{
			$customer_id = $appSession->getConfig()->getProperty("customer_id");
			$sql = "SELECT d3.name, d2.code, d3.exp_date, d3.amount, d3.currency_id, d4.code AS currency_code FROM voucher_line_rel d1 LEFT OUTER JOIN voucher_line d2 ON(d1.line_id = d2.id) LEFT OUTER JOIN voucher d3 ON(d2.voucher_id = d3.id) LEFT OUTER JOIN res_currency d4 ON(d3.currency_id = d4.id) WHERE d1.status =0 AND d2.status =0 AND d1.line_id NOT IN(select voucher_line_id FROM voucher_log WHERE status=0) AND d1.rel_id='".$customer_id."'";
			$sql = $sql." ORDER BY d1.create_date DESC";
			$msg->add("query", $sql);
			$dt = $appSession->getTier()->getTable($msg);
		?>
		<div class="track-order-item bg-color-white">
		
			<div class="order-details">
				<div class="order-details-container d-none d-md-block">
					<table class="table">
						<?php
						for($i =0; $i<$dt->getRowCount(); $i++)
						{
							
							$name = $dt->getString($i, "name");
							$code = $dt->getString($i, "code");
							$exp_date = $dt->getString($i, "exp_date");
							$currency_id = $dt->getString($i, "currency_id");
							$amount = $appSession->getTool()->toDouble($dt->getString($i, "amount"));
							
						?>
						<tr>
							<td><?php echo $name;?></td>
							<td align="center" width="70"><?php echo $code;?></td>
							<td align="center" width="120"><?php echo $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($exp_date));?></td>
							<td width="120" align="right"><?php echo $appSession->getCurrency()->format($currency_id, $amount);?></td>
						</tr>
						<?php
						}
						?>
					</table>
				</div>
			</div>
		</div>
		<?php
		}
		?>
	</div>
</section>
<!-- dashboard-section end -->