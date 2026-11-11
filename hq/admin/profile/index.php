<?php
validUser($appSession);
$ac = "";
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "")
{
	$ac = "view";
}
$msg = $appSession->getTier()->createMessage();
$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
$msg->add("user_id", $appSession->getUserInfo()->getId());

if($ac == "view")
{
	$appSession->getLang()->load($appSession->getTier(), "profile", $appSession->getConfig()->getProperty("lang_id"));
	$company_ids = "rel_id='".$appSession->getUserInfo()->getCompanyId()."'";
	$pid = $appSession->getUserInfo()->getCompanyId();
	
	$sql = "SELECT d1.thousands_sep, d1.decimal_point, d1.date_format, d1.name, d1.lang_id, d1.title FROM res_user d1 LEFT OUTER JOIN res_company d2 ON(d1.company_id = d2.id) WHERE d1.id='".$appSession->getUserInfo()->getId()."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	
	$numrows = count($result);	
	
	
	
	$thousands_sep = ",";
	$decimal_point = ".";
	$date_format = "YYYY-MM-DD";
	$name = "";
	$lang_id = "";
	$title = "";
	if($numrows>0)
	{
		$row = $result[0];
		$thousands_sep = $row[0];
		$decimal_point = $row[1];
		$date_format = $row[2];
		$name = $row[3];
		$lang_id = $row[4];
		$title = $row[5];
	}
	
	$sql = "SELECT id, name FROM res_lang WHERE status =0 ORDER BY name ASC";
	$msg->add("query", $sql);
	$langs = $appSession->getTier()->getArray($msg);
	
	$sql = "SELECT d1.style_id, d1.type FROM res_style_rel d1 WHERE d1.rel_id='".$appSession->getUserInfo()->getId()."' AND d1.status =0";
	$msg->add("query", $sql);
	$styles = $appSession->getTier()->getArray($msg);
	$style = "";
	$theme = "";
	$font = "";
	for($i =0; $i<count($styles); $i++)
	{
		if($styles[$i][1] == "style")
		{
			$style = $styles[$i][0];
		}
		if($styles[$i][1] == "theme")
		{
			$theme = $styles[$i][0];
		}
		if($styles[$i][1] == "font")
		{
			$font = $styles[$i][0];
		}
	}
	
?>
<div class="content-header">
	<h3><?php echo $appSession->getUserInfo()->getName();?></h3>
	<div class="row padding-top-small">
			<div class="col m8">
			<button onclick="saveProfile();" class="button btn-primary"><?php echo $appSession->getLang()->find("Save");?></button>
		</div>
	</div>
</div>
<div class="content-body">
	<div class="row padding-small" style = "background-color:white" >
		<div class="col m4">
			Picture
		</div>
		<div class="col m8">
			<div class="row">
				<div class="col m12">
					<div class="form-group">
						<label>Name:</label>
						<input type="text" class="input" id="editname" value="<?php echo $name;?>"/>
					</div>
				</div>
			</div>
			<div class="row ">
				<div class="col m12 ">
					<div class="form-group">
						<label>Title:</label>
						<input type="text" class="input" id = "edittitle" value="<?php echo $title;?>"/>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col m6 ">
					<div class="form-group">
						<label>Thousands Point:</label>
						<input type="text" class="input" id="editthousands_sep" value="<?php echo $thousands_sep;?>"/>
					</div>
				</div>
				<div class="col m6">
					<div class="form-group">
						<label>Decimal point:</label>
						<input type="text" class="input" id="editdecimal_point" value="<?php echo $decimal_point;?>" />
					</div>
					
				</div>
			</div>
			<div class="row">
				<div class="col m6">
					<div class="form-group">
						<label>Date Format</label>
						<select id="editdate_format" id="editdate_format" class="select">
							<option value=""></option>
							<option value="DD-MM-YYYY" <?php if($date_format == "DD-MM-YYYY"){ echo " selected "; }?>>dd-MM-yyyy</option>
							<option value="DD-MMM-YYYY" <?php if($date_format == "DD-MMM-YYYY"){ echo " selected "; }?>>dd-MMM-yyyy</option>
							<option value="DD.MM.YYYY" <?php if($date_format == "DD.MM.YYYY"){ echo " selected "; }?> >dd.MM.yyyy</option>
							<option value="DD.MMM.YYYY" <?php if($date_format == "DD.MMM.YYYY"){ echo " selected "; }?>>DD.MMM.yyyy</option>
							<option value="DD/MM/YYYY" <?php if($date_format == "DD/MM/YYYY"){ echo " selected "; }?>>dd/MM/yyyy</option>
							<option value="DD/MMM/YYYY" <?php if($date_format == "DD/MMM/YYYY"){ echo " selected "; }?>>dd/MMM/yyyy</option>
							<option value="MM-DD-YYYY" <?php if($date_format == "MM-DD-YYYY"){ echo " selected "; }?>>MM-dd-yyyy</option>
							<option value="MM.DD.YYYY" <?php if($date_format == "MM.DD.YYYY"){ echo " selected "; }?>>MM.dd.yyyy</option>
							<option value="MM/DD/YYYY" <?php if($date_format == "MM/DD/YYYY"){ echo " selected "; }?>>MM/dd/yyyy</option>
							<option value="YYYY-MM-DD" <?php if($date_format == "YYYY-MM-DD"){ echo " selected "; }?>>yyyy-MM-dd</option>
							<option value="YYYY-MMM-DD" <?php if($date_format == "YYYY-MMM-DD"){ echo " selected "; }?>>yyyy-MMM-dd</option>
							<option value="YYYY.MM.DD" <?php if($date_format == "YYYY.MM.DD"){ echo " selected "; }?>>yyyy.MM.dd</option>
							<option value="YYYY.MMM.DD" <?php if($date_format == "YYYY.MMM.DD"){ echo " selected "; }?>>yyyy.MMM.dd</option>
							<option value="YYYY/MM/DD" <?php if($date_format == "YYYY/MM/DD"){ echo " selected "; }?>>yyyy/MM/dd</option>
							<option value="YYYY/MMM/DD" <?php if($date_format == "YYYY/MMM/DD"){ echo " selected "; }?>>yyyy/MMM/dd</option>
						</select>
					</div>
				</div>
				<div class="col m6">
					<div class="form-group">
						<label>Language:</label>
						<select id="editlang" class="select">
							<option value="">Select</option>
							<?php
							for($i =0; $i<count($langs); $i++)
							{
							?>
							<option value="<?php echo $langs[$i][0];?>" <?php if($lang_id == $langs[$i][0]){ echo " selected ";}?>><?php echo $langs[$i][1];?></option>
							<?php
							}
							?>
						</select>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col m3">
					<div class="form-group">
						<label>Style:</label>
						<select class="select" id="editstyle">
							<option value="style_left_pannel" <?php if($style == "style_left_pannel"){ echo " selected "; }?> >Left Pannel</option>
							<option value="style_top_menu" <?php if($style == "style_top_menu"){ echo " selected "; }?>>Top Menu</option>
						</select>
					</div>
				</div>
				<div class="col m3">
					<div class="form-group">
						<label>Theme:</label>
						<select class="select" id="edittheme">
							<option value="theme_light" <?php if($theme == "theme_light"){ echo " selected "; }?>>Light</option>
							<option value="theme_blue" <?php if($theme == "theme_blue"){ echo " selected "; }?>>Blue</option>
							<option value="theme_yellow" <?php if($theme == "theme_yellow"){ echo " selected "; }?>>Yellow</option>
							<option value="theme_red" <?php if($theme == "theme_red"){ echo " selected "; }?>>Red</option>
							<option value="theme_black" <?php if($theme == "theme_black"){ echo " selected "; }?>>Black</option>
						</select>
					</div>
				</div>
				<div class="col m3">
					<div class="form-group">
						<label>Font:</label>
						<select class="select" id="editfont">
							<option value="font_default" <?php if($font == "font_default"){ echo " selected "; }?>>Default</option>
							<option value="font_arial" <?php if($font == "font_arial"){ echo " selected "; }?>>Arial</option>
							<option value="font_times_new_roman" <?php if($font == "font_times_new_roman"){ echo " selected "; }?>>Times New Roman</option>
							<option value="font_courier_new" <?php if($font == "font_courier_new"){ echo " selected "; }?>>Courier New</option>
							<option value="font_verdana" <?php if($font == "font_verdana"){ echo " selected "; }?>>Verdana</option>
						</select>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col m12 ">
					<div class="form-group">
						<a href="javascript:changePassword()">Change Password</a>
					</div>
				</div>
				
			</div>
		</div>
	</div>
</div>
<script>
		function saveProfile()
		{
		
			ctr = document.getElementById('editname');
			if(ctr.value == "")
			{
				alert("Please enter full name");
				ctr.focus();
			}
			var name = ctr.value;
			ctr = document.getElementById('edittitle');
			var title = ctr.value;
			
			var thousands_sep = document.getElementById('editthousands_sep').value;
			if(thousands_sep == "")
			{
				thousands_sep = ",";
			}
			var decimal_point = document.getElementById('editdecimal_point').value;
			if(decimal_point == "")
			{
				decimal_point = ".";
			}
			if(thousands_sep == decimal_point)
			{
				thousands_sep = ",";
				decimal_point = ".";
			}
			var date_format = document.getElementById('editdate_format').value;
			var lang_id = document.getElementById('editlang').value;
			var style = document.getElementById('editstyle').value;
			var theme = document.getElementById('edittheme').value;
			var font = document.getElementById('editfont').value;
			
			var _url = '<?php echo URL?>admin/profile/?ac=save';
			_url = _url + "&title=" + encodeURIComponent(title);
			_url = _url + "&thousands_sep=" + encodeURIComponent(thousands_sep);
			_url = _url + "&decimal_point=" + encodeURIComponent(decimal_point);
			_url = _url + "&date_format=" + encodeURIComponent(date_format);
			_url = _url + "&name=" + encodeURIComponent(name);
			_url = _url + "&lang_id=" + lang_id;
			_url = _url + "&style=" + style;
			_url = _url + "&theme=" + theme;
			_url = _url + "&font=" + font;
			
			loadPage('contentView', _url, function(status, message)
			{
				if(status== 0)
				{
					if(message == "OK")
					{
						logout();
					}else
					{
						
						alert(message);
					}
					
				}
				
			}, true);
		}
		function changePassword()
		{
			document.location.href="<?php echo URL;?>account/?ac=reset_password";
		}
	</script>
<?php
}else if($ac == "save")
{
	$title = "";
	if(isset($_REQUEST['title']))
	{
		$company_name = $_REQUEST['title'];
	}
	
	$name = "";
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	$thousands_sep = "";
	if(isset($_REQUEST['thousands_sep']))
	{
		$thousands_sep = $_REQUEST['thousands_sep'];
	}
	$decimal_point = "";
	if(isset($_REQUEST['decimal_point']))
	{
		$decimal_point = $_REQUEST['decimal_point'];
	}
	$date_format = "";
	if(isset($_REQUEST['date_format']))
	{
		$date_format = $_REQUEST['date_format'];
	}
	$lang_id = "";
	if(isset($_REQUEST['lang_id']))
	{
		$lang_id = $_REQUEST['lang_id'];
	}
	$sql = "UPDATE res_user SET write_date=NOW()";
	$sql = $sql.", name='".str_replace("'", "''", $name)."'";
	$sql = $sql.", thousands_sep='".str_replace("'", "''", $thousands_sep)."'";
	$sql = $sql.", decimal_point='".str_replace("'", "''", $decimal_point)."'";
	$sql = $sql.", date_format='".str_replace("'", "''", $date_format)."'";
	$sql = $sql.", lang_id='".str_replace("'", "''", $lang_id)."'";
	$sql = $sql.", title='".str_replace("'", "''", $title)."'";
	$sql = $sql." WHERE id='".$appSession->getUserInfo()->getId()."'";
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	
	$style = "";
	if(isset($_REQUEST['style']))
	{
		$style = $_REQUEST['style'];
	}
	$theme = "";
	if(isset($_REQUEST['theme']))
	{
		$theme = $_REQUEST['theme'];
	}
	$font = "";
	if(isset($_REQUEST['font']))
	{
		$font = $_REQUEST['font'];
	}
	$styles =[["style", $style], ["theme", $theme], ["font", $font]];
	for($i =0; $i<count($styles); $i++)
	{
		$sql = "SELECT id FROM res_style_rel WHERE create_uid='".$appSession->getUserInfo()->getId()."' AND type='".$styles[$i][0]."'";
		$msg->add("query", $sql);
		$id = $appSession->getTier()->getValue($msg);
		if($id == "")
		{
			$id = $appSession->getTool()->getId();
			$sql = "INSERT INTO res_style_rel(";
			$sql = $sql."id";
			$sql = $sql.", create_uid";
			$sql = $sql.", create_date";
			$sql = $sql.", write_date";
			$sql = $sql.", company_id";
			$sql = $sql.", status";
			$sql = $sql.", rel_id";
			$sql = $sql.", style_id";
			$sql = $sql.", type";
			$sql = $sql." )VALUES(";
			$sql = $sql."'".$id."'";
			$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
			$sql = $sql.", ".$appSession->getTier()->getDateString();
			$sql = $sql.", ".$appSession->getTier()->getDateString();
			$sql = $sql.", '".$appSession->getConfig()->getProperty("root_company_id")."'";
			$sql = $sql.", 0";
			$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
			$sql = $sql.", '".$styles[$i][1]."'";
			$sql = $sql.", '".$styles[$i][0]."'";
			$sql = $sql.")";
			$msg->add("query", $sql);
			$appSession->getTier()->exec($msg);
		}else
		{
			$sql = "UPDATE res_style_rel SET write_date=".$appSession->getTier()->getDateString();
			$sql = $sql.", style_id='".$styles[$i][1]."'";
			$sql = $sql." WHERE id='".$id."'";
			$msg->add("query", $sql);
			$appSession->getTier()->exec($msg);
		}
	}
	
	
	echo "OK";
}
?>