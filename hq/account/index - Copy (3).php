<?php
function listCompany($appSession, $id)
{
	$msg = $appSession->getTier()->createMessage();
	$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
	$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
	$msg->add("user_id", $appSession->getUserInfo()->getId());
	
	$s = [];
	$sql = "SELECT id, parent_id FROM res_company WHERE status =0 AND parent_id='".$id."'";
	
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	for($i=0; $i<count($result); $i++)
	{
		
		$s[count($s)] = [$result[$i][0], $result[$i][1]] ;
		/*if($result[$i][0] != $result[$i][1])
		{
			$s1 = listCompany($appSession, $result[$i][0]);
			for($j =0; $j<count($s1); $j++)
			{
				$s[count($s)] = $s1[$j];
			}
		}*/
		
	}
	return $s;
}

$continue = "";
if(isset($_REQUEST['continue']))
{
	$continue = $_REQUEST['continue'];
}

$ac = '';
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if(isset($_POST['ac']))
{
	$ac = $_POST['ac'];
}

if($ac == "")
{
	$ac = "view";
}
$message = "";
$user = '';
$company_id = "";
$login_count = 0;
$check_count = 3;

//$appSession->getTool()->send_mail("", "", "vang@bluebytez.com", "vang@bluebytez.com", "Hello", "Test mail");
$msg = $appSession->getTier()->createMessage();

if($ac == "active")
{
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}

	$continue = "";
	if(isset($_REQUEST['continue']))
	{
		$continue = $_REQUEST['continue'];
	}
	
	$msg->add("id", $id);
	$msg->add("action", "b4fe23b8-8423-4ebd-995f-67ea976238f7");
	$message = $appSession->getTier()->getData($msg);
	$ac = "view";
	
}else if($ac == "captcha")
{
	$x = 4;
	$captcha_code = substr(str_shuffle("0123456789"), 0, $x);
	$_SESSION["captcha_code"] = $captcha_code;
	$layer = imagecreatetruecolor(80, 37);
	$captcha_bg = imagecolorallocate($layer, 247, 174, 71);
	imagefill($layer, 0, 0, $captcha_bg);
	$captcha_text_color = imagecolorallocate($layer, 0, 0, 0);
	imagestring($layer, 5, 20, 10, $captcha_code, $captcha_text_color);
	header("Content-type: image/jpeg");
	imagejpeg($layer);

}else if($ac == "lang")
{
	if(isset($_REQUEST["id"]))
	{
		if($_REQUEST["id"] != "")
		{
			$appSession->getConfig()->setProperty("lang_id", $_REQUEST["id"]);
			$appSession->getConfig()->save();
		}
		
	}
	$ac = "view";
}
else if($ac == "login")
{
	
	
	$ac = "view";
	$valid = 1;
	if(isset($_SESSION["login_count"]))
	{
		$login_count = $_SESSION["login_count"];
		if($login_count > $check_count)
		{
			$captcha_code = "";
			if(isset($_SESSION["captcha_code"]))
			{
				$captcha_code = $_SESSION["captcha_code"];
			}
			$captcha = '';
			if(isset($_REQUEST['captcha']))
			{
				$captcha = $_REQUEST['captcha'];
			}
			if(isset($_POST['captcha']))
			{
				$captcha = $_POST['captcha'];
			}
			if($captcha != $captcha_code)
			{
				$message = "Security code is invalid";
				$valid = 0;
			}
			
			
		}
		
	}
	$login_count = $login_count + 1;
	$_SESSION["login_count"] = $login_count;
	
	if(isset($_REQUEST['company_id']))
	{
		$company_id = $_REQUEST['company_id'];
	}
	
	if(isset($_REQUEST['user_name']))
	{
		$user = $_REQUEST['user_name'];
	}
	if(isset($_POST['user_name']))
	{
		$user = $_POST['user_name'];
	}

	$password = '';
	if(isset($_REQUEST['password']))
	{
		$password = $_REQUEST['password'];
	}
	if(isset($_POST['password']))
	{
		$password = $_POST['password'];
	}
	if($valid == 1)
	{
		$sql = "SELECT d1.id, d1.password, d1.name, d3.company_id, d1.date_format, d1.thousands_sep, d1.time_format, d1.decimal_point, d1.avatar, d1.lang_id, d2.currency_id, d3.group_id FROM res_user d1 LEFT OUTER JOIN res_user_company d3 ON(d1.id = d3.user_id AND d3.status =0) LEFT OUTER JOIN res_company d2 ON(d3.company_id = d2.id) WHERE d1.status =0 AND d3.status=0 AND (lower(d1.user_name)='".strtolower($user)."' OR lower(d1.email) ='".strtolower($user)."') AND d1.status =0";
		
	
		$msg->add("query", $sql);
	
		$dt = $appSession->getTier()->getTable($msg);
	
		if($dt->getRowCount()>0)
		{
			
			$row = $dt->getRow(0);
			$user_id = $row->getString("id");
			$s = $appSession->getTool()->toHash("sha256", "[".$user_id."]".$password);
			$len = $appSession->getTool()->lenght($password);
			for($i = 0; $i<$len; $i++)
			{
				$s = $s.chr($i + 48);
			}
			$password = $appSession->getTool()->toHash("md5", $s);
			
			if($password == $row->getString("password"))
			{
				if($appSession->getConfig()->getProperty("lang_id") != "")
				{
					$sql = "UPDATE res_user SET lang_id='".$appSession->getConfig()->getProperty("lang_id")."' WHERE id='".$user_id."'";
					$msg->add("query", $sql);
					$appSession->getTier()->exec($msg);
				}else{
					$appSession->getConfig()->setProperty("lang_id", $row->getString("lang_id"));
				}
				if($company_id == "")
				{
					$company_id = $row->getString("company_id");
				}
				
				$sql = "SELECT d1.database_id, d2.name, d3.type, d3.url FROM ir_database_rel d1 LEFT OUTER JOIN ir_database d2 ON(d1.database_id = d2.id) LEFT OUTER JOIN ir_data_resource d3 ON(d2.resource_id = d3.id) WHERE d1.status=0 ";
				$sql = $sql." AND d1.rel_id='".$company_id."'";
				
				$msg->add("query", $sql);
				$result1 = $appSession->getTier()->getArray($msg);
					
				if(count($result1)>0)
				{
					$db_id = $result1[0][0];
					
					$appSession->getConfig()->setProperty("db_id", $db_id);
					if($result1[0][1] != "")
					{
						$appSession->getConfig()->setProperty("db_name", $result1[0][1]);
					}
					if($result1[0][2] != "")
					{
						$appSession->getConfig()->setProperty("db_type", $result1[0][2]);
					}
					if($result1[0][3] != "")
					{
						$arr = explode("&", $result1[0][3]);
				
						for($i =0; $i<count($arr); $i++)
						{
							$pos = strpos($arr[$i], "=");
							if ($pos !== false){
								$k = substr($arr[$i], 0, $pos);
								$v =  substr($arr[$i], $pos + 1);
								if($k == "url")
								{
									$appSession->getConfig()->setProperty("db_host", $v);
								}else if($k == "port")
								{
									$appSession->getConfig()->setProperty("db_port", $v);
								}else if($k == "user")
								{
									$appSession->getConfig()->setProperty("db_user", $v);
								}else if($k == "password")
								{
									$appSession->getConfig()->setProperty("db_password", $v);
								}
							}
						}
					}
					
					$sql = "SELECT d1.partner_id, d1.url FROM res_company_partner d1 WHERE d1.status=0";
					$sql = $sql." AND d1.rel_id ='".$company_id."'";
					
					$msg->add("query", $sql);
					
					$result1 = $appSession->getTier()->getArray($msg);
					$partner_url = "";
					if(count($result1)>0)
					{
						$company_id = $result1[0][0];
						$partner_url = $result1[0][1];
					}else{
						$company_id = "ROOT";
					}
					
					$appSession->getConfig()->setProperty("partner_url", $partner_url);
					$appSession->getTier()->setDBId($db_id);
					
				}
				
				$appSession->getConfig()->setProperty("user_id", $user_id);
				$appSession->getConfig()->setProperty("user_name", $row->getString("name"));
				$appSession->getConfig()->setProperty("company_id", $company_id);
				$appSession->getConfig()->setProperty("date_format", $row->getString("date_format"));
				$appSession->getConfig()->setProperty("thousands_sep", $row->getString("thousands_sep"));
				$appSession->getConfig()->setProperty("time_format", $row->getString("time_format"));
				$appSession->getConfig()->setProperty("decimal_point", $row->getString("decimal_point"));
				$appSession->getConfig()->setProperty("avatar", $row->getString("avatar"));
				
				$appSession->getConfig()->setProperty("currency_id", $row->getString("currency_id"));
				$appSession->getConfig()->setProperty("root_company_id", $row->getString("root_company_id"));
				$appSession->getConfig()->setProperty("user_group_id", $row->getString("group_id"));
				
				
				
				$root_company_id = $company_id;
	
				$p_company_ids = $company_id;
				$pid = $company_id;
				$level = 0;
				while($level<10)
				{
					$sql = "SELECT parent_id FROM res_company WHERE status =0 AND id='".$pid."'";
					$msg->add("query", $sql);
					$result1 = $appSession->getTier()->getArray($msg);
					
					$numrows1 = count($result1);	
					if($numrows1>0)
					{
						$row1 = $result1[0];
						$pid = $row1[0];
					
						if($pid == "")
						{
							break;
						}
						if(strpos($p_company_ids, $pid) !== false)
						{
							break;
						}
						
						$root_company_id = $pid;
						
						$p_company_ids = $p_company_ids.",".$pid;
					
					}else
					{
						break;
					}
					
					$level = $level + 1;
					
				}
				
				
				if($root_company_id == "")
				{
					$root_company_id = $company_id;
				}
				
				
				
				
				
				$company_ids = $p_company_ids;
				$c_company_ids = $company_id;
				if($company_id != "")
				{
					$sql = "SELECT id FROM res_company WHERE status =0 AND parent_id='".$company_id."'";
					$msg->add("query", $sql);
					$result1 = $appSession->getTier()->getArray($msg);
					
					for($j =0; $j<count($result1); $j++)
					{
						if($c_company_ids != "")
						{
							$c_company_ids = $c_company_ids.",";
						}
						$c_company_ids = $c_company_ids.$result1[$j][0];
					}
					if($c_company_ids != "")
					{
						if($company_ids != "")
						{
							$company_ids = $company_ids.",";
						}
						$company_ids = $company_ids.$c_company_ids;
					}
				}
				
				
				$appSession->getConfig()->setProperty("company_ids", $company_ids);
				$appSession->getConfig()->setProperty("parent_company", $p_company_ids);
				$appSession->getConfig()->setProperty("child_company", $c_company_ids);
				
				$appSession->getConfig()->save();
				
				$_SESSION["login_count"] = 0;
				if($continue == "")
				{
					$continue = URL;
				}
				
				header('Location: '.$continue);
				exit;
			}else{
				
				$message = 'Password is invalid';
			}
			
		}else{
			
			$message = 'Account name is invalid';
		}
	}
	
}else if($ac == "logout")
{
	$appSession->getConfig()->setProperty("user_id", "");
	$appSession->getConfig()->setProperty("user_name", "");
	$appSession->getConfig()->setProperty("company_id", "");
	$appSession->getConfig()->setProperty("db_id", "");
	$appSession->getConfig()->setProperty("db_name", "");
	$appSession->getConfig()->setProperty("db_user", "");
	$appSession->getConfig()->setProperty("db_password", "");
	$appSession->getConfig()->setProperty("db_host", "");
	$appSession->getConfig()->setProperty("db_port", "");
	$appSession->getConfig()->save();
	
	header('Location: '.$continue);
	exit;
	
	
}else if($ac == "loadLocation")
{
	$user = '';
	if(isset($_REQUEST['user']))
	{
		$user = $_REQUEST['user'];
	}
	$user = strtolower($user);
	$sql = "SELECT d1.company_id, d2.name, d5.name AS group_name, d6.name AS company_name FROM res_user_company d1";
	$sql = $sql." LEFT OUTER JOIN res_company d2 ON(d1.company_id = d2.id)";
	$sql = $sql." LEFT OUTER JOIN res_user d4 ON(d1.user_id = d4.id)";
	$sql = $sql." LEFT OUTER JOIN res_user_group d5 ON(d1.group_id = d5.id)";
	$sql = $sql." LEFT OUTER JOIN res_company d6 ON(d1.company_id = d6.id)";
	$sql = $sql." WHERE d1.status =0 AND (lower(d4.user_name) ='".str_replace("'", "''", $user)."' OR lower(d4.email)='".str_replace("'", "''", $user)."' OR lower(d4.phone)='".str_replace("'", "''", $user)."')";
	
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getTableMaster($msg);
	
	$numrows =$result->getRowCount();
	
	for($i=0; $i<$numrows; $i++)
	{
		$row = $result->getRow($i);
		if($i>0)
		{
			echo "\n";
		}
		$name = $row->getString("name");
		if($row->getString("group_name") != "")
		{
			$name = $name.' - '.$row->getString("group_name");
		}
		if($row->getString("name") != $row->getString("company_name"))
		{
			$name = $name.' - '.$row->getString("company_name");
		}
		echo $row->getString("company_id")."=".$name;
	}
}
if($ac == "view")
{
	$lang_id = $appSession->getConfig()->getProperty("lang_id");
	if($lang_id == "")
	{
		if(LANGUAGE == "")
		{
			$default_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
			$lang_id = $default_lang;
		}else{
			$lang_id = LANGUAGE;
		}
		
	}

	$appSession->getLang()->load($appSession->getTier(), "account", $lang_id);
	$logo = logo;
	if($logo == "")
	{
		$logo = "120.png";
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?php echo $appSession->getLang()->find("Account");?> - <?php echo META_TITLE;?></title>
<meta http-equiv="x-ua-compatible" content="ie=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="shortcut icon" href="<?php echo URL;?>favicon.ico" type="image/png"/>
<link rel="stylesheet" href="<?php echo URL;?>assets/css/font_default.css">
<link rel="stylesheet" href="<?php echo URL;?>assets/css/style.css">
<script src="<?php echo URL;?>assets/js/jquery.js"></script>
<script src="<?php echo URL;?>assets/js/controller.js"></script>
<script src="<?php echo URL;?>assets/js/tool.js"></script>
</head>
<body style="background-image: url('<?php echo URL;?>assets/img/bg/bg-3.jpg');">
<style>

#frmLoginInfo
{
    background-color: #fff;
    background-image: -webkit-gradient(linear, left top, left bottom, from(#fff), to(#eee));
    background-image: -webkit-linear-gradient(top, #fff, #eee);
    background-image: -moz-linear-gradient(top, #fff, #eee);
    background-image: -ms-linear-gradient(top, #fff, #eee);
    background-image: -o-linear-gradient(top, #fff, #eee);
    background-image: linear-gradient(top, #fff, #eee);  
	<?php
	if($login_count > $check_count)
	{
		echo "height: 510px;";
	}else
	{
		if(account_no_register !="1")
		{
			if($message == "")
			{
				echo "height: 470px;";
			}else
			{
				echo "height: 490px;";
			}
		}else
		{
			if($message == "")
			{
				echo "height: 390px;";
			}else
			{
				echo "height: 410px;";
			}
		}
		
	}
	?>
    
    width: 460px;
    margin: -150px 0 0 -230px;
    padding: 30px;
    position: absolute;
	top:260px;
    left: 50%;
    z-index: 1000;
    -moz-border-radius: 3px;
    -webkit-border-radius: 3px;
    border-radius: 3px;  
    -webkit-box-shadow:
          0 0 2px rgba(0, 0, 0, 0.2),
          0 1px 1px rgba(0, 0, 0, .2);

    
}

#frmLoginInfo:before
{
    content: '';
    position: absolute;
    z-index: -1;
    border: 1px dashed #ccc;
    top: 5px;
    bottom: 5px;
    left: 5px;
    right: 5px;
    -moz-box-shadow: 0 0 0 1px #fff;
    -webkit-box-shadow: 0 0 0 1px #fff;
    box-shadow: 0 0 0 1px #fff;
}

</style>
<table width="100%"><tr><td align="center">	
<a href="<?php echo URL;?>"><img src="<?php echo URL;?>assets/img/<?php echo $logo;?>" height="90" ></a></td></tr></table>
<form class="form" id="frmLoginInfo" name="frmLoginInfo">
	
<!--begin::Title-->
<div class="row">
	<div class="col m12">
	<h3><?php echo $appSession->getLang()->find("Sign In")?></h3>
	<span style="color:red"><b><?php echo $appSession->getLang()->find($message)?></b></span>
	</div>
</div>
<!--begin::Title-->

<!--begin::Form group-->
<div class="row padding">
	<div class="col m12">
		<label class="label"><?php echo $appSession->getLang()->find("Account name")?>:</label>
		<input class="input" type="text" id="edituser_name" value="<?php echo  $user;?>" autocomplete="off" onKeyDown="if(event.keyCode == 13){login();}" onblur="loadLocation(this.value,'')" />
	</div>
</div>

<?php
if($appSession->getConfig()->getProperty("addons") == "mart")
{
?>
<div class="row padding">
	<div class="col m12">
		<label class="label"><?php echo $appSession->getLang()->find("Location")?>:</label>
		<select class="input" type="text" id="editcompany_id">
		</select>
	</div>
</div>
<?php
}
?>
<?php
	if($login_count > $check_count)
	{
?>
<div class="row padding">
	<div class="col m8">
		<label class="label"><?php echo $appSession->getLang()->find("Security Code")?>: </label>
		<input class="input"  type="text" id="editcaptcha" value="" autocomplete="off" onKeyDown="if(event.keyCode == 13){login();}" />
	</div>
	<div class="col m4">
		<label></label>
		<br>
		<img width="100%" height="36px" src="<?php echo URL;?>account/?ac=captcha" onclick="this.src = this.src">
	</div>
</div>
<?php
	}
?>
<div class="row padding">
	<div class="col m12">
		<label class="label"><?php echo $appSession->getLang()->find("Password")?>:</label>
		<input class="input" type="password" id="editpassword" autocomplete="off" onKeyDown="if(event.keyCode == 13){login();}" />
	</div>
</div>
<!--end::Form group-->

<!--begin::Action-->
<div class="row padding">

	<div class="col m6">
		<button type="button" class="button-raised btn-primary" onclick="login()"><?php echo $appSession->getLang()->find("Login")?></button> 
	</div>
	<div class="col m6" style="text-align:right">
		<?php 
		if(account_reset_register !="1")
		{
		?>
		<a href="javascript:forgot()"><?php echo $appSession->getLang()->find("Forgot Password")?></a>
		<?php
		}
		?>
	</div>
</div>
<!--end::Action-->
<?php 
if(account_no_register !="1")
{
?>
<div class="row">
	<div class="col m12">
	<br>
	<input type="button" class="button btn-success" style="width:100%" onclick="register()" value="<?php echo $appSession->getLang()->find("Create Account")?>"/>
	</div>
	
</div>
<?php
}
?>
</form>
<form id="frmSubmit" name="frmSubmit" method="post" action="<?php echo URL;?><?php echo $routing->find("account");?>/">
	<input type="hidden" id="ac" name="ac" value="login"/>
	<input type="hidden" id="user_name" name="user_name"/>
	<input type="hidden" id="password" name="password"/>
	<input type="hidden" id="captcha" name="captcha"/>
	<input type="hidden" id="continue" name="continue" value="<?php echo $continue;?>"/>
	<input type="hidden" id="company_id" name="company_id"/>
</form>

<?php



$sql = "SELECT d1.id, d1.name, lg.description FROM res_lang d1 LEFT OUTER JOIN res_lang_line lg ON(lg.lang_id='".$lang_id."' AND lg.rel_id = d1.id AND lg.name='name' AND lg.status =0) WHERE d1.status =0 AND (d1.id='' OR d1.code ilike '%".$lang_id."%')";
$sql = $sql." LIMIT 1";


$msg->add("query", $sql);
$arr = $appSession->getTier()->getArray($msg);
if($lang_id == "" && count($arr)>0)
{
	$lang_id = $arr[0][0];
}

$sql = "SELECT d1.id, d1.name, lg.description FROM res_lang d1 LEFT OUTER JOIN res_lang_line lg ON(lg.lang_id='".$lang_id."' AND lg.rel_id = d1.id AND lg.name='res_lang_name' AND lg.status =0) WHERE d1.status =0";
	
	$sql = $sql." ORDER BY d1.sequence ASC, lg.description ASC LIMIT 10";

$msg->add("query", $sql);

$langs = $appSession->getTier()->getArray($msg);

?>
<div class="bottom center">
<?php

for($i =0; $i<count($langs); $i++)
{
	$hasItem = false;
	for($j=0; $j<count($arr); $j++)
	{
		if($arr[$j][0] == $langs[$i][0])
		{
			$hasItem  = true;
			break;
		}
	}
	if($hasItem == false)
	{
		$arr[count($arr)] = $langs[$i];
	}
}
if(count($arr)>0)
{
?>
<b><?php echo $appSession->getLang()->find("Language");?>:</b> 
<?php
	for($i=0; $i<count($arr); $i++)
	{
		if($i == 7)
		{
			break;
		}
		if($i>0)
		{
			echo " / ";
		}
		$name = $arr[$i][2];
		if($name == "")
		{
			$name = $arr[$i][1];
		}
		?>
		<a href="javascript:lang('<?php echo $arr[$i][0];?>')"><?php echo $name;?></a>
		<?php
	}
	if(count($arr)>=10)
	{
	?>
	 / <a href="javascript:moreLang()"><?php echo $appSession->getLang()->find("More");?></a>
	<?php
	}
}
?>
<br>
<br>
</div>
<div id="popView" class="modal" style="z-index:3000">
    <div class="modal-content" style="max-width:600px">
      <header class="container"> 
        <span onclick="closePopup()" 
        class="button display-topright">&times;</span>
        &nbsp;
      </header>
	  <hr>
      <div class="container">
        <div id="pnFullDialogContent" class="padding-16" ></div>
      </div>
 
    </div>
 </div>
 <script src="<?php echo URL;?>assets/js/sha1.js"></script>
<script>
function login()
{
	var ctr = frmLoginInfo.edituser_name;
	if(ctr.value == '')
	{
		alert("Please enter user");
		ctr.focus();
		return;
	}
	var user_name = ctr.value;
	ctr = frmLoginInfo.editpassword;
	if(ctr.value == '')
	{
		alert("Please enter password");
		ctr.focus();
		return;
	}
	var password = ctr.value.trim();
	var company_id = "";
	<?php
	if($appSession->getConfig()->getProperty("addons") == "mart")
	{
	?>
	company_id = frmLoginInfo.editcompany_id.value;
	<?php
	}
	?>
	<?php
	if($login_count > $check_count)
	{
	?>
	var ctr = frmLoginInfo.editcaptcha;
	if(ctr.value == '')
	{
		alert("Please enter captcha code");
		ctr.focus();
		return;
	}
	document.frmSubmit.captcha.value = ctr.value;
	<?php
	}
	?>
	password = Sha1.hash(password);
	
	document.frmSubmit.user_name.value = user_name.trim();
	document.frmSubmit.password.value = password;
	document.frmSubmit.company_id.value = company_id;
	document.frmSubmit.submit();
}

frmLoginInfo.edituser_name.focus();

function register()
{
	
	var _url = '<?php echo URL;?>account/?ac=signup';
	openPopup(_url);
}
function lang(id)
{
	var _url = '<?php echo URL;?>account/?ac=lang&id=' + id;
	document.location.href = _url;
}
function openPopup(_url)
{
	loadPage('pnFullDialogContent', _url, function(status, message)
	{
		if(status== 0)
		{
			document.getElementById('popView').style.display='block';
		}
		
	}, false);
	
}
function closePopup()
{
	document.getElementById('popView').style.display='none';
}
function moreLang()
{
	var _url = '<?php echo URL;?>account/?ac=langs';
	openPopup(_url);
}
function forgot()
{
	var _url = '<?php echo URL;?>account/?ac=forgot';
	
	openPopup(_url);
}

function loadLocation(name, company_id)
{
	var company = document.frmLoginInfo.editcompany_id;
	while (company.length > 0) {
	  company.remove(0);
	}
			
	var _url = '<?php echo URL;?>account/?ac=loadLocation&user=' + encodeURIComponent(name);
	console.log(_url);
	loadPage('pnPopuContent', _url, function(status, message)
	{
		if(status== 0)
		{
			
			var arr = message.split('\n');
			for(var i=0; i<arr.length; i++)
			{
				var item = arr[i];
				var index = item.indexOf('=');
				if(index != -1)
				{
			
					var option = document.createElement("option");
					option.text = item.substring(index + 1);
					option.value = item.substring(0, index);
					company.add(option);
					if(option.value == company_id){
						document.frmLoginInfo.editcompany_id.selectedIndex =i;
					}
				}
			}
			if(arr.length>0 && company_id == "")
			{
				document.frmLoginInfo.editcompany_id.selectedIndex =0;
			}
			
		}
		
	}, true);
}
<?php if($user != "")
{
?>
loadLocation('<?php echo $user;?>', '<?php echo $company_id;?>');
<?php
}
?>

</script>
</body>
</html>
<?php
}else if($ac == "signup")
{
	$appSession->getLang()->load($appSession->getTier(), "account", $appSession->getConfig()->getProperty("lang_id"));
?>
<form id="frmRegister" name="frmRegister">
  <div class="row">
		<div class="col m12">
		  <h2><?php echo $appSession->getLang()->find("Sign Up");?></h2>
		  <?php echo $appSession->getLang()->find("Please fill in this form to create an account");?>.
		</div>
  </div>
  
  <hr>
   <div class="row">
		<div class="col m4">
			 <label class="label"><?php echo $appSession->getLang()->find("Name");?> <span style="color:red">*</span>:</label>
		</div>
		<div class="col m8">
		
		  <input type="text" class="input" placeholder="<?php echo $appSession->getLang()->find("Enter name");?>" id="editregister_name" autocomplete="false">
		</div>
	</div>
	<div class="row">
		<div class="col m4">
			 <label class="label"><?php echo $appSession->getLang()->find("Email");?> <span style="color:red">*</span>:</label>
		</div>
		<div class="col m8">
		  <input type="text" class="input" placeholder="<?php echo $appSession->getLang()->find("Enter email address");?>" id="editregister_email" autocomplete="false">
		</div>
	</div>
	<div class="row">
		<div class="col m4">
			 <label class="label"><?php echo $appSession->getLang()->find("Mobile");?>:</label>
		</div>
		<div class="col m8">
		  <input type="text" class="input" placeholder="<?php echo $appSession->getLang()->find("Enter mobile number");?>" id="editregister_mobile" autocomplete="false">
		</div>
	</div>
	<div class="row">
		<div class="col m4">
			 <label class="label"><?php echo $appSession->getLang()->find("Account name");?> <span style="color:red">*</span>:</label>
		</div>
		<div class="col m8">
		
		  <input type="text" class="input" placeholder="<?php echo $appSession->getLang()->find("Enter account name");?>" id="editregister_user_name" autocomplete="false">
		</div>
	</div>
	<div class="row">
		<div class="col m4">
			 <label class="label"><?php echo $appSession->getLang()->find("Password");?> <span style="color:red">*</span>:</label>
		</div>
		<div class="col m8">
			<input type="password" class="input" placeholder="<?php echo $appSession->getLang()->find("Enter password");?>" id="editregister_password" autocomplete="false">
		</div>
	</div>
	<div class="row">
		<div class="col m4">
			<label class="label"><?php echo $appSession->getLang()->find("Repeat password");?> <span style="color:red">*</span>:</label>
		</div>
		<div class="col m8">
			<input type="password" class="input" placeholder="<?php echo $appSession->getLang()->find("Enter repeat password");?>" name="editregister_repeat_password" autocomplete="false">
		</div>
	</div>


	<div class="row">
		<div class="col m12">
			<p><?php echo $appSession->getLang()->find("By creating an account you agree to our");?> <a href="#" style="color:dodgerblue"><?php echo $appSession->getLang()->find("Terms & Privacy");?></a>.</p>
		</div>
	</div>
	<hr>
  <div class="row">
		<div class="col m6">
			<button type="button" class="button btn-danger" style="width:100%" onclick="closePopup()" ><?php echo $appSession->getLang()->find("Cancel");?></button>
			
		</div>
		<div class="col m6">
		<button type="button" onclick="do_register()" class="button btn-primary" style="width:100%" ><?php echo $appSession->getLang()->find("Sign Up");?></button>
		</div>
</div>
</form>
<script>
function do_register()
{
	var ctr = document.frmRegister.editregister_name;
	if(ctr.value == '')
	{
		
		alert("<?php echo $appSession->getLang()->find('Please enter name');?>");
		ctr.focus();
		return false;
	}
	var name = ctr.value.trim();

	ctr = document.frmRegister.editregister_email;
	if(ctr.value == '')
	{
		ctr.focus();
		alert("<?php echo $appSession->getLang()->find('Please enter email');?>");
		return false;
	}
	if(validate_email(ctr.value) == false)
	{
		alert("<?php echo $appSession->getLang()->find('Invalid email');?>");
		ctr.focus();
		return;
	}
	var email = ctr.value.trim();
	
	
	ctr = document.frmRegister.editregister_mobile;
	var phone = ctr.value.trim();

	ctr = document.frmRegister.editregister_user_name;
	if(ctr.value == '')
	{
		ctr.focus();
		alert("<?php echo $appSession->getLang()->find('Please enter account name');?>");
		return false;
	}
	var user_name = ctr.value.trim();
	
	ctr = document.frmRegister.editregister_password;
	if(ctr.value == '')
	{
		ctr.focus();
		alert("<?php echo $appSession->getLang()->find('Please enter password');?>");
		return false;
	}
	var password = ctr.value.trim();
	if(document.frmRegister.editregister_repeat_password.value != password)
	{
		document.frmRegister.editre_password.focus();
		alert("<?php echo $appSession->getLang()->find('Password is not mark');?>");
		return false;
	}

	password = Sha1.hash(password);


	var _url = '<?php echo URL?>account/?ac=submit_signup&name=' + encodeURIComponent(name);
	_url = _url + '&email=' + encodeURIComponent(email);
	_url = _url + '&phone=' + encodeURIComponent(phone);
	_url = _url + '&user_name=' + encodeURIComponent(user_name);
	_url = _url + '&password=' + encodeURIComponent(password);
	
	loadPage('contentView', _url, function(status, message)
	{
		if(status== 0)
		{
			if(message == "OK")
			{
				alert('<?php echo $appSession->getLang()->find("Please check email to complete signup");?>');
				closePopup();
			}else if(message == "USER_AVAIBLE")
			{
				alert('<?php echo $appSession->getLang()->find("Account is avaible");?>');
				
			}else if(message == "EMAIL_AVAIBLE")
			{
				alert('<?php echo $appSession->getLang()->find("Email is avaible");?>');
			}else if(message == "MOBILE_AVAIBLE")
			{
				alert('<?php echo $appSession->getLang()->find("Mobile is avaible");?>');
			}
			else
			{
				alert(message);
			}
			
		}
		
	}, true);
	
	
	
}
</script>
<?php
}else if($ac == "submit_signup")
{
	$message = "";
	
	$name = "";
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	if(isset($_POST['name']))
	{
		$name = $_POST['name'];
	}
	$email = "";
	if(isset($_REQUEST['email']))
	{
		$email = $_REQUEST['email'];
	}
	if(isset($_POST['email']))
	{
		$email = $_POST['email'];
	}
	
	$phone = "";
	
	if(isset($_REQUEST['phone']))
	{
		$phone = $_REQUEST['phone'];
	}
	if(isset($_POST['phone']))
	{
		$phone = $_POST['phone'];
	}
	$user_name = "";
	if(isset($_REQUEST['user_name']))
	{
		$user_name = $_REQUEST['user_name'];
	}
	if(isset($_POST['user_name']))
	{
		$user_name = $_POST['user_name'];
	}
	
	if(isset($_REQUEST['password']))
	{
		$password = $_REQUEST['password'];
	}
	if(isset($_POST['password']))
	{
		$password = $_POST['password'];
	}
	
	$msg->add("action", "38cc4eed-4483-4738-af08-2ee3066e5728");
	$msg->add("name", $name);
	$msg->add("email", $email);
	$msg->add("phone", $phone);
	$msg->add("user_name", $user_name);
	$msg->add("password", $password);
	$msg->add("lang_id", $appSession->getConfig()->getProperty("lang_id"));
	$url = URL.$routing->find("account").'/?ac=active&id={id}&continue='.$appSession->getTool()->urlEncode($continue);
	$body = $appSession->getLang()->find("Dear").' '.$name.',<br><br>'.$appSession->getLang()->find("To complete register").'.<br>'.$appSession->getLang()->find("Please").' <a href="'.$url.'"> '.$appSession->getLang()->find("click").' </a> '.$appSession->getLang()->find("or copy link").' '.$url;
	$msg->add("body", $body);	
	$msg->add("subject",  $appSession->getLang()->find("Register account"));
	
	$message = $appSession->getTier()->getData($msg);
	echo $message;
}
else if($ac == "langs")
{
	$appSession->getLang()->load($appSession->getTier(), "account", $appSession->getConfig()->getProperty("lang_id"));
	
	
	$lang_id = $appSession->getConfig()->getProperty("lang_id");
	if($lang_id == "")
	{
		$default_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
		$lang_id = $default_lang;
	}
	
	$sql = "SELECT d1.id, d1.name, lg.description FROM res_lang d1 LEFT OUTER JOIN res_lang_line lg ON(d1.id = lg.rel_id AND lg.name='res_lang_name' AND lg.lang_id='".$lang_id."') WHERE d1.status =0 ";
	$sql = $sql." ORDER BY lg.description ASC, d1.name ASC";
	
	
	$msg->add("query", $sql);
	$langs = $appSession->getTier()->getArray($msg);
	
	$len = count($langs)/3;
	$start = 0;
	?>
	<div class="row">
		<div class="col m12">
		  <h2><?php echo $appSession->getLang()->find("Language");?></h2>
		</div>
    </div>
	<hr>
	<div class="row">
	
		<div class="col m4">
		<?php
		for($i = 0; $i<$len; $i++)
		{
			$start = $start + 1;
			if($i>=$len)
			{
				break;
			}
			$name = $langs[$i][2];
			if($name == "")
			{
				$name = $langs[$i][1];
			}
			echo "<br>";
		?>
		<a href="javascript:lang('<?php echo $langs[$i][0];?>')"><?php echo $name;?></a>
		<?php
		}
		?>
		</div>
		<div class="col m4">
		<?php
		$len = ($len + $start);
		for($i = $start; $i<$len; $i++)
		{
			$start = $start + 1;
			if($i>=$len)
			{
				break;
			}
			$name = $langs[$i][2];
			if($name == "")
			{
				$name = $langs[$i][1];
			}
			echo "<br>";
		?>
		<a href="javascript:lang('<?php echo $langs[$i][0];?>')"><?php echo $name;?></a>
		<?php
		}
		?>
		</div>
		<div class="col m4">
		<?php
		for($i = $start; $i< count($langs); $i++)
		{
			$start = $start + 1;
			$name = $langs[$i][2];
			if($name == "")
			{
				$name = $langs[$i][1];
			}
			echo "<br>";
		?>
		<a href="javascript:lang('<?php echo $langs[$i][0];?>')"><?php echo $name;?></a>
		<?php
		}
		?>
		</div>
	</div>
	<?php
}else if($ac == "forgot")
{
	$appSession->getLang()->load($appSession->getTier(), "account", $appSession->getConfig()->getProperty("lang_id"));
?>
<div class="row">
		<div class="col m12">
		  <h2><?php echo $appSession->getLang()->find("Reset password");?></h2>
		  <?php echo $appSession->getLang()->find("Please fill in this form to reset password");?>.
		</div>
  </div>
  
  <hr>
   <div class="row">
		<div class="col m12">
			 <label class="label"><?php echo $appSession->getLang()->find("Account name or email");?>  :</label>
		</div>
	</div>
	 <div class="row">
		<div class="col m12">
		 <input type="text" class="input" placeholder="<?php echo $appSession->getLang()->find("Enter account name or email");?>" id="editregister_user_name" autocomplete="false">
		</div>
	</div>
	<hr>
  <div class="row">
		<div class="col m6">
			<button type="button" class="button btn-danger" style="width:100%" onclick="closePopup()" ><?php echo $appSession->getLang()->find("Cancel");?></button>
		</div>
		<div class="col m6">
		<button type="button" class="button btn-primary" onclick="reset_password()" style="width:100%" ><?php echo $appSession->getLang()->find("Submit");?></button>
		</div>
</div>
<script>
	function reset_password()
	{
		var ctr = document.getElementById('editregister_user_name');
		if(ctr.value == '')
		{
			alert('<?php echo $appSession->getLang()->find("Please enter user or email");?>');
			ctr.focus();
			return;
		}
		var name = ctr.value;
		name = name.trim();
		var _url = '<?php echo URL?>account/?ac=submit_reset_password&name=' + encodeURIComponent(name);
		
		loadPage('contentView', _url, function(status, message)
		{
			if(status== 0)
			{
				if(message == "OK")
				{
					alert('<?php echo $appSession->getLang()->find("Please check email to complete reset password");?>');
					closePopup();
				}else if(message == "INVALID_USER_EMAIL")
				{
					alert('<?php echo $appSession->getLang()->find("Invalid user or email");?>');
					
				}else
				{
					alert(message);
				}
				
			}
			
		}, true);
	}
	document.getElementById('editregister_user_name').focus();
</script>
<?php
}else if($ac == "submit_reset_password")
{
	$appSession->getLang()->load($appSession->getTier(), "account", $appSession->getConfig()->getProperty("lang_id"));
	$name = "";
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	$name = $appSession->getTool()->replace($name, "'", "''");
	
	$msg->add("action", "32c29c34-d47d-48eb-85bd-52e937f2e11c");
	$msg->add("name", $name);
	$url = URL.$routing->find("account").'/?ac=reset_password&id={id}&continue='.$appSession->getTool()->urlEncode($continue);
	$body = $appSession->getLang()->find("Dear").' {name},<br><br>'.$appSession->getLang()->find("To complete reset password").'.<br>'.$appSession->getLang()->find("Please").' <a href="'.$url.'"> '.$appSession->getLang()->find("click").' </a> '.$appSession->getLang()->find("or copy link").' '.$url;

	$msg->add("body", $body);
	$msg->add("subject", $appSession->getLang()->find("Reset password"));
	$message = $appSession->getTier()->getData($msg);
	echo $message;		
}else if($ac == "reset_password")
{
	$appSession->getLang()->load($appSession->getTier(), "account", $appSession->getConfig()->getProperty("lang_id"));
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	
	$continue = "";
	if(isset($_REQUEST['continue']))
	{
		$continue = $_REQUEST['continue'];
	}
	?>
	<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?php echo $appSession->getLang()->find("Reset password");?> - <?php echo META_TITLE;?></title>
<meta http-equiv="x-ua-compatible" content="ie=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="shortcut icon" href="<?php echo URL;?>favicon.ico" type="image/png"/>
<link rel="stylesheet" href="<?php echo URL;?>assets/css/fonts.css">
<link rel="stylesheet" href="<?php echo URL;?>assets/css/style.css?v=7">
<script src="<?php echo URL;?>assets/js/jquery.min.js"></script>
<script src="<?php echo URL;?>assets/js/controller.js"></script>
<script src="<?php echo URL;?>assets/js/tool.js"></script>
<script src="<?php echo URL;?>assets/js/sha1.js"></script>
</head>
<body>
<style>

#frmLoginInfo
{
    background-color: #fff;
    background-image: -webkit-gradient(linear, left top, left bottom, from(#fff), to(#eee));
    background-image: -webkit-linear-gradient(top, #fff, #eee);
    background-image: -moz-linear-gradient(top, #fff, #eee);
    background-image: -ms-linear-gradient(top, #fff, #eee);
    background-image: -o-linear-gradient(top, #fff, #eee);
    background-image: linear-gradient(top, #fff, #eee);  
	height: 300px;
    width: 450px;
    margin: -150px 0 0 -230px;
    padding: 30px;
    position: absolute;
	top:250px;
    left: 50%;
    z-index: 1000;
    -moz-border-radius: 3px;
    -webkit-border-radius: 3px;
    border-radius: 3px;  
    -webkit-box-shadow:
          0 0 2px rgba(0, 0, 0, 0.2),
          0 1px 1px rgba(0, 0, 0, .2);

    
}

#frmLoginInfo:before
{
    content: '';
    position: absolute;
    z-index: -1;
    border: 1px dashed #ccc;
    top: 5px;
    bottom: 5px;
    left: 5px;
    right: 5px;
    -moz-box-shadow: 0 0 0 1px #fff;
    -webkit-box-shadow: 0 0 0 1px #fff;
    box-shadow: 0 0 0 1px #fff;
}

</style>	
<form class="form" id="frmLoginInfo" name="frmLoginInfo">

<div class="row">
	<div class="col m12">
	<h3><?php echo $appSession->getLang()->find("Reset password")?></h3>
	<b><?php echo $appSession->getLang()->find($message)?></b>
	</div>
</div>


	<div class="row">
		<div class="col m12">
			<label class="label"><?php echo $appSession->getLang()->find("Password")?>:</label>
			<input class="input" type="password" id="editpassword" autocomplete="off" onKeyDown="if(event.keyCode == 13){reset_pwd();}" />
		</div>
	</div>
	<div class="row">
		<div class="col m12">
			<label class="label"><?php echo $appSession->getLang()->find("Repeat password")?>:</label>
			<input class="input" type="password" id="editrepeat_password" autocomplete="off" onKeyDown="if(event.keyCode == 13){reset_pwd();}" />
		</div>
	</div>

	<div class="row padding-small">

		<div class="col m6">
			<button type="button" class="button-raised btn-primary" onclick="reset_pwd()"><?php echo $appSession->getLang()->find("Submit")?></button> 
		</div>
		
	</div>
	
	</form>
	<script>
	function reset_pwd()
	{
		var ctr = document.getElementById('editpassword');
		if(ctr.value.trim() == '')
		{
			alert('<?php echo $appSession->getLang()->find("Please enter password");?>');
			ctr.focus();
			return;
		}
		var password = ctr.value;
		password = password.trim();
		ctr = document.getElementById('editrepeat_password')
		if(ctr.value != password)
		{
			alert('<?php echo $appSession->getLang()->find("Password is not mark");?>');
			ctr.focus();
			return;
		}
		
		password = Sha1.hash(password);
		
		var _url = '<?php echo URL?>account/?ac=do_reset_password&password=' + encodeURIComponent(password);
		_url = _url + '&id=' + encodeURIComponent('<?php echo $_REQUEST['id'];?>');
		loadPage('contentView', _url, function(status, message)
		{
			if(status== 0)
			{
				if(message == "OK")
				{
					alert('<?php echo $appSession->getLang()->find("Password is reset");?>');
					var _url = '<?php echo URL;?><?php echo $routing->find("account")?>/?continue='+ encodeURIComponent('<?php echo $continue;?>');
		document.location.href = _url;
				}else if(message == "INVALID_USER_ID")
				{
					alert('<?php echo $appSession->getLang()->find("Invalid user or email");?>');
					
				}else
				{
					alert(message);
				}
				
			}
			
		}, true);
	}

	</script>

	<?php
}else if($ac == "do_reset_password")
{
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	
	$password = "";
	if(isset($_REQUEST['password']))
	{
		$password = $_REQUEST['password'];
	}
	$msg->add("action", "975eecad-fad7-4225-ed61-e95343021c8c");
	$msg->add("password", $password);
	$msg->add("id", $id);
	
	$body = $appSession->getLang()->find("Reset password").' {name},<br><br>'.$appSession->getLang()->find("Password is reset").'.';

	$msg->add("body", $body);
	$msg->add("subject", $appSession->getLang()->find("Reset password"));
	$message = $appSession->getTier()->getData($msg);
	echo $message;		
	
		
}
?>
