<!-- page-header-section start -->
<div class="page-header-section">
    <div class="container">
        <div class="row">
            <div class="col-12 d-flex justify-content-between justify-content-md-end">
                <ul class="breadcrumb">
                    <li><a href="<?php echo URL; ?>"><?php echo $appSession->getLang()->find("Home");?></a></li>
                    <li><span>/</span></li>
                    <li><a href="<?php echo URL; ?>about"><?php echo $appSession->getLang()->find("About");?></a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- page-header-section end -->

<!-- contact section start -->
<section class="contact-section section-ptb">
    <div class="container">
        <div class="row">
          
            <div class="col-12">
                <div class="contact-info-wrapper">
                    <div class="contact-info">
                        <h4>Về chúng tôi</h4>
                        <p>
						<h3>Công ty Cổ phần Fosacha</h3><br>
							Địa chỉ: <b>Hà Nội: 130 Nguyễn Đức Cảnh, phường Tương Mai, quận Hoàng Mai, Hà Nội.
							Hồ Chí Minh: 163 Điện Biên Phủ, phường 15, quận Bình Thạnh, TP Hồ Chí Minh.</b><br>
							Mã số thuế: <b>0109771441</b><br>
							Số điện thoại: <b>0869330380</b><br>
							Địa chỉ Email: <b>info@fosacha.com</b><br>
						</p>
                        
                    </div>
                </div>
            </div>
           
        </div>
    </div>
</section>
<!-- about section end -->

<script>
function sendContact() {

    var ctrName = document.getElementById('editcontact_name');
    if (ctrName.value == '') {
        alert('Họ tên không được để trống!');
        ctrName.focus();
        return;
    }

    var ctrPhone = document.getElementById('editcontact_phone');
    if (ctrPhone.value == '') {
        alert('Số điện thoại không được để trống!');
        ctrPhone.focus();
        return;
    }

    var ctrEmail = document.getElementById('editcontact_email');
    if (!validateEmail(ctrEmail.value)) {
        alert('Email không đúng định dạng!');
        ctrEmail.focus();
        return;
    }

    if (ctrEmail.value == '') {
        alert('Email không được để trống!');
        ctrEmail.focus();
        return;
    }

    var ctrTitle = document.getElementById('editcontact_title');
    if (ctrTitle.value == '') {
        alert('Tiêu đề không được để trống!');
        ctrTitle.focus();
        return;
    }

    var ctrMessage = document.getElementById('editcontact_message');
    if (ctrMessage.value == '') {
        alert('Nội dung không được để trống!');
        ctrMessage.focus();
        return;
    }

    javascript: sendEmailContact(ctrName.value, ctrPhone.value, ctrEmail.value, ctrTitle.value, ctrMessage.value,
        function(status, message) {
            if (status == 0) {
                if (message == 'OK') {
                    document.location.href = '<?php echo URL; ?>';
                } else {
                    console.log(message);
                }
            }
        })

}

function validateEmail(email) {
    const re =
        /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

function isValidPhone(str) {
    str = str.replace(/[^0-9]/g, '');
    var l = str.length;
    if (l < 10) return ['error', 'Tel number length < 10'];

    var tel = '',
        num = str.substr(-7),
        code = str.substr(-10, 3),
        coCode = '';
    if (l > 10) {
        coCode = '+' + str.substr(0, (l - 10));
    }
    tel = coCode + ' (' + code + ') ' + num;

    return ['succes', tel];
}
</script>