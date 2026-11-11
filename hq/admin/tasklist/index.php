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
	$fdate = date('Y-m-d');
	$tdate = date('Y-m-d');

$sql = "SELECT id, name FROM res_assign_status WHERE status =0 AND create_uid='".$appSession->getConfig()->getProperty("user_id")."'";
$msg->add("query", $sql);
$statusList = $appSession->getTier()->getArray($msg);
$cview = 'contentView';
if(isset($_REQUEST['cview']))
{
	$cview = $_REQUEST['cview'];
}
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
	min-width: 300px;
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

</style>
<div class="row">
	<div class="col m12" id="pnTaskListContent">
		<div class="row">
			<div class="col m6">
				<table border=0>
				<td><a href="javascript:addTask()"><i class="icon-plus"></i> Task </a> </td>
				<td><div id="reportrange" style="cursor: pointer; width: 100%"><span></span> <img src="<?php echo URL;?>assets/img/menu_down.png" border="0" />
				</div></td>
				</table>
				
				
			</div>
			<div class="col m6 right-align">
				
				<a href="javascript:addColumn()"><i class="icon-plus"></i> Column </a>
			</div>
		</div>
		<div class="row">
			<div class="col m12" id="pnTaskList" style="overflow-x: auto">
			</div>
		</div>
	</div>
</div>
<script>
	function loadUserTask()
	{
		var _url = "<?php echo URL;?>admin/tasklist/?ac=list&fdate=" + encodeURIComponent(fdate) + '&tdate=' + encodeURIComponent(tdate);;
		loadPage("pnTaskList", _url, function(status, message)
		{
			if(status== 0)
			{
				
				
			}
			
		}, false);
	}
		
	var fdate = '<?php echo $fdate;?>';
	var tdate = '<?php echo $tdate;?>';
	$( document ).ready(function() {
		var start = moment();
		var end = moment();

		function cb(start, end) {
			if(start != null)
			{
				$('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
				fdate = start.format('YYYY-MM-D') + ' 00:00:00';
				tdate = end.format('YYYY-MM-D')+ ' 23:59:59';
			}else{
				$('#reportrange span').html(null);
			}
			
			loadUserTask();
		}

		$('#reportrange').daterangepicker({
			startDate: start,
			endDate: end,
			ranges: {
			   ' ': [null, null],
			   'Today': [moment(), moment()],
			   'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
			   'Last 7 Days': [moment().subtract(6, 'days'), moment()],
			   'Last 30 Days': [moment().subtract(29, 'days'), moment()],
			   'This Month': [moment().startOf('month'), moment().endOf('month')],
			   'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
			}
		}, cb);

		cb(start, end);
	});

	
	function addColumn()
	{
		var name = prompt("Please enter name", "");

		if (name == null || name == "") {
			return;
		} 
		var _url = "<?php echo URL;?>admin/tasklist/?ac=addStatus&name="+ encodeURIComponent(name);
		loadPage("pnTask", _url, function(status, message)
		{
			if(status== 0)
			{
				loadUserTask();
			}
			
		}, true);
		
	}
	
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
	  
		if(status_id == "todo")
		{
			taskProccess(assign_id, 0);
			return;
		}
		if(status_id == "doing")
		{
			taskProccess(assign_id, 1);
			return;
		}
		if(status_id == "done")
		{
			taskProccess(assign_id, 2);
			return;
		}
		
		var _url = "<?php echo URL;?>admin/tasklist/?ac=updateAssignStatus&assign_id="+ assign_id+ "&status_id=" + status_id;
		loadPage("pnTask", _url, function(status, message)
		{
			if(status== 0)
			{
				
				loadUserTask();
			}
			
		}, true);
	}
	function taskProccess(assign_id, proccess)
	{
		_url = "<?php echo URL;?>admin/tasklist/?ac=updateProccess&assign_id="+ assign_id+ "&proccess=" + proccess;
		
		loadPage("pnTask", _url, function(status, message)
		{
			if(status== 0)
			{
				
				loadUserTask();
			}
			
		}, true);
	}
	function taskStatus(assign_id, status_id)
	{
		 var _url = "<?php echo URL;?>admin/tasklist/?ac=updateAssignStatus&assign_id="+ assign_id+  "&status_id=" + status_id;
		
		loadPage("pnTask", _url, function(status, message)
		{
			if(status== 0)
			{
				
				loadUserTask();
			}
			
		}, true);
	}
	function resAssignStatus(assign_id, status_id, has_description, proccess)
	{
		var description = "";
		if(has_description == "1")
		{
			description = prompt("Please enter description", "");
			if (description == null || description == "") {
				return;
			} 
		}

		
		 var _url = "<?php echo URL;?>admin/tasklist/?ac=resAssignStatus&assign_id="+ assign_id+  "&status_id=" + status_id;
		 _url = _url + "&description=" + encodeURIComponent(description);
		 _url = _url + "&proccess=" + encodeURIComponent(proccess);
		

		loadPage("pnTask", _url, function(status, message)
		{
			
			if(status== 0)
			{
				todo();
				
			}
			
		}, true);
	}
	function addTask()
	{
		var _url = '<?php echo URL;?>admin/tasklist/action/?ac=form&rel_id=<?php echo $appSession->getUserInfo()->getId();?>&type=&func=loadUserTask';
		
		openPopup(_url);
	}
	function editTask(id)
	{
		var _url = '<?php echo URL;?>admin/tasklist/action/?ac=form&id=' + id;
		openPopup(_url);
	}

	addListener("project_task", function(from, att){
		
		if(att.getProperty("name") == "res_assign"){
			if(att.getProperty("session_id") != "<?php echo $appSession->getSessionId();?>"){
				loadUserTask();
			}
			
		}
	});
	var statusList=new Array();
	<?php
	for($i =0; $i<count($statusList); $i++)
	{
	?>
	statusList.push(new Array('<?php echo $statusList[$i][0];?>', '<?php echo $statusList[$i][1];?>'));
	<?php
	}
	?>
	function itemClick(id, proccess, status_id, task_id, resource_id)
	{
		var ctr = document.getElementById('c' + id);
		if(ctr != null)
		{
			var s = "";
			s = s + '<a  class="right-align" style="padding-top:10px" href="javascript:itemClose(\'' + id + '\')">';
			s = s + '<i class="icon-close"></i></a>';
			if(resource_id == '<?php echo $appSession->getUserInfo()->getId();?>')
			{
				s = s + '<a href="javascript:itemClose(\'' + id + '\'); editTask(\'' + task_id + '\');itemClose(\'' + id + '\')">';
				s = s + '<i class="icon-pencil"></i> Edit</a>';
			}
			s = s + '<a href="javascript:itemClose(\'' + id + '\');notificationLine(\'' + id + '\');">';
			s = s + '<i class="icon-check"></i> View</a>';
			
			for(var i=0; i<statusList.length; i++)
			{
				if(status_id == statusList[i][0])
				{
					continue;
				}
				s = s + '<a href="javascript:taskStatus(\'' + id + '\', \'' + statusList[i][0]+'\')" >';
				s = s + '<i class="icon-arrow-right"></i> ' + statusList[i][1] + '</a>';
			}
		
			if(proccess == "" || proccess == "0")
			{
				s = s + '<a href="javascript:itemClose(\'' + id + '\');taskProccess(\'' + id + '\', 1)" >';
				s = s + '<i class="icon-arrow-right"></i> Doing</a>';
				s = s + '<a href="javascript:itemClose(\'' + id + '\'); taskProccess(\'' + id + '\', 2)" >';
				s = s + '<i class="icon-arrow-right"></i> Done</a>';
			}
			if(proccess == "1")
			{
				s = s + '<a href="javascript:itemClose(\'' + id + '\'); taskProccess(\'' + id + '\', 0)" >';
				s = s + '<i class="icon-arrow-right"></i> Todo</a>';
				s = s + '<a href="javascript:itemClose(\'' + id + '\'); taskProccess(\'' + id + '\', 2)" >';
				s = s + '<i class="icon-arrow-right"></i> Done</a>';
			}
			if(proccess == "2")
			{
				s = s + '<a href="javascript:itemClose(\'' + id + '\'); taskProccess(\'' + id + '\', 0)" >';
				s = s + '<i class="icon-arrow-right"></i> Todo</a>';
				s = s + '<a href="javascript:itemClose(\'' + id + '\'); taskProccess(\'' + id + '\', 1)" >';
				s = s + '<i class="icon-arrow-right"></i> Doing</a>';
			}
			
			
			ctr.innerHTML = s;
		
			ctr.style.display = "block";
		}
	}
	function itemClose(id)
	{
		var ctr = document.getElementById('c' + id);
		if(ctr != null)
		{
			ctr.style.display = "none";
		}
	}
	function notificationLine(id)
	{ 
		var _url = "<?php echo URL;?>admin/tasklist/form/?id=" + id;
		loadPage("pnTaskListContent", _url, function(status, message)
		{
			if(status== 0)
			{
				
				
			}
			
		}, false);
	}
</script>
<?php
}else if($ac == "list")
{
	$fdate = date("Y-m-d 00:00:00");
	if(isset($_REQUEST['fdate']))
	{
		$fdate = $_REQUEST['fdate'];
	}
	$tdate = date("Y-m-d 23:59:59");
	if(isset($_REQUEST['fdate']))
	{
		$fdate = $_REQUEST['fdate'];
	}
	$ids = "d1.resource_id='".$appSession->getUserInfo()->getId()."'";
	$sql = "SELECT d1.company_id, d2.department_id, d2.id, d3.rel_id FROM res_user_company d1 LEFT OUTER JOIN hr_employee d2 ON(d1.employee_id = d2.id AND d2.status =0) LEFT OUTER JOIN hr_employee_rel d3 ON(d2.id = d3.employee_id AND d3.status =0) WHERE d1.user_id='".$appSession->getConfig()->getProperty("user_id")."' AND d1.status =0";
	
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	for($i =0; $i<count($values); $i++)
	{
		if($values[$i][0] != "")
		{
			$ids = $ids." OR d1.resource_id='".$values[$i][0]."'";
		}
		if($values[$i][1] != "")
		{
			$ids = $ids." OR d1.resource_id='".$values[$i][1]."'";
		}
		if($values[$i][2] != "")
		{
			$ids = $ids." OR d1.resource_id='".$values[$i][2]."'";
		}
		if($values[$i][3] != "")
		{
			$ids = $ids." OR d1.resource_id='".$values[$i][3]."'";
		}
		
	}


	$sql = "SELECT d1.id, d2.receipt_no, d2.name, d2.start_date, d2.end_date, d1.proccess, d1.start_work, d1.end_work, d1.rel_id, d1.module_id, d1.resource_id, d4.name AS priority_name FROM res_assign d1 LEFT OUTER JOIN project_task d2 ON(d1.rel_id = d2.id) LEFT OUTER JOIN project_priority d4 ON(d2.priority_id= d4.id) LEFT OUTER JOIN project_proccess d5 ON(d2.proccess_id = d5.id) WHERE d1.status =0";
	$sql = $sql." AND d2.start_date>='".$fdate."'";
	$sql = $sql." AND d2.start_date<='".$tdate."'";
	$sql = $sql." AND (".$ids.")";
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);

	
	
	$sql = "SELECT id, name FROM res_assign_status WHERE status =0 AND create_uid='".$appSession->getConfig()->getProperty("user_id")."'";
	$msg->add("query", $sql);
	$statusList = $appSession->getTier()->getArray($msg);
	function buildItem($appSession, $row)
	{
		$id = $row->getString("id");
		$receipt_no = $row->getString("receipt_no");
		$proccess = $row->getString("proccess");
		$status_id = $row->getString("status_id");
		$resource_id = $row->getString("resource_id");
		$start_date = $row->getString("start_date");
		$end_date = $row->getString("end_date");
		$priority_name = $row->getString("priority_name");
		$proccess_name = $row->getString("proccess_name");
		$rel_id = $row->getString("rel_id");
		$start_date = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($start_date));
		$end_date = $appSession->getFormats()->getDATE()->formatDate($appSession->getTool()->toDateTime($end_date));
		
		$s = '<span class="dropdown-click right-align"><a href="javascript:itemClick(\''.$id.'\', \''.$proccess.'\', \''.$status_id.'\', \''.$rel_id.'\', \''.$resource_id.'\')"><i class="icon-options-vertical"></i></a></span>';
		$s = $s.'<div class="dropdown-content" style="right:0; text-align:left" id="c'.$id.'">';
		$s = $s.'</div>';
		$s= $s."<a href=\"javascript:notificationLine('".$id."')\">".$receipt_no.". ".$row->getString("name")."</a>";
		$s = $s."".$start_date."  > ".$end_date." | ".$priority_name." | ".$proccess_name;
		return $s;
	}
	
?>



<div id="board">
	<div class="section" id="todo" ondrop="drop(event)" ondragover="allowDrop(event)">
	<h1>To Do</h1>
	<?php
	for($i =0; $i<$dt->getRowCount(); $i++)
	{
		if($dt->getString($i, "status_id") == "")
		{
			$proccess = $dt->getString($i, "proccess");
			if($proccess == "" || $proccess == "0")
			{
				$proccess = "info";
		?>
		<div class="<?php echo $proccess;?> card" id="<?php echo $dt->getRow($i)->getString("id");?>" draggable="true" ondragstart="drag(event)"><?php echo buildItem($appSession, $dt->getRow($i));?></div>
		<?php
			}
		}
	?>
	<?php
		
	}
	?>
	</div>
	<div class="section" id="doing" ondrop="drop(event)" ondragover="allowDrop(event)">
	<h1>Doing</h1>
	<?php
	for($i =0; $i<$dt->getRowCount(); $i++)
	{
		if($dt->getString($i, "status_id") == "")
		{
			$proccess = $dt->getString($i, "proccess");
			if($proccess == "1")
			{
				$proccess = "warning";
			
		?>
		<div class="<?php echo $proccess;?> card" id="<?php echo $dt->getRow($i)->getString("id");?>" draggable="true" ondragstart="drag(event)"><?php echo buildItem($appSession, $dt->getRow($i));?></div>
		<?php
			}
		}
	?>
	<?php
	}
	?>
	</div>
	
	<div class="section" id="done" ondrop="drop(event)" ondragover="allowDrop(event)">
	<h1>Done</h1>
	<?php
	for($i =0; $i<$dt->getRowCount(); $i++)
	{
		if($dt->getString($i, "status_id") == "")
		{
			$proccess = $dt->getString($i, "proccess");
			if($proccess == "2")
			{
				$proccess = "success";
			
		?>
		<div class="<?php echo $proccess;?> card" id="<?php echo $dt->getRow($i)->getString("id");?>" draggable="true" ondragstart="drag(event)"><?php echo buildItem($appSession, $dt->getRow($i));?></div>
		<?php
			
			}
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
		<div class="<?php echo $proccess;?> card"  id="<?php echo $dt->getRow($i)->getString("id");?>" draggable="true" ondragstart="drag(event)"><?php echo buildItem($appSession, $dt->getRow($i));?></div>
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
}else if($ac == "updateProccess")
{
	$id = '';
	if(isset($_REQUEST['assign_id']))
	{
		$id = $_REQUEST['assign_id'];
	}
	$proccess = '0';
	if(isset($_REQUEST['proccess']))
	{
		$proccess = $_REQUEST['proccess'];
	}
	
	$task = new Task();
	echo $task->updateAssignProccess($appSession, $id, $proccess);
	
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
	$task = new Task();
	echo $task->updateAssignStatus($appSession, $assign_id, $status_id);
}else if($ac == "resAssignStatus")
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
	$description = '';
	if(isset($_REQUEST['description']))
	{
		$description = $_REQUEST['description'];
	}
	$proccess = '';
	if(isset($_REQUEST['proccess']))
	{
		$proccess = $_REQUEST['proccess'];
	}
	if($proccess == "")
	{
		$proccess = "0";
	}
	$task = new Task();
	echo $task->updateResAssignStatus($appSession, $assign_id, $status_id, $description, $proccess);
}
else if($ac == "rel")
{
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	$type = '';
	if(isset($_REQUEST['type']))
	{
		$type = $_REQUEST['type'];
	}
	$module_id = '';
	if(isset($_REQUEST['module_id']))
	{
		$module_id = $_REQUEST['module_id'];
	}
?>
<span id="pnTaskRelView"></span><a href="javascript:addTask();"><i class="icon-plus"></i></a>
<script>
function loadTask()
{
	var _url = "<?php echo URL;?>admin/tasklist/?ac=rel_list&rel_id=<?php echo $rel_id;?>&module_id=<?php echo $module_id;?>";
	loadPage("pnTaskRelView", _url, function(status, message)
	{
		if(status== 0)
		{
			
			
		}
		
	}, false);
}
loadTask();
function addTask()
{
	var _url = '<?php echo URL;?>admin/tasklist/action/?ac=form&rel_id=<?php echo $rel_id;?>&module_id=<?php echo $module_id;?>&type=<?php echo $type;?>&func=loadTask';

	openPopup(_url);
}
function editTask(id)
{
	var _url = '<?php echo URL;?>admin/tasklist/action/?ac=form&rel_id=<?php echo $rel_id;?>&type=<?php echo $type;?>&id=' + id;
	openPopup(_url);
}


function delTask(id)
{
	var result = confirm("Want to delete?");
	if (!result) {
		return;
	}
	var _url = '<?php echo URL;?>admin/task/action/?ac=delTask&id=' + id;

	loadPage('pnTask', _url, function(status, message)
	{
		if(status== 0)
		{
			if(message.indexOf("OK")!=-1)
			{
				loadTask();
			}else{
				alert(message);
			}
			
		}
		
	}, true);
}
</script>

<?php
}else if($ac == "rel_list")
{
	$rel_id = '';
	if(isset($_REQUEST['rel_id']))
	{
		$rel_id = $_REQUEST['rel_id'];
	}
	
	
	$sql = "SELECT d1.id, d1.receipt_no, d1.name, d1.start_date, d1.end_date FROM project_task d1 WHERE d1.rel_id='".$rel_id."'";
	$sql = $sql." AND d1.status =0 ORDER BY d1.sequence ASC, d1.start_date ASC";
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	for($i =0; $i<count($values); $i++)
	{
	?>
	<a href="javascript:editTask('<?php echo $values[$i][0];?>');"><?php echo $values[$i][1];?>. <?php echo $values[$i][2];?></a>
	<?php
	}
}
?>