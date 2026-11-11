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

   <section class="login-section section-ptb">
       <div class="container">
           <div class="row align-items-center">
               <div class="col-lg-12">
                   <div class="eflux-login-form-area">
                       <form action="#" class="eflux-login-form">
                           <div class="row">
                               <div class="col-lg-12">
                                   <div class="input-item">
                                       <label><?php echo $appSession->getLang()->find("Name");?> (*)</label>
                                       <input type="text" id="editregister_name" name="editregister_name"
                                           autocomplete=false />
                                   </div>
                               </div>
							</div>
                            <div class="row">
                               <div class="col-lg-6">
                                   <div class="input-item">
                                       <label><?php echo $appSession->getLang()->find("Email");?></label>
                                       <input type="text" id="editregister_email" name="editregister_email"
                                           autocomplete=false />
                                   </div>
                               </div>

                               <div class="col-lg-6">
                                   <div class="input-item">
                                       <label><?php echo $appSession->getLang()->find("Phone");?></label>
                                       <input type="number" id="editregister_phone" name="editregister_phone"
                                           autocomplete = false />
                                   </div>
                               </div>
							</div>
							<div class="row">

								<div class="col-lg-4">
                                   <div class="input-item">
                                       <label><?php echo $appSession->getLang()->find("Username");?> (*)</label>
                                       <input type="text" id="editregister_username" name="editregister_username"
                                           autocomplete=false />
                                   </div>
                               </div>
							   
                               <div class="col-lg-4">
                                   <div class="input-item">
                                       <label><?php echo $appSession->getLang()->find("Password");?> (*)</label>
                                       <input type="password" id="editregister_password" name="editregister_password"
                                           autocomplete=false />
                                   </div>
                               </div>
                               <div class="col-lg-4">
                                   <div class="input-item">
                                       <label><?php echo $appSession->getLang()->find("Retype password");?> (*)</label>
                                       <input type="password" id="editregister_re_password"
                                           name="editregister_re_password" autocomplete=false />
                                   </div>
                               </div>
							</div>
							<div class="row">
								<div class="col-lg-12">
                                   <div class="input-item">
                                       <label><?php echo $appSession->getLang()->find("Address");?></label>
                                       <textarea type="text" id="editregister_address" style="width: 100%;"></textarea>
                                   </div>
                               </div>
							 </div>
                           <div>
                               <a href="javascript:doRegister()" class="submit"><?php echo $appSession->getLang()->find("Register");?></a>
                           </div>
                       </form>
                   </div>
               </div>
           </div>
       </div>
   </section>
   <script>
   

	
	
function doRegister() {

    var ctr = document.getElementById('editregister_name');
    if (ctr.value == '') {
        alert('<?php echo $appSession->getLang()->find("Name is not empty");?>');
        ctr.focus();
        return;
    }
	var name = ctr.value;


	ctr = document.getElementById('editregister_email');
	/*if (ctr.value == '') {
        alert('<?php echo $appSession->getLang()->find("Email is not empty");?>');
        ctrEmail.focus();
        return;
    }*/
	
    if (ctr.value != "" && !validateEmail(ctr.value)) {
        alert('<?php echo $appSession->getLang()->find("Email is invalid");?>');
        ctr.focus();
        return;
    }
	var email = ctr.value;
    

   ctr = document.getElementById('editregister_phone');
    if (ctr.value == '') {
        alert('<?php echo $appSession->getLang()->find("Phone is not empty");?>');
        ctr.focus();
        return;
    }
    var phone = ctr.value;
	
	ctr = document.getElementById('editregister_username');
    if (ctr.value == '') {
        alert('Username is not empty');
        ctr.focus();
        return;
    }
	var user_name = ctr.value;
	
    ctr = document.getElementById('editregister_password');
    if (ctr.value == '') {
        alert('<?php echo $appSession->getLang()->find("Password is not empty");?>');
        ctr.focus();
        return;
    }
	var password = ctr.value;

	 var ctr = document.getElementById('editregister_re_password');

    if (password != ctr.value) {
        ctr.focus();
        alert('<?php echo $appSession->getLang()->find("Password is not mark");?>');
        return false;
    }
	password = Sha1.hash(password);
	ctr = document.getElementById('editregister_address');
	var address = ctr.value;
	
	var _url =  "<?php echo URL;?>api/account/?ac=register_account";
        _url = _url + "&name=" + encodeURIComponent(name);
        _url = _url + "&phone=" + encodeURIComponent(phone);
        _url = _url + "&email=" + encodeURIComponent(email);
        _url = _url + "&user=" + encodeURIComponent(user_name);
        _url = _url + "&address=" + encodeURIComponent(address);
        _url = _url + "&lang_id=" + "vi";
		_url = _url + "&source=" + "WEB";
        _url = _url + "&password=" + encodeURIComponent(password);

        loadPage('contentView', _url, function(status, message) {
            if (status == 0) {
                if (message.length == 36) {
                    document.location.href = '<?php echo URL; ?>signup-success';
                } else if (message.indexOf("USER_AVAIBLE") != -1) {
                    alert('<?php echo $appSession->getLang()->find("Username is avaible");?>');
                } else if (message.indexOf("PHONE_AVAIBLE") !=-1) {
                    alert('<?php echo $appSession->getLang()->find("Phone is avaible");?>');
                } else if (message.indexOf("EMAIL_AVAIBLE") != -1) {
                    alert('<?php echo $appSession->getLang()->find("Email is avaible");?>');
                } else {
                    console.log(message);
                }
            }
        }, true);

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