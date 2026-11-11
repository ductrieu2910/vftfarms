<!-- page-header-section start -->
<div class="page-header-section">
    <div class="container">
        <div class="row">
            <div class="col-12 d-flex justify-content-between justify-content-md-end">
                <ul class="breadcrumb">
                    <li><a href="<?php echo URL; ?>">Trang chủ </a></li>
                    <li><span>/</span></li>
                    <li>Liên hệ</li>
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
            <div class="col-12"><iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3725.151259604836!2d105.84986801527715!3d20.986572986021272!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135ac6ad1441baf%3A0x68951cb39e341434!2zMTMwIE5ndXnhu4VuIMSQ4bupYyBD4bqjbmgsIFTGsMahbmcgTWFpLCBIYWkgQsOgIFRyxrBuZywgSMOgIE7hu5lpLCBWaeG7h3QgTmFt!5e0!3m2!1svi!2s!4v1629791066518!5m2!1svi!2s"
                    width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe></div>
            <div class="col-lg-5 mb-4 mb-lg-0">
                <div class="contact-info-wrapper">
                    <div class="contact-info">
                        <h4>Liên hệ chúng tôi</h4>
                        <p></p>
                        <h3>CÔNG TY CỔ PHẦN VIFOTEC</h3><br>
										Địa chỉ: <b><br>- Tầng 1, số 130 Nguyễn Đức Cảnh, phường Tương Mai, quận Hoàng Mai, TP. Hà Nội, VN<br>
										- 489 Hoàng Quốc Việt, Cổ Nhuế, Cầu Giấy, Hà Nội</b><br>
										Số điện thoại: <b>0243 202 9199 - 0935 386 788</b><br>
										Địa chỉ Email: <b>info@vifotec.com</b><br>
										Mã số thuế: <b>0109385090</b><br>
										Số tài khoản: <b>3666699999 - Ngân hàng TMCP Ngoại thương Việt Nam - Chi nhánh Nam Hà Nội</b><br>
										Zalo: <a href="https://zalo.me/0935386788" target="_blank">0935386788</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="contact-form-area">
                    <form action="#" class="contact-form">
                        <div class="input-item">
                            <input type="text" id="editcontact_name" name="editcontact_name" placeholder="">
                            <i class="fas fa-user"></i>
                        </div>

                        <div class="input-item">
                            <input type="text" id="editcontact_phone" name="editcontact_phone"
                                placeholder="">
                            <i class="fas fa-mobile-alt"></i>
                        </div>

                        <div class="input-item">
                            <input type="email" id="editcontact_email" name="editcontact_email"
                                placeholder="">
                            <i class="fas fa-envelope"></i>
                        </div>

                        <div class="input-item">
                            <input type="text" id="editcontact_title" name="editcontact_title" placeholder="">
                            <i class="fas fa-globe"></i>
                        </div>

                        <div class="input-item">
                            <textarea id="editcontact_message" name="editcontact_message"
                                placeholder=""></textarea>
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <div>
                            <a href="javascript:sendContact()" type="submit" class="submit">Gửi tin nhắn</a>
                        </div>
                    </form>
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

  sendEmailContact(ctrName.value, ctrPhone.value, ctrEmail.value, ctrTitle.value, ctrMessage.value,
        function(status, message) {
            if (status == 0) {
				
                if (message.indexOf("OK") != -1) {
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