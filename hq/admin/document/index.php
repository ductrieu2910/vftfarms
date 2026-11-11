<?php

require_once(ABSPATH.'config.php' );

$ac = '';
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}

if($ac == "")
{
	$ac = "view";
}

$msg = $appSession->getTier()->createMessage();


if($ac == "view")
{
	$pageIndex = 0;
	if(isset($_REQUEST['pageIndex']))
	{
		$pageIndex = $_REQUEST['pageIndex'];
	}
	$rel_id = "";
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$readonly = "";
	if(isset($_REQUEST['readonly']))
	{
		$readonly = $_REQUEST['readonly'];
	}

	?>
	<div id="pnDocument<?php echo $pageIndex;?>"></div>
	<script>
	function loadDocument<?php echo $pageIndex;?>()
	{
		var _url = '<?php echo URL;?>admin/document/?ac=list_poster&rel_id=<?php echo $rel_id;?>&pageIndex=<?php echo $pageIndex;?>&readonly=<?php echo $readonly;?>';
		
		loadPage('pnDocument<?php echo $pageIndex;?>', _url, function(status, message)
		{
			if(status == 0)
			{
			}
			
		}, false);
	}
	function delPoster<?php echo $pageIndex;?>(id)
	{
		var result = confirm("Want to delete?");
		if (!result) {
			return;
		}

		var _url = '<?php echo URL;?>document/?ac=delDoc&id=' + id ;					
		loadPage('pnDocument<?php echo $pageIndex;?>', _url, function(status, message)
		{
			if(status  == 0)
			{
				if(message == "OK")
				{
					loadDocument<?php echo $pageIndex;?>();
					
				}else
				{
					alert(message);
				}
			}
		}, true);
	}
	function editName<?php echo $pageIndex;?>(id, name)
	{
		var name = prompt("Please enter name", name);
		if(name == "" || name == null)
		{
			return;
		}
		
		var _url = '<?php echo URL;?>document/?ac=updateName&id=' + id ;	
		_url = _url + "&name="+ encodeURIComponent(name);
		loadPage('pnDocument<?php echo $pageIndex;?>', _url, function(status, message)
		{
			if(status  == 0)
			{
				
				if(message.indexOf("OK") != -1)
				{
					loadDocument<?php echo $pageIndex;?>();
					
				}else
				{
					alert(message);
				}
			}
		}, true);
	}

	loadDocument<?php echo $pageIndex;?>();
	</script>
	<?php
}else if($ac == "list_poster")
{
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$readonly = '';
	if(isset($_REQUEST['readonly']))
	{
		$readonly = $_REQUEST['readonly'];
	}
	$pageIndex = 0;
	if(isset($_REQUEST['pageIndex']))
	{
		$pageIndex = $_REQUEST['pageIndex'];
	}
	$appSession->getLang()->load($appSession->getTier(),"DOCUMENT", $appSession->getConfig()->getProperty("lang_id"));
	
	$sql = "SELECT d1.id, d1.name, d1.ext, d1.path FROM document d1 WHERE d1.rel_id='".$rel_id."' AND d1.status =0";
	$sql = $sql." ORDER BY d1.create_date ASC";
	$msg->add("query", $sql);
	$arr = $appSession->getTier()->getArray($msg);

	if($readonly != "1"){
	?>
	<input id="files<?php echo $pageIndex;?>" type="file" multiple  style="display:none" onchange="readFile<?php echo $pageIndex;?>('', this);"><a href="javascript:document.getElementById('files<?php echo $pageIndex;?>').click();"><i class="icon-paper-clip"></i></a> <span id="progress_upload<?php echo $pageIndex;?>"></span>
	<?php
	}
	if(count($arr)>0)
	{
	?>
	<div class="responsive" >
		<table class="table" width="100%">
		
		<tbody>
		<?php
		for($i=0; $i<count($arr); $i++)
		{
			$id = $arr[$i][0];
			$name = $arr[$i][1];
			$ext = strtolower($arr[$i][2]);
			$path = strtolower($arr[$i][3]);
			$url = "";
			if($ext == "jpg" || $ext == "png")
			{
				$url = URL."document/?id=".$id."&h=72";
			}else{
				$url = URL."assets/img/ico/".$ext.".jpg";
			}
			
		?>
		<tr>
			<td width="36" style="padding-bottom:4"><a href="<?php echo URL;?>disk/<?php echo $path;?>/<?php echo $id;?>" target="_blank"><img style="max-height:36px" src="<?php echo $url;?>"></a></td>
			<td align="left" style="text-align:left" valign="middle"><a href="<?php echo URL;?>disk/<?php echo $path;?>/<?php echo $id;?>" target="_blank"><?php echo $name;?></a> </td>
			<td width="30" align="center" valign="middle"><a href="<?php echo URL;?>document/?id=<?php echo $id;?>" target="_blank"><img src="<?php echo URL;?>assets/img/get_app_black.png"></a></td>
			<?php if($readonly != "1"){ ?>
			<td width="30" align="center" valign="middle"><a href="<?php echo URL;?>document/?id=<?php echo $id;?>" target="_blank"><img src="<?php echo URL;?>assets/img/get_app_black.png"></a> <a href="javascript:editName<?php echo $pageIndex;?>('<?php echo $id;?>', '<?php echo $name;?>')"><i class="icon-pencil"></i></a></td>
			<td width="30" align="center" valign="middle"><a href="javascript:delPoster<?php echo $pageIndex;?>('<?php echo $id;?>')"><i class="icon-close"></i></a></td>
			<?php } ?>
		</tr>
		<?php
		}
		?>
		</tbody>
		</table>
	</div>
	<?php
	}
	?>
	<script>
	var reader<?php echo $pageIndex; ?>;
	var files<?php echo $pageIndex; ?>;
	var names<?php echo $pageIndex; ?> = new Array();
	var file_index<?php echo $pageIndex; ?> = 0;
	
	function readFile<?php echo $pageIndex; ?>(parent_id, theFiles) 
	{
		
		files<?php echo $pageIndex; ?> = theFiles.files;
		if (!files<?php echo $pageIndex; ?>.length) {
		  alert('Please select a file!');
		  return;
		}
		names<?php echo $pageIndex; ?> = new Array();
		for(var i =0; i<files<?php echo $pageIndex; ?>.length; i++)
		{
			var name = files<?php echo $pageIndex; ?>[i].name;
			var index = name.lastIndexOf(".");
			if(index != -1)
			{
				name = name.substr(0, index);
			}
			names<?php echo $pageIndex; ?>.push(name);
			
		}
		uploadDoc<?php echo $pageIndex; ?>();
		
		
	}
	function uploadDoc<?php echo $pageIndex; ?>()
	{
		
		
		file_index<?php echo $pageIndex; ?> = 0;
		upByFile<?php echo $pageIndex; ?>();
	}

	function postDataPage<?php echo $pageIndex; ?>(_url, params, complete)
	{
		  
		var xmlHttp = GetXmlHttpObject();
		if (xmlHttp == null) {
				alert("Browser does not support HTTP Request");
				return;
		}
		xmlHttp.open("POST", _url, true);
		xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded;charset=UTF-8");
		xmlHttp.onreadystatechange = function() 
		{
			if ((xmlHttp.readyState == 4) || (xmlHttp.readyState == "complete")) 
			{
				var responseMsg =  xmlHttp.responseText;
				if (xmlHttp.status == 200) 
				{
					complete(0, responseMsg);	
				}
			}
		};
		
		xmlHttp.send(params);
		
	}

	function upByFile<?php echo $pageIndex; ?>()
	{
		var file = files<?php echo $pageIndex; ?>[file_index<?php echo $pageIndex; ?>];
		file.name = "";
		var document_id = '';
		
		var _url = '<?php echo URL;?>document/?ac=createFile&document_id=' + document_id;

		loadPage('pnFileManager<?php echo $pageIndex; ?>', _url, function(status, message)
		{
			if(status  == 0)
			{
				if(message.length == 36)
				{
					var file_id = message;
					reader<?php echo $pageIndex; ?> = new FileReader();
					
					var byteRead = 1024 * 100;
					
					
					var start = 0;
					var stop = byteRead -1;
					var sizes = file.size;
					
					if(sizes<=byteRead)
					{
						stop = sizes - 1;
					}
					
					var baseUrl = '<?php echo URL;?>document/?ac=writeFile&file_id=' + file_id ;
					var proccess = document.getElementById('progress_upload<?php echo $pageIndex; ?>');
					reader<?php echo $pageIndex; ?>.onloadend = function(evt) {
					
					  if (evt.target.readyState == FileReader.DONE) { 
						
		
						var _url = baseUrl;
						if(start == 0)
						{
							_url = _url + '&n=1' 
						}
						
						var base64String = btoa(String.fromCharCode.apply(null, new Uint8Array(evt.target.result)));
					
						base64String = 'sData=' + encodeURIComponent(base64String);
						
						if(evt.target.result.byteLength>0)
						{
							postDataPage<?php echo $pageIndex; ?>(_url, base64String, function(status, message)
							{
								if(status  == 0)
								{
									if(message == "OK")
									{
										var p = (start/sizes) * 100;
										p = parseInt(p);
										proccess.innerHTML  = p + "%";
										stop = stop + 1;
										start = stop ;
										if((stop + 1)<sizes)
										{
							
											if((stop + byteRead)>=sizes)
											{
												byteRead = (sizes - stop);
											}
											stop = stop + byteRead;
											stop = stop -1;
											readBytes<?php echo $pageIndex; ?>(file, start, stop);
										}else
										{
											var extension = files<?php echo $pageIndex; ?>[file_index<?php echo $pageIndex; ?>].name;
											var index = extension.lastIndexOf(".");
											if(index != -1)
											{
												extension = extension.substr(index);
											}else{
												extension = "";
											}
											_url = '<?php echo URL;?>document/?ac=commitDocument&file_id=' + file_id ;
											_url = _url + '&name=' + encodeURIComponent(names<?php echo $pageIndex; ?>[file_index<?php echo $pageIndex; ?>] + extension);
											_url = _url + '&rel_id=<?php echo $rel_id; ?>';
											_url = _url + '&extension=' + extension;
											
											loadPage('pnFileManager<?php echo $pageIndex; ?>', _url, function(status, message)
											{
												if(status  == 0)
												{
													if(message == "OK")
													{
														file_index<?php echo $pageIndex; ?> += 1;
														if(files<?php echo $pageIndex; ?>.length>file_index<?php echo $pageIndex; ?>)
														{
															upByFile<?php echo $pageIndex; ?>();
														}
														else{
															loadDocument<?php echo $pageIndex;?>();
														}
													}else
													{
														alert(message);
													}
												}
											}, true);
											
										}
									}else
									{
										alert(message);
									}
								}
								
							});
						}
						
					  }
					};
					
					readBytes<?php echo $pageIndex; ?>(file, start, stop);
					
				}else
				{
					alert(message);
				}
			}
		}, true);
	}
	function readBytes<?php echo $pageIndex; ?>(file, start, stop)
	{
	 
		var blob = file.slice(start, stop + 1);
		reader<?php echo $pageIndex; ?>.readAsArrayBuffer(blob);
	}
	</script>

	<?php
}else if($ac == "del")
{
	$id = '';
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE document SET status =1, write_date=".$appSession->getTier()->getDateString().", write_uid='".$appSession->getUserInfo()->getId()."'";
	$sql = $sql." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo "OK";
}
?>
