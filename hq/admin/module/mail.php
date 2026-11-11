<?php
$cview = "";
if(isset($_REQUEST['cview']))
{
	$cview = $_REQUEST['cview'];
}
$pageIndex = $appSession->getId();


$prev_view_module_id = "";
$arr_params = explode('&', $params);
$paramList =[];
for($p=0; $p<count($arr_params); $p++)
{
	$arrItem = explode('=', $arr_params[$p]);
	if(count($arrItem)>1)
	{
		if($arrItem[0] == "rel_id")
		{
			$rel_id = $arrItem[1];
		}
		if($arrItem[0] == "view_module_id")
		{
			$prev_view_module_id = $arrItem[1];
		}
		
		$paramList[count($paramList)] = [$arrItem[0], $arrItem[1]];
	}
}

$msg = $appSession->getTier()->createMessage();
$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
$msg->add("user_id", $appSession->getUserInfo()->getId());


$module_caption = "";
$form_caption = "";	

$sql = "SELECT d1.module_model_id, d1.data FROM ir_module_model_attribute d1 LEFT OUTER JOIN ir_module_model d2 ON(d1.module_model_id = d2.id) WHERE d1.status =0 AND d2.group_id='".$module_model_id."'";
if($root_module_id != "")
{
	$sql = $sql. " AND (d2.module_id='".$module_id."' OR d2.module_id='".$root_module_id."')";
}else
{
	$sql = $sql. " AND d2.module_id='".$module_id."'";
}
$msg->add("query", $sql);

$result = $appSession->getTier()->getArray($msg);
$numrows = count($result);	
$values = [];
for($i =0; $i<$numrows; $i++)
{
	$row = $result[$i];
	$arr = [];
	$arr[0] = $row[0];
	$arr[1] = $row[1];
	$values[$i] = $arr;
}



$sql = "SELECT d1.id, d1.parent_id, d2.name, d3.name AS rename, d4.name AS module_name FROM ir_module_model d1 LEFT OUTER JOIN ir_model d2 ON(d1.model_id = d2.id) LEFT OUTER JOIN ir_model_attribute d3 ON(d1.rel_id = d3.id) LEFT OUTER JOIN ir_module d4 ON(d1.module_id = d4.id) WHERE d1.status =0 AND d1.group_id='".$module_model_id."'";
if($root_module_id != "")
{
	$sql = $sql. " AND (d1.module_id='".$module_id."' OR d1.module_id='".$root_module_id."')";
}else
{
	$sql = $sql. " AND d1.module_id='".$module_id."'";
}
$sql = $sql." ORDER BY d1.create_date ASC";

$msg->add("query", $sql);
$result = $appSession->getTier()->getArray($msg);
$numrows = count($result);	


$to_email = "";
$from_table = "";
for($i =0; $i<$numrows; $i++)
{
	$row = $result[$i];
	
	if($row[3] == "to_email")
	{
		$to_email = findValue($values, $row[0]);
		
	}
	if($row[3] == "from_table")
	{
		$from_table = findValue($values, $row[0]);
		
	}
	
	if($module_caption == "")
	{
		$module_caption = $row[4];
	}
			
}
if($from_table != "")
{
	$sql = "SELECT * FROM ".$from_table." WHERE id='".$rel_id."'";
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	if($dt->getRowCount()>0)
	{
		for($j=0; $j<$dt->getColumnCount(); $j++)
		{
			$column_name = $dt->getColumns()[$j]->getName();
			if($to_email == $column_name)
			{
				$to_email = $dt->getString(0, $column_name);
			}
			$paramList[count($paramList)] = [$column_name, $dt->getString(0, $column_name)];
			
		}
	}
}


$appSession->getLang()->load($appSession->getTier(), $module_id, $appSession->getConfig()->getProperty("lang_id"));





?>
<div class="content-header">
	<div class="row">
		<div class="col m12" id ="navTop">
		</div>
	</div>
	<div class="row padding-top-small">
		<div class="col m8">
		<button type="button" class="button btn-secondary" onclick="back<?php echo $pageIndex;?>()"><?php echo $appSession->getLang()->find("Back");?></button>
	
		</div>
		<div class="col m4" style="text-align:right">
			
			<div class="dropdown-hover">
			  <span><img src="<?php echo URL;?>assets/img/more_vert.png"></span>
			  <div class="dropdown-content" style="right:0; text-align:left">
				<a href="javascript:del<?php echo $pageIndex;?>()" class="navi-link">
				<i class="icon-trash"></i> <?php echo $appSession->getLang()->find("Delete");?>
				</a>
				
				<a href="javascript:gt('contentView', '22332e18-bed2-46b5-b21e-91eef0629730', '', '', 'rel_id=<?php echo $id;?>', '')" class="navi-link">
					<i class="icon-paper-clip"></i> <?php echo $appSession->getLang()->find("Attachment");?>
				</a>
				<a href="javascript:gt('contentView', '3fe9c732-157e-4a1e-f34d-c836b3114c94', '', '', 'rel_id=<?php echo $id;?>', '')" class="navi-link">
					<i class="icon-note"></i> <?php echo $appSession->getLang()->find("Note");?>
				</a>
				<a href="javascript:gt('contentView', '563d8be9-24b3-47c1-b105-eb2763fb2448', '', '', 'rel_id=<?php echo $id;?>', '')" class="navi-link">
					<i class="icon-bubbles"></i> <?php echo $appSession->getLang()->find("Comment");?>
				</a>

				
				<a href="#" class="navi-link">
					<i class="icon-question"></i> <?php echo $appSession->getLang()->find("Help");?>
				</a>
			  </div>
			</div>
				
		</div>
	</div>
</div>
<?php
$sql = "SELECT d1.id, d1.name FROM mail_account d1 WHERE d1.status =0 AND d1.create_uid='".$appSession->getUserInfo()->getId()."'";

$msg->add("query", $sql);
$account = $appSession->getTier()->getArray($msg);
$mail_from = "";
if(count($account)>0)
{
	$mail_from = $account[0][1];
}
?>
<div class="content-body" >
	<div class="content-body-backcolor content-body-forecolor border shadow--1dp">
		<div class="row padding">
			<div class="col m2">
				From: 
			</div>
			<div class="col m4">
				<select class="select" id="editfrom" onchange="fromChanged(this)">
				<?php
				for($i=0; $i<count($account); $i++)
				{
				?>
				<option value="<?php echo $account[$i][0];?>"><?php echo $account[$i][1];?></option>
				<?php
				}
				?>
				</select>
			</div>
			<div class="col m6">
				<input type="text" class="input" id="editmail_from" value="<?php echo $mail_from;?>">
			</div>
		</div>
		<div class="row padding">
			<div class="col m2">
				To: 
			</div>
			<div class="col m10">
				<input type="text" id="editto" value="<?php echo $to_email;?>" class="input"/>
			</div>
		</div>
		<div class="row padding">
			<div class="col m2">
				Cc: 
			</div>
			<div class="col m10">
				<input type="text" value="" id="editcc" class="input"/>
			</div>
		</div>
		<div class="row padding">
			<div class="col m2">
				Bcc: 
			</div>
			<div class="col m10">
				<input type="text" value="" id="editbcc" class="input"/>
			</div>
		</div>
		<div class="row padding">
			<div class="col m2">
				Subject: 
			</div>
			<div class="col m10">
				<input type="text" value="" id="editsubject" class="input"/>
			</div>
		</div>
		<div class="row padding">
			<div class="col m2">
				Message: 
			</div>
			<div class="col m10">
				
				<textarea class="textarea" id="editmessage" name="editmessage" rows="8" class="input"></textarea>
			</div>
		</div>
		<div class="row padding">
			<div class="col m2">
				&nbsp;
			</div>
			<div class="col m10">
				
				<button onclick="sendMail()" class="button btn-primary"><?php echo $appSession->getLang()->find("Send");?></button>
			</div>
		</div>
	</div>
</div>
<div class="content-footer">
	<div class="row">
		<div class="col m12" id ="navBottom">
			
		</div>
	</div>
</div>
	 
	
<script>
	
	
	function back<?php echo $pageIndex;?>()
	{
		clearNav(navList.length -1);
		var item = navList[navList.length -1];
		clearNav(navList.length -1);
		gt(item.v, item.module_id, item.model_name, item.id, item.params, item.root_module_id);
		
		
	}
	
	
	addNav('<?php echo $form_caption;?>', 'contentView', '<?php echo $module_id;?>', '<?php echo $model_name;?>', '<?php echo $id;?>', '<?php echo $params;?>', '<?php echo $root_module_id;?>');
	printNav("navTop");
	printNav("navBottom");
	
	tinymce.remove('#editmessage');
	tinymce.init({
		selector: '#editmessage',
		content_style: '.mce-content-body {font-size:10pt;font-family:Arial,sans-serif;}',
		toolbar: 'code | mybutton undo redo | image media | styleselect | bold italic | alignleft aligncenter alignright alignjustify fontselect fontsizeselect | bullist numlist outdent indent | link | table tabledelete',
		plugins: 'lists autolink table image code link table ',
		file_picker_types: 'file image media',
		image_uploadtab: true,
		menubar: false,
		height: 150,

		  document_base_url : "<?php echo URL;?>",
		  automatic_uploads: false,
		  images_upload_url: '<?php echo URL;?>/document/',
		  file_picker_types: 'image',
		  
		  file_picker_callback: function (cb, value, meta) {
			var input = document.createElement('input');
			input.setAttribute('type', 'file');
			input.setAttribute('accept', 'image/*');

			

			input.onchange = function () {
			  var file = this.files[0];

			  var reader = new FileReader();
			  reader.onload = function () {
				
				var base64 = reader.result.split(',')[1];
				var _url = '<?php echo URL;?>document/?ac=upload&data=' + encodeURIComponent(base64);
				_url = _url + "&name=" + file.name;
				_url = _url + "&company_id=<?php echo $appSession->getUserInfo()->getCompanyId();?>";
				loadPage('pnSensoryCriteria', _url, function(status, message)
				{
					if(status== 0 )
					{
						
						var sr = message;
						 cb(sr);
					}
					
				}, true);

			  };
			  reader.readAsDataURL(file);
			};

			input.click();
		  }
	  
		
	});
	function fromChanged(e)
	{
		
			
		var text=e.options[e.selectedIndex].text;
		var ctr = document.getElementById('editmail_from');
		ctr.value = text;
	}
	function sendMail()
	{
		var ctr = document.getElementById('editfrom');
		if(ctr.value == '')
		{
			alert("Select from account");
			ctr.focus();
			return;
		}
		var from = ctr.value;
		var ctr = document.getElementById('editmail_from');
		var mail_from =  ctr.value;
		
		ctr = document.getElementById('editto');
		if(ctr.value == '')
		{
			alert("Input to");
			ctr.focus();
			return;
		}
		var mail_to = ctr.value;
		ctr = document.getElementById('editcc');
		var cc = ctr.value;
		
		ctr = document.getElementById('editbcc');
		var bcc = ctr.value;
		
		ctr = document.getElementById('editsubject');
		if(ctr.value == '')
		{
			alert("Input subject");
			ctr.focus();
			return;
		}
		var subject = ctr.value;
		
		var content = tinymce.get('editmessage').getContent();
		var _url = '<?php echo URL?>admin/module/mail/?ac=send&rel_id=<?php echo $rel_id;?>';
		_url = _url + '&from_account_id=' + encodeURIComponent(from);
		_url = _url + '&mail_from=' + encodeURIComponent(mail_from);
		_url = _url + '&mail_to=' + encodeURIComponent(mail_to);
		_url = _url + '&cc=' + encodeURIComponent(cc);
		_url = _url + '&bcc=' + encodeURIComponent(bcc);
		_url = _url + '&subject=' + encodeURIComponent(subject);
		_url = _url + '&body=' + encodeURIComponent(content);
		
		loadPage("contentView", _url, function(status, message)
		{
			if(status== 0)
			{
				if(message == "OK")
				{
					alert("Mail send");
					back<?php echo $pageIndex;?>()
				}else{
					alert(message);
				}
				
			}
			
		}, true);
		
	}
		

</script>