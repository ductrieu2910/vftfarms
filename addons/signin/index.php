   <!-- page-header-section start -->
   <div class="page-header-section">
       <div class="container">
           <div class="row">
               <div class="col-12 d-flex justify-content-between justify-content-md-end">
                   <ul class="breadcrumb">
                       <li><a href="<?php echo URL; ?>"><?php echo $appSession->getLang()->find("Home");?></a></li>
                       <li><span>/</span></li>
                       <li><?php echo $appSession->getLang()->find("Sign In");?></li>
                   </ul>
               </div>
           </div>
       </div>
   </div>
   <!-- page-header-section end -->

   <section class="login-section section-ptb">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-6 mb--30 mb-lg-0">
                            <div class="eflux-login-form-area">
                                <form action="#" class="eflux-login-form">
                                    
                                    <div class="input-item">
                                        <label><?php echo $appSession->getLang()->find("Username");?></label>
                                        <input type="text" id="edituser" autocomplete =false />
                                    </div>

                                    <div class="input-item">
                                        <label><?php echo $appSession->getLang()->find("Password");?></label>
                                        <input type="password" id="editpassword" autocomplete =false />
                                    </div>

                                    <div>
                                        <button type="button" class="submit" onclick="signIn()"><?php echo $appSession->getLang()->find("Sign In");?></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="col-lg-5 pl-lg-5">
                            <div class="new-customer-container">
                                <h6><?php echo $appSession->getLang()->find("New Customer");?></h6>
                                <div class="new-customer-box">
                                    <h6><?php echo $appSession->getLang()->find("Create a New Account");?></h6>
                                    <p><?php echo $appSession->getLang()->find("Sign up for a free account at our store. Registration is quick and easy. It allows you to be able to order from our shop. To start shopping click register");?>.</p>
                                    <a href="<?php echo URL;?>signup"  class="signup-btn"><?php echo $appSession->getLang()->find("Sign Up");?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
   <script>
   

	 function signIn() 
	 {
		var ctr = document.getElementById('edituser');
		if (ctr.value == '') {
			alert('<?php echo $appSession->getLang()->find("Name is not empty");?>');
			ctr.focus();
			return;
		}
		var user_name = ctr.value;
	
		ctr = document.getElementById('editpassword');
		if (ctr.value == '') {
			alert('<?php echo $appSession->getLang()->find("Password is not empty");?>');
			ctr.focus();
			return;
		}
		var password = ctr.value;
		password = Sha1.hash(password);
		doLoginAccount(user_name, password, function(status, message){
			
			if( message.indexOf("OK") != -1)
			{
				document.location.href="<?php echo URL;?>";
				
			}else if(message == "INCORRECT")
			{
				alert('<?php echo $appSession->getLang()->find("Invalid password");?>');
			}else if(message == "INVALID")
			{
				alert('<?php echo $appSession->getLang()->find("Invalid user name");?>');
			}
		}
		);
    }
	
   </script>