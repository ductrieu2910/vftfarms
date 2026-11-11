 <?php require_once(ABSPATH."vnpay_php/config.php"); ?>   


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <meta name="description" content="">
        <meta name="author" content="">
        <title>Tạo mới đơn hàng</title>
        <!-- Bootstrap core CSS -->
        <link href="/vnpay_php/assets/bootstrap.min.css" rel="stylesheet"/>
        <!-- Custom styles for this template -->
        <link href="/vnpay_php/assets/jumbotron-narrow.css" rel="stylesheet">  
        <script src="/vnpay_php/assets/jquery-1.11.3.min.js"></script>
    </head>

    <body>
                 
        <div class="container">
            <div class="header clearfix">
                <h3 class="text-muted">VNPAY DEMO</h3>
            </div>
            <h3>Tạo mới đơn hàng</h3>
            <div class="table-responsive">
                <form action="<?php echo URL;?>vnpay_php/vnpay_create_payment.php" id="create_form" method="post">       

                 
    
                    <div class="form-group">
                        <label for="amount">Số tiền</label>
                        <input class="form-control" id="amount"
                               name="amount" type="number" value="10000"/>
                    </div>
                    <div class="form-group">
                        <label for="order_desc">Nội dung thanh toán</label>
                        <textarea class="form-control" cols="20" id="order_desc" name="order_desc" rows="2">Noi dung thanh toan</textarea>
                    </div>
                    <div class="form-group">
                        <label for="bank_code">Ngân hàng</label>
                        <select name="bank_code" id="bank_code" class="form-control">
                            <option value="">Không chọn</option>
                            <option value="NCB"> Ngan hang NCB</option>
                            <option value="AGRIBANK"> Ngan hang Agribank</option>
                            <option value="SCB"> Ngan hang SCB</option>
                            <option value="SACOMBANK">Ngan hang SacomBank</option>
                            <option value="EXIMBANK"> Ngan hang EximBank</option>
                            <option value="MSBANK"> Ngan hang MSBANK</option>
                            <option value="NAMABANK"> Ngan hang NamABank</option>
                            <option value="VNMART"> Vi dien tu VnMart</option>
                            <option value="VIETINBANK">Ngan hang Vietinbank</option>
                            <option value="VIETCOMBANK"> Ngan hang VCB</option>
                            <option value="HDBANK">Ngan hang HDBank</option>
                            <option value="DONGABANK"> Ngan hang Dong A</option>
                            <option value="TPBANK"> Ngân hàng TPBank</option>
                            <option value="OJB"> Ngân hàng OceanBank</option>
                            <option value="BIDV"> Ngân hàng BIDV</option>
                            <option value="TECHCOMBANK"> Ngân hàng Techcombank</option>
                            <option value="VPBANK"> Ngan hang VPBank</option>
                            <option value="MBBANK"> Ngan hang MBBank</option>
                            <option value="ACB"> Ngan hang ACB</option>
                            <option value="OCB"> Ngan hang OCB</option>
                            <option value="IVB"> Ngan hang IVB</option>
                            <option value="VISA"> Thanh toan qua VISA/MASTER</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="language">Ngôn ngữ</label>
                        <select name="language" id="language" class="form-control">
                            <option value="vn">Tiếng Việt</option>
                            <option value="en">English</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" id="btnPopup">Thanh toán Post</button>
                    <button type="button" name="redirect" onclick="addDeposit()" id="redirect" class="btn btn-default">Thanh toán Redirect</button>

                </form>
            </div>
            <p>
                &nbsp;
            </p>
           
        </div>  
		<script>
		function addDeposit()
		{
			var ctr = document.getElementById('bank_code');
			var bankcode = ctr.value;
			
			var ctr = document.getElementById('amount');
			if(ctr.value == '')
			{
				alert('Please enter amount');
				ctr.focus();
				return;
			}
			var amount = ctr.value;
			var ctr = document.getElementById('order_desc');
			var description = ctr.value;
			var postData = "order_id=123&order_desc="+ description;
			postData = postData + "&order_type=billpayment";
			postData = postData + "&amount=" + amount;
			postData = postData + "&language=vn";
			postData = postData + "&bank_code=" + bankcode;
			postData = postData + "&redirect=1";
			
			$.ajax({
				type: "POST",
				url: "https://www.fosacha.vn/vnpay_php/vnpay_create_payment.php",
				data: postData,
				dataType: 'JSON',
				success: function (x) {
					if (x.code === '00') {   
						console.log(x);
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
			});
			
		}
		</script>
         


    </body>
</html>
