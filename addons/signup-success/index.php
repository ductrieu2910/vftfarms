<!-- page-header-section start -->
<div class="page-header-section">
    <div class="container">
        <div class="row">
            <div class="col-12 d-flex justify-content-between justify-content-md-end">
                <ul class="breadcrumb">
                    <li><a href="<?php echo URL; ?>"><?php echo $appSession->getLang()->find("Home");?></a></li>
                    <li><span>/</span></li>
                    <li><?php echo $appSession->getLang()->find("Sign Up");?></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- page-header-section end -->
<!-- Product order success section start -->
<section class="product-order-success-section section-ptb">
    <div class="container">
        <div class="product-order-success-container">
            <div class="product-order-success">
                <div class="iconimage">
                    <img src="<?php echo URL;?>assets/images/checkicon.png" alt="icon">
                </div>
                <h2>Cảm ơn bạn đã đăng ký thành viên của VFSC!</h2>
                <p>Bạn hãy đăng nhập để tiến hành lựa chọn những sản phẩm yêu thích.</p>
				Nhập mã OTP để kích hoạt tài khoản:
				<table width="100%" border="0">
					<tr>
						<td style="text-align:center" align="center">
						<input type="text" id="editotp" name="editotp"  style="width:150px"><br><br>
                <a href="javascript:activeAccount()" class="order-btn">KÍCH HOẠT</a>
				</td>
					</tr>
				</table>
				
            </div>

        </div>
    </div>
</section>
<script>
function activeAccount()
{
	var ctr = document.getElementById('editotp');
	if(ctr.value == "")
	{
		alert("Nhập mã OTP để kích hoạt tài khoản");
		ctr.focus();
		return;
	}
	var _url =  "<?php echo URL;?>api/account/?ac=active";
	_url = _url + "&code=" + encodeURIComponent(ctr.value);
	

	loadPage('contentView', _url, function(status, message) {
		if (status == 0) {
			if (message.indexOf("OK") != -1) {
				alert("Tài khoản được kích hoạt");
				document.location.href = '<?php echo URL; ?>signup';
			} else if (message.indexOf("INVALID") != -1) {
				alert("Mã không hợp lệ");
			}
		}
	}, true);
	
}
</script>
<!-- Product order success section end -->