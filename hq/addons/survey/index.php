<?php
$msg = $appSession->getTier()->createMessage();
$survey_id = "";
if(isset($_REQUEST['survey_id']))
{
	$survey_id = $_REQUEST['survey_id'];
}

$ac = "";
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "")
{
	$ac = "view";
}
$pageIndex = 0;
if(isset($_REQUEST['pageIndex']))
{
	$pageIndex = $_REQUEST['pageIndex'];
}
if($ac == "view")
{
	
?>
<a href="javascript:addSurvey<?php echo $pageIndex;?>();"><i class="icon-plus"></i></a>
<br>
<div id="pnSurveyList<?php echo $pageIndex;?>" style="padding-top:5px"></div>
<script>
function loadSurveyList<?php echo $pageIndex;?>()
{
	var _url = '<?php echo URL;?>addons/survey/?ac=list&survey_id=<?php echo $survey_id;?>&pageIndex=<?php echo $pageIndex;?>';
	loadPage('pnSurveyList<?php echo $pageIndex;?>', _url, function(status, message)
	{
		if(status== 0)
		{
			
			
		}
		
	}, false);
}

loadSurveyList<?php echo $pageIndex;?>();
function addSurvey<?php echo $pageIndex;?>()
{
	var _url = '<?php echo URL;?>addons/survey/?ac=question&survey_id=<?php echo $survey_id;?>&pageIndex=<?php echo $pageIndex;?>';
	openPopup(_url);
}
function doAddQuestion<?php echo $pageIndex;?>(id)
{
	var _url = '<?php echo URL;?>addons/survey/?ac=add&survey_id=<?php echo $survey_id;?>&pageIndex=<?php echo $pageIndex;?>&question_id=' + id;
	
	loadPage('pnSurveyList<?php echo $pageIndex;?>', _url, function(status, message)
	{
		if(status== 0)
		{
			
			loadSurveyList<?php echo $pageIndex;?>();
		}
		
	}, true);
}
function removeSurvey<?php echo $pageIndex;?>(id)
{
	var _url = '<?php echo URL;?>addons/survey/?ac=del&survey_id=<?php echo $survey_id;?>&pageIndex=<?php echo $pageIndex;?>&id=' + id;
	
	loadPage('pnMetaList<?php echo $pageIndex;?>', _url, function(status, message)
	{
		if(status== 0)
		{
			
			loadSurveyList<?php echo $pageIndex;?>();
		}
		
	}, true);
}
</script>
<?php
}else if($ac == "list")
{
	$sql = "SELECT d1.id, d2.code, d2.name, d1.sequence FROM survey_line d1 LEFT OUTER JOIN survey_question d2 ON(d1.question_id = d2.id) WHERE d1.status =0 AND d1.survey_id='".$survey_id."' ORDER BY d1.sequence ASC, d1.create_date ASC";
	
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	
	?>
	<div class="responsive">
	<table class="table">
	<tr>
		<td width="40">Code</td>
		<td >Name</td>
		<td width="20"></td>
		</tr>
		<?php
		for($i =0; $i<$dt->getRowCount(); $i++)
		{
			$_id = $dt->getString($i, "id");
			$code = $dt->getString($i, "code");
			$name = $dt->getString($i, "name");
			$sequence = $dt->getFloat($i, "sequence");
		?>
		<tr>
			<td width="30"><?php echo $code;?></td>
			<td><?php echo $name;?></td>
			<td width="100"><input type="number"  class="input" value="<?php echo $sequence;?>" onblur="saveLine('survey_line', 'sequence', '', '<?php echo $_id;?>', this, function(status, message){})"></td>
			<td width="20"><a href="javascript:removeSurvey<?php echo $pageIndex;?>('<?php echo $_id;?>')"><i class="icon-minus"></i></a></td>
		</tr>
		<?php
		}
		?>
	</table>
	</div>
	<?php
	
	
}else if($ac == "add")
{
	$question_id = "";
	if(isset($_REQUEST['question_id']))
	{
		$question_id = $_REQUEST['question_id'];
	}

	$builder = $appSession->getTier()->createBuilder("survey_line");
	$id = $appSession->getTool()->getId();
	$builder->add("id", $id);
	$builder->add("survey_id", $survey_id);
	$builder->add("question_id", $question_id);
	$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
	$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
	$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("status", 0);
	$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
	$sql = $appSession->getTier()->getInsert($builder);

	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo $id;
}else if($ac == "del")
{
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE survey_line SET status=1, write_date=".$appSession->getTier()->getDateString()." WHERE id='".$id."'";
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo "OK";
	
}else if($ac == "question")
{
?>
<div class="row padding">
	<div class="col m12">
	<div class="input-group">	
		<input type="text" class="input" placeholder="Search..." id="editpos_product_search" value="" onKeyDown="if(event.keyCode == 13){loadQuestion();}" />
		<button onclick="loadQuestion()" class="button btn-secondary"><?php echo $appSession->getLang()->find("Search");?></button>
	</div>
	</div>
	
</div>
<div class="row padding">
	<div class="col m12">
		<div id="pnQuestion"></div>
	</div>
</div>
<script>
function loadQuestion()
{
	var search = document.getElementById('editpos_product_search').value;
	
	var _url = '<?php echo URL;?>addons/survey/?ac=question_list&pageIndex=<?php echo $pageIndex;?>';
	_url = _url+ "&search=" + encodeURIComponent(search);
	loadPage('pnQuestion', _url, function(status, message)
	{
		if(status== 0)
		{
			
			
		}
		
	}, false);
}
loadQuestion();
</script>
<?php
}else if($ac == "question_list")
{
	
	$search = '';
	if(isset($_REQUEST['search']))
	{
		$search = $_REQUEST['search'];
	}
	$sql = "SELECT d1.id, d1.code, d1.name FROM survey_question d1";
	$sql = $sql." WHERE d1.status =0 AND (d1.company_id='".$appSession->getConfig()->getProperty("company_id")."' OR d1.company_id='".$appSession->getConfig()->getProperty("parent_company_id")."')";

	if($search != "")
	{
		$sql = $sql." AND (".$appSession->getTier()->buildSearch(["d1.name", "d1.code"], $search).")";
	}

	$sql = $sql." ORDER BY d1.code ASC, d1.name ASC";
	$sql = $sql." LIMIT 100";
	$msg->add("query", $sql);
	$productList = $appSession->getTier()->getTable($msg);
	?>
	<div class="row">
	<div class="col m12 responsive" >
<table class="table">
<td width="20"></td>
<td  width="40"><?php echo $appSession->getLang()->find("Code");?></td>
<td><?php echo $appSession->getLang()->find("Name");?></td>

<?php
for ($i = 0; $i < $productList->getRowCount(); $i++) {

	$id = $productList->getString($i, "id");
	$code = $productList->getString($i, "code");
	$name = $productList->getString($i, "name");

?>
<tr>
	<td><a href="javascript:doAddQuestion<?php echo $pageIndex;?>('<?php echo $id;?>')"><i class="icon-plus"></i></td>
	<td><?php echo $code;?></td>
	<td><?php echo $name;?></td>
</tr>
<?php
	}
?>
</table>
	</div>
</div>
	<?php
}
?>
