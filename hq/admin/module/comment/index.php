<?php
$ac = "";
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "")
{
	$ac = "view";
}
if($ac == "view")
{
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$parent_id = "";
	if(isset($_REQUEST['parent_id']))
	{
		$parent_id = $_REQUEST['parent_id'];
	}
	$name = "";
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	
	$comment_id = $appSession->getTool()->getId();
	
	
	$msg = $appSession->getTier()->createMessage();
	$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
	$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
	$msg->add("user_id", $appSession->getUserInfo()->getId());
	
	$sql = "SELECT d1.id, d1.receipt_no, d1.receipt_date, d1.content, d2.name FROM comment d1 LEFT OUTER JOIN res_user d2 ON(d1.create_uid = d2.id) WHERE d1.rel_id='".$rel_id."'";
	$sql = $sql." ORDER BY d1.create_date ASC";
	
	$msg->add("query", $sql);
	
	
	$comments = $appSession->getTier()->getArray($msg);
		
	
?>
	<?php
	for($j=0; $j<count($comments); $j++)
	{
		$_id = $comments[$j][0];
		$comment_receipt_no = $comments[$j][1];
		$comment_receipt_date = $comments[$j][2];
		$comment_content = $comments[$j][3];
		$comment_user_name = $comments[$j][4];
		if($j>0)
		{
			echo "<hr>";
		}
		
	?>
	<div class="row">
		<div class="col m12">
			<?php
			if($comment_receipt_date != "")
			{
				$comment_receipt_date = $appSession->getFormats()->getDATE()->formatShortDateTime($appSession->getTool()->toDateTime($comment_receipt_date));
			}
			echo "<b>".$comment_user_name. "</b> - ".$comment_receipt_date;
			
			?>
			
			<?php echo $comment_content;?>
			<?php
			$sql = "SELECT d1.id, d1.name, d1.ext FROM document d1 WHERE d1.rel_id='".$_id."' AND d1.status =0 ORDER BY d1.create_date ASC";
			$msg->add("query", $sql);
			$docList = $appSession->getTier()->getArray($msg);
			if(count($docList)>0)
			{
				
				for($d =0; $d<count($docList); $d++)
				{
					$row = $docList[$d];
					$file_id = $row[0];
					$document_name = $row[1];
					$extension = $row[2];
					if($d>0)
					{
						echo "<br>";
					}
			?>
			<?php echo ($d + 1);?>.  <a href="<?php echo URL;?>document/?id=<?php echo $file_id; ?>" target="_blank">
			<?php echo $document_name; ?>.<?php echo $extension;?></a> <a href="<?php echo URL;?>document/?id=<?php echo $file_id; ?>" target="_blank"><i class="icon-cloud-download"></i></a>
						
			<?php
				}
			}
			?>
		</div>
	</div>
	<?php
	}
	?>
	
<div class="row">
	<div class="col m12">
		<textarea id="comment_<?php echo $name;?>" name="comment_<?php echo $name;?>" class="textarea" rows="8" ></textarea>
	</div>
</div>
<br>
<div class="row">
	<div class="col m1">
		<a class="button btn-primary" href="javascript:sendComment()"><?php echo $appSession->getLang()->find("Post");?></a>
	</div>
	<div class="col m11">
		<div id="pnDocList"></div>
	</div>
</div>
<script>
	tinymce.remove('#comment_<?php echo $name;?>');
	tinymce.init({
	selector: '#comment_<?php echo $name;?>',
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
				if(status== 0 && message.length == 36 )
				{
					
					var sr = "document/?id=" + message;
					 cb(sr);
				}else
				{
				}
				
			}, true);

		  };
		  reader.readAsDataURL(file);
		};

		input.click();
	  }
  
	
});
function loadDoc()
{
	var _url = '<?php echo URL?>document/?ac=upload_view&can_edit=1&rel_id=<?php echo $comment_id;?>';
	loadPage('pnDocList', _url, function(status, message)
	{
		if(status== 0)
		{
			
		}
		
	}, false);
}
loadDoc();
function sendComment()
{
	var content = tinymce.get('comment_<?php echo $name;?>').getContent();
	var _url = '<?php echo URL?>admin/module/comment/?ac=addComment&rel_id=<?php echo $rel_id;?>&comment_id=<?php echo $comment_id;?>&parent_id=<?php echo $parent_id;?>';
	_url = _url + '&content=' + encodeURIComponent(content);
	
	loadPage("contentView", _url, function(status, message)
			{
				if(status== 0)
				{
					if(message == "OK")
					{
						viewComment();
					}else{
						alert(message);
					}
					
				}
				
			}, true);
	
}
</script>
<?php
}else if($ac == "addComment")
{

	$id = "";
	if(isset($_REQUEST['comment_id']))
	{
		$id = $_REQUEST['comment_id'];
	}
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$parent_id = "";
	if(isset($_REQUEST['parent_id']))
	{
		$parent_id = $_REQUEST['parent_id'];
	}

	$content = "";
	if(isset($_REQUEST['content']))
	{
		$content = $_REQUEST['content'];
	}
	$receipt_no = $appSession->getTool()->findReceiptNo($appSession->getTier(), "comment", $appSession->getConfig()->getProperty("root_company_id"));
	$sql = "INSERT INTO comment(";
	$sql = $sql."id";
	$sql = $sql.", receipt_no";
	$sql = $sql.", receipt_date";
	$sql = $sql.", create_uid";
	$sql = $sql.", write_uid";
	$sql = $sql.", create_date";
	$sql = $sql.", write_date";
	$sql = $sql.", company_id";
	$sql = $sql.", status";
	$sql = $sql.", rel_id";
	$sql = $sql.", content";
	$sql = $sql." )VALUES(";
	$sql = $sql."'".$id."'";
	$sql = $sql.", '".$receipt_no."'";
	$sql = $sql.", ".$appSession->getTier()->getDateString();
	$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
	$sql = $sql.", '".$appSession->getUserInfo()->getId()."'";
	$sql = $sql.", ".$appSession->getTier()->getDateString();
	$sql = $sql.", ".$appSession->getTier()->getDateString();
	$sql = $sql.", '".$appSession->getUserInfo()->getCompanyId()."'";
	$sql = $sql.", 0";
	$sql = $sql.", '".$rel_id."'";
	$sql = $sql.", '".$appSession->getTool()->replace($content, "'", "''")."'";
	$sql = $sql.")";
	
	$msg = $appSession->getTier()->createMessage();
	$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
	$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
	$msg->add("user_id", $appSession->getUserInfo()->getId());
	
	$msg->add("query", $sql);
	$result = $appSession->getTier()->exec($msg);
	echo "OK";
}

?>