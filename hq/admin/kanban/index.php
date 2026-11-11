<?php
require_once(ABSPATH.'api/Task.php' );
validUser($appSession);
$msg = $appSession->getTier()->createMessage();
$ac = '';
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

?>
<style>
.danger {
  border-top: 1px solid #ffdddd;
  border-right: 1px solid #ffdddd;
  border-bottom: 1px solid #ffdddd;
  border-left: 6px solid #f44336;
  padding: 4px;
  margin: 4px;
}
.success {
   border-top: 1px solid #ddffdd;
  border-right: 1px solid #ddffdd;
  border-bottom: 1px solid #ddffdd;
  border-left: 6px solid #04AA6D;
  padding: 4px;
  margin: 4px;
}

.info {
  border-top: 1px solid #e7f3fe;
  border-right: 1px solid #e7f3fe;
  border-bottom: 1px solid #e7f3fe;
  border-left: 6px solid #2196F3;
  padding: 4px;
  margin: 4px;
}
.warning {
  border-top: 1px solid #ffffcc;
  border-right: 1px solid #ffffcc;
  border-bottom: 1px solid #ffffcc;
  border-left: 6px solid #ffeb3b;
  padding: 4px;
  margin: 4px;
}

#board {
	display: table;
	margin: 0;
	padding: 0;
	border-spacing: 5px;
}
.section {
	display: table-cell;
	margin: 0;
	border: 1px solid #666;
	padding: 5px;
	width: 300px;
}
.section.droppable {
	border: 1px dashed #666;
}
.section > h1 {
	margin: 0;
	border-bottom: 1px solid #999;
	padding: 0;
	font-size: 12pt;
	text-align: center;
}
.card {
	vertical-align: top;
	margin: 10px 5px;
	padding: 10px;
	cursor: move;
	box-shadow: 1px 1px 1px #eee;
}
</style>
<div class="row">
	<div class="col m12">
		<a href="javascript:addColumn()"><i class="icon-plus"></i></a>
	</div>
</div>
<div class="row">
	<div class="col m12" id="pnTaskList">
	</div>
</div>
<script>
	function loadUserTask()
	{
		var _url = "<?php echo URL;?>admin/kanban/?ac=list";
		loadPage("pnTaskList", _url, function(status, message)
		{
			if(status== 0)
			{
				
				
			}
			
		}, false);
	}
	function addColumn()
	{
		var name = prompt("Please enter name", "");

		if (name == null || name == "") {
			return;
		} 
		var _url = "<?php echo URL;?>admin/kanban/?ac=addStatus&name="+ encodeURIComponent(name);
		loadPage("pnTask", _url, function(status, message)
		{
			if(status== 0)
			{
				loadUserTask();
			}
			
		}, true);
		
	}
	loadUserTask();
	function allowDrop(ev) {
	  ev.preventDefault();
	}

	function drag(ev) {
		
	  ev.dataTransfer.setData("text", ev.target.id);
	}

	function drop(ev) {
	  ev.preventDefault();
	  var assign_id = ev.dataTransfer.getData("text");
	  var status_id = ev.target.id;
	  var _url = "<?php echo URL;?>admin/kanban/?ac=updateAssignStatus&assign_id="+ assign_id+ "&status_id=" + status_id;
	 
		loadPage("pnTask", _url, function(status, message)
		{
			if(status== 0)
			{
				loadUserTask();
			}
			
		}, true);
	}
</script>
<?php
}else if($ac == "list")
{

	$ids = "d1.rel_id ='".$appSession->getConfig()->getProperty("user_id")."'";
	$sql = "SELECT d1.company_id, d2.department_id, d2.id, d3.rel_id FROM res_user_company d1 LEFT OUTER JOIN hr_employee d2 ON(d1.employee_id = d2.id AND d2.status =0) LEFT OUTER JOIN hr_employee_rel d3 ON(d2.id = d3.employee_id AND d3.status =0) WHERE d1.user_id='".$appSession->getConfig()->getProperty("user_id")."' AND d1.status =0";

	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	if(count($values)>0)
	{
		if($values[0][0] != "")
		{
			$ids = $ids." OR d1.resource_id='".$values[0][0]."'";
		}
		if($values[0][1] != "")
		{
			$ids = $ids." OR d1.resource_id='".$values[0][1]."'";
		}
		if($values[0][2] != "")
		{
			$ids = $ids." OR d1.resource_id='".$values[0][2]."'";
		}
		if($values[0][3] != "")
		{
			$ids = $ids." OR d1.resource_id='".$values[0][3]."'";
		}
		
	}


	$sql = "SELECT d1.id, d2.description, d2.start_date, d2.end_date, d1.proccess, d1.start_work, d1.end_work, d2.params, d2.rel_id, d3.status_id FROM res_assign d1 LEFT OUTER JOIN mrp_workorder_routing d2 ON(d1.rel_id = d2.id) LEFT OUTER JOIN res_assign_status_log d3 ON(d1.id = d3.assign_id AND d3.status =0) WHERE d1.status =0";
	$sql = $sql." AND (".$ids.")";
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	

	$sql = "SELECT id, name FROM res_assign_status WHERE status =0 AND create_uid='".$appSession->getConfig()->getProperty("user_id")."'";
	$msg->add("query", $sql);
	$statusList = $appSession->getTier()->getArray($msg);
	function buildItem($row)
	{
		return $row->getString("description");
	}
	
?>



<div class="responsive" id="board">
	<div class="section" id="" ondrop="drop(event)" ondragover="allowDrop(event)">
	<h1>To Do</h1>
	<?php
	for($i =0; $i<$dt->getRowCount(); $i++)
	{
		if($dt->getString($i, "status_id") == "")
		{
			$proccess = $dt->getString($i, "proccess");
			if($proccess == "1")
			{
				$proccess = "info";
			}else if($proccess == "2")
			{
				$proccess = "success";
			}else{
				$proccess = "danger";
			}
		?>
		<div class="<?php echo $proccess;?> card" id="<?php echo $dt->getRow($i)->getString("id");?>" draggable="true" ondragstart="drag(event)"><?php echo buildItem($dt->getRow($i));?></div>
		<?php
			
		}
	?>
	<?php
	}
	?>
	</div>
	
	<?php
	for($c =0; $c<count($statusList); $c++)
	{
		
	?>
	<div class="section" id="<?php echo $statusList[$c][0];?>"  ondrop="drop(event)" ondragover="allowDrop(event)">
		<h1><?php echo $statusList[$c][1];?></h1>
		<?php
		for($i =0; $i<$dt->getRowCount(); $i++)
		{
			$proccess = $dt->getString($i, "proccess");
			if($proccess == "1")
			{
				$proccess = "info";
			}else if($proccess == "2")
			{
				$proccess = "success";
			}else{
				$proccess = "danger";
			}
			if($dt->getString($i, "status_id") == $statusList[$c][0])
			{
				?>
		<div class="<?php echo $proccess;?> card"  id="<?php echo $dt->getRow($i)->getString("id");?>" draggable="true" ondragstart="drag(event)"><?php echo buildItem($dt->getRow($i));?></div>
		<?php
			}
		}
		?>
	</div>
	<?php
	}
	?>
</div>
<script>
var cards = document.querySelectorAll('.card');
for (var i = 0, n = cards.length; i < n; i++) {
	var card = cards[i];
	card.draggable = true;
};

var board = document.getElementById('board');

var hideMe;

board.onselectstart = function(e) {
	e.preventDefault();
}

board.ondragstart = function(e) {
	console.log('dragstart');
	hideMe = e.target;
	e.dataTransfer.setData('card', e.target.id);
	e.dataTransfer.effectAllowed = 'move';
};

board.ondragend = function(e) {
	e.target.style.visibility = 'visible';
};

var lastEneterd;

board.ondragenter = function(e) {
	console.log('dragenter');
	if (hideMe) {
		hideMe.style.visibility = 'hidden';
		hideMe = null;
	}
	// Save this to check in dragleave.
	lastEntered = e.target;
	var section = closestWithClass(e.target, 'section');
	// TODO: Check that it's not the original section.
	if (section) {
		section.classList.add('droppable');
		e.preventDefault(); // Not sure if these needs to be here. Maybe for IE?
		return false;
	}
};

board.ondragover = function(e) {
	// TODO: Check data type.
	// TODO: Check that it's not the original section.
	if (closestWithClass(e.target, 'section')) {
		e.preventDefault();
	}
};

board.ondragleave = function(e) {
	// FF is raising this event on text nodes so only check elements.
	if (e.target.nodeType === 1) {
		// dragleave for outer elements can trigger after dragenter for inner elements
		// so make sure we're really leaving by checking what we just entered.
		// relatedTarget is missing in WebKit: https://bugs.webkit.org/show_bug.cgi?id=66547
		var section = closestWithClass(e.target, 'section');
		if (section && !section.contains(lastEntered)) {
			section.classList.remove('droppable');
		}
	}
	lastEntered = null; // No need to keep this around.
};

board.ondrop = function(e) {
	var section = closestWithClass(e.target, 'section');
	var id = e.dataTransfer.getData('card');
	if (id) {
		var card = document.getElementById(id);
		// Might be a card from another window.
		if (card) {
			if (section !== card.parentNode) {
				section.appendChild(card);
			}
		} else {
			alert('couldn\'t find card #' + id);
		}
	}
	section.classList.remove('droppable');
	e.preventDefault();
};

function closestWithClass(target, className) {
	while (target) {
		if (target.nodeType === 1 &&
			target.classList.contains(className)) {
			return target;
		}
		target = target.parentNode;
	}
	return null;
}
</script>
<?php
}else if($ac == "addStatus")
{
	$name = '';
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	$builder = $appSession->getTier()->createBuilder("res_assign_status");
	$id = $appSession->getTool()->getId();
	$builder->add("id", $appSession->getTool()->getId());
	$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
	$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
	$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("name", $name);
	$builder->add("status", 0);
	$sql = $appSession->getTier()->getInsert($builder);
	$msg->add("query", $sql);
	$r = $appSession->getTier()->exec($msg);
	echo $id;
}else if($ac == "updateAssignStatus")
{
	$status_id = '';
	if(isset($_REQUEST['status_id']))
	{
		$status_id = $_REQUEST['status_id'];
	}
	$assign_id = '';
	if(isset($_REQUEST['assign_id']))
	{
		$assign_id = $_REQUEST['assign_id'];
	}
	$sql = "UPDATE res_assign_status_log SET status =1 WHERE assign_id='".$assign_id."' AND status =0";
	$msg->add("query", $sql);
	$r = $appSession->getTier()->exec($msg);
	if($status_id != "")
	{
		$sql = "SELECT id FROM res_assign_status_log WHERE assign_id='".$assign_id."'";
		$msg->add("query", $sql);
		$log_id = $appSession->getTier()->getValue($msg);
		if($log_id != "")
		{
			$sql = "UPDATE res_assign_status_log SET status =0, status_id='".$status_id."' WHERE id='".$log_id."'";
			$msg->add("query", $sql);
			$r = $appSession->getTier()->exec($msg);
		}else{
			$builder = $appSession->getTier()->createBuilder("res_assign_status_log");
			$id = $appSession->getTool()->getId();
			$builder->add("id", $id);
			$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
			$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
			$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
			$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
			$builder->add("status_id", $status_id);
			$builder->add("assign_id", $assign_id);
			$builder->add("status", 0);
			$sql = $appSession->getTier()->getInsert($builder);
			$msg->add("query", $sql);
			$r = $appSession->getTier()->exec($msg);
		}
	}
	echo "OK";
}
?>