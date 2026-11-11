<?php
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

	$arr_params = explode('&', $params);
	$rel_id = "";
	for($p=0; $p<count($arr_params); $p++)
	{
		$arrItem = explode('=', $arr_params[$p]);
		if(count($arrItem)>1)
		{
			if($arrItem[0] == "id")
			{
				$rel_id = $arrItem[1];
			}
		}
	}
	
	$sql = "SELECT id, name FROM mrp_workcenter WHERE status =0 AND (company_id='".$appSession->getConfig()->getProperty("company_id")."' OR company_id='".$appSession->getConfig()->getProperty("parent_company_id")."') ORDER BY sequence ASC";
	$msg->add("query", $sql);
	$values = $appSession->getTier()->getArray($msg);
	
	$sql = "SELECT d1.id, d1.name, d1.description, d2.name AS workcenter_name FROM mrp_workcenter_line_flow d1 LEFT OUTER JOIN mrp_workcenter d2 ON(d1.workcenter_id = d2.id) WHERE d1.line_id='".$rel_id."' AND d1.status =0 AND d2.id IS NOT NULL";
	$msg->add("query", $sql);
	
	$nodes = $appSession->getTier()->getArray($msg);
	
	$sql = "SELECT d1.id, d1.name, d1.rel_id, lg.description  FROM ir_module d1 LEFT OUTER JOIN ir_module d2 ON(d1.rel_id = d2.id) LEFT OUTER JOIN res_lang_line lg ON(lg.lang_id='".$appSession->getConfig()->getProperty("lang_id")."' AND lg.rel_id = d1.id AND lg.name='module_name' AND lg.status =0) WHERE d1.status =0 AND d1.grouped = 1 ";
	if($root_module_id != "")
	{
		$sql = $sql." AND (d1.parent_id='".$module_id."' OR d1.parent_id='".$root_module_id."')";
	}else{
		$sql = $sql." AND d1.parent_id='".$module_id."'";
	}
	$sql = $sql." ORDER BY d1.sequence ASC";
	
	$msg->add("query", $sql);
	$result_grouped = $appSession->getTier()->getArray($msg);
	

?>
<style>
:root {
  --border-color: #cacaca;
  --background-color: #ffffff;

  --background-box-title: #f7f7f7;
}


.drag-drawflow {
  line-height: 50px;
  border-bottom: 1px solid var(--border-color);
  padding-left: 20px;
  cursor: move;
  user-select: none;
}

#drawflow {
  width: 100%;
  height: 100%;
  top: 40px;
  background: var(--background-color);
  background-size: 25px 25px;
  background-image:
   linear-gradient(to right, #f1f1f1 1px, transparent 1px),
   linear-gradient(to bottom, #f1f1f1 1px, transparent 1px);
}


/* Editing Drawflow */

.drawflow .drawflow-node {
  background: var(--background-color);
  border: 1px solid var(--border-color);
  -webkit-box-shadow: 0 2px 15px 2px var(--border-color);
  box-shadow: 0 2px 15px 2px var(--border-color);
  padding: 0px;
  width: 200px;
}

.drawflow .drawflow-node.selected  {
  background: white;
  border: 1px solid #4ea9ff;
  -webkit-box-shadow: 0 2px 20px 2px #4ea9ff;
  box-shadow: 0 2px 20px 2px #4ea9ff;
}

.drawflow .drawflow-node.selected .title-box {
  color: #22598c;
  /*border-bottom: 1px solid #4ea9ff;*/
}

.drawflow .connection .main-path {
  stroke: #4ea9ff;
  stroke-width: 3px;
}

.drawflow .drawflow-node .input, .drawflow .drawflow-node .output {
  height: 15px;
  width: 15px;
  border: 2px solid var(--border-color);
}

.drawflow .drawflow-node .input:hover, .drawflow .drawflow-node .output:hover {
  background: #4ea9ff;
}

.drawflow .drawflow-node .output {
  right: 10px;
}

.drawflow .drawflow-node .input {
  left: -10px;
  background: white;
}

.drawflow > .drawflow-delete {
  border: 2px solid #43b993;
  background: white;
  color: #43b993;
  -webkit-box-shadow: 0 2px 20px 2px #43b993;
  box-shadow: 0 2px 20px 2px #43b993;
}

.drawflow-delete {
  border: 2px solid #4ea9ff;
  background: white;
  color: #4ea9ff;
  -webkit-box-shadow: 0 2px 20px 2px #4ea9ff;
  box-shadow: 0 2px 20px 2px #4ea9ff;
}

.drawflow-node .title-box {
  height: 50px;
  line-height: 50px;
  background: var(--background-box-title);
  border-bottom: 1px solid #e9e9e9;
  border-radius: 4px 4px 0px 0px;
  padding-left: 10px;
}
.drawflow .title-box svg {
  position: initial;
}
.drawflow-node .box {
  padding: 10px 20px 20px 20px;
  font-size: 14px;
  color: #555555;

}
.drawflow-node .box p {
  margin-top: 5px;
  margin-bottom: 5px;
}

.drawflow-node.welcome {
  width: 250px;
}

.drawflow-node.slack .title-box {
  border-radius: 4px;
}

.drawflow-node input, .drawflow-node select, .drawflow-node textarea {
  border-radius: 4px;
  border: 1px solid var(--border-color);
  height: 30px;
  line-height: 30px;
  font-size: 16px;
  width: 158px;
  color: #555555;
}

.drawflow-node textarea {
  height: 100px;
}


.drawflow-node.personalized {
  background: red;
  height: 200px;
  text-align: center;
  color: white;
}
.drawflow-node.personalized .input {
  background: yellow;
}
.drawflow-node.personalized .output {
  background: green;
}

.drawflow-node.personalized.selected {
  background: blue;
}

.drawflow .connection .point {
  stroke: var(--border-color);
  stroke-width: 2;
  fill: white;
  
}

.drawflow .connection .point.selected, .drawflow .connection .point:hover {
  fill: #4ea9ff;
}


</style>
<script src="<?php echo URL;?>assets/drawflow/drawflow.min.js"></script>
<link rel="stylesheet" href="<?php echo URL;?>assets/drawflow/drawflow.min.css">
<div class="row">
	<div class="col m12" id="pnAction">
	</div>
</div>
<div class="row">
	<div class="col m2" style="overflow:auto; max-height:550px">
	<?php
			for($i =0; $i<count($values); $i++)
			{
			?>
			 <div class="drag-drawflow" draggable="true" ondragstart="drag(event)" data-node="<?php echo $values[$i][0];?>,<?php echo $values[$i][1];?>">
				<span><?php echo $values[$i][1];?></span>
			  </div>
			<?php
			}
			?>
	
	</div>
	<div class="col m8">
		<div id="drawflow" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
	</div>
</div>


<script>
	var listModules = new Array()
	<?php
	for($i =0; $i<count($result_grouped); $i++)
	{
		$_id = $result_grouped[$i][0];
		$_rel_id = $result_grouped[$i][2];
		$_name = $result_grouped[$i][3];
		if($_name == "")
		{
			$_name = $result_grouped[$i][1];
		}
	?>
	listModules.push(new Array('<?php echo $_id;?>', '<?php echo $_rel_id;?>', '<?php echo $_name;?>'));
	<?php
	}
	?>
	function updateNode(node)
	{
		var description = "point=" + node.pos_x + "," + node.pos_y;
		var connections = "";
		if(node.outputs != null && node.outputs.output_1 != null && node.outputs.output_1.connections)
		{
			
			for(var i=0; i<node.outputs.output_1.connections.length; i++)
			{
				var _id = editor.getNodeFromId(node.outputs.output_1.connections[i].node).name;
				if(connections != "")
				{
					connections = connections + ",";
				}
				connections = connections + _id;
			}
		}
		description = description + "&connections=" + connections;
		var _url = "<?php echo URL;?>admin/workcenter_line/?ac=updateDescription&id="+ node.name;
		_url = _url + "&description=" + encodeURIComponent(description);
		loadPage("pnTask", _url, function(status, message)
		{
			if(status== 0)
			{
				
			}
			
		}, true);
	}
	function findNode(ids, id)
	{
		for(var i =0; i<arrIDList.length; i++)
		{
			var f = editor.getNodeFromId(arrIDList[i]);
			if(f != null && f.name == id)
			{
				return f;
			}
		}
		return null;
	}
    var id = document.getElementById("drawflow");
    var editor = new Drawflow(id);
    editor.reroute = true;
	var arrIDList = new Array();
    editor.start();
	 <?php
	$config = new AppConfig("");
	for($i =0; $i<count($nodes); $i++)
	{
		$_id = $nodes[$i][0];
		$_name = $nodes[$i][1]. " (".$nodes[$i][3].")";
		$config->clear();
		$config->parseWith($nodes[$i][2], "&");
		$point = $config->getProperty("point");
		$index = $appSession->getTool()->indexOf($point, ",");
		$x = 100;
		$y = 100;
		if($index != -1)
		{
			$x = $appSession->getTool()->substring($point, 0,  $index);
			$y = $appSession->getTool()->substring($point, $index + 1);
		}
	?>
	arrIDList.push(editor.addNode('<?php echo $_id;?>', 1,  1, <?php echo $x;?>, <?php echo $y;?>, '', {}, '<?php echo $_name;?></div>' ));
	<?php
	}

	for($i =0; $i<count($nodes); $i++)
	{
		$_id = $nodes[$i][0];
		$config->clear();
		$config->parseWith($nodes[$i][2], "&");
		$connections = $config->getProperty("connections");
		if($connections != "")
		{
			$arr = $appSession->getTool()->split($connections, ',');
	?>
		var from = findNode(arrIDList, '<?php echo $_id;?>');
		if(from !=null)
		{
			<?php
			for($j=0; $j<count($arr); $j++)
			{
				if($arr[$j] == "")
				{
					continue;
				}
			?>
			var to = findNode(arrIDList, '<?php echo $arr[$j];?>');
			if(to != null)
			{
				 editor.addConnection(from.id, to.id, "output_1", "input_1");
			}
			<?php
			}
			?>
		}
	<?php
		}
	}
	?>
	var last_id = "";
    editor.on('nodeCreated', function(id) {
      
    })
	
	
	
    editor.on('nodeRemoved', function(id) {
     
	   var _url = "<?php echo URL;?>admin/workcenter_line/?ac=del&id="+ last_id;
		
		loadPage("pnTask", _url, function(status, message)
		{
			if(status== 0)
			{
				
			}
			
		}, true);
    })

    editor.on('nodeSelected', function(id) {
       var node = editor.getNodeFromId(id);
	   last_id = node.name;
	   var s = node.html + ": ";
	   for(var i =0; i<listModules.length; i++)
	   {
		   if(i>0)
		   {
			   s = s +  " | ";
		   }
		   s = s + '<a href="javascript:gt(\'contentView\', \'' +  listModules[i][0] + '\', \'\', \'\', \'rel_id=' + last_id + '\', \'' + listModules[i][1] + '\')">' + listModules[i][2] + '</a>';
	   }
	   var ctr = document.getElementById('pnAction').innerHTML = s;
    })

    editor.on('moduleCreated', function(name) {
      
    })

    editor.on('moduleChanged', function(name) {
      
    })

    editor.on('connectionCreated', function(connection) {
	  var id = connection.output_id.replace('"', '');
      //alert(JSON.stringify(connection.output_id));
	  var node = editor.getNodeFromId(id);
	  updateNode(node);
      
    })

    editor.on('connectionRemoved', function(connection) {
      var id = connection.output_id.replace('"', '');
	  var node = editor.getNodeFromId(id);
	  updateNode(node);
    })

    editor.on('mouseMove', function(position) {
		x = position.x;
		y = position.y;
    })

    editor.on('nodeMoved', function(id) {
			var node = editor.getNodeFromId(id);
			updateNode(node);
		}
	)

    editor.on('zoom', function(zoom) {
      console.log('Zoom level ' + zoom);
    })

    editor.on('translate', function(position) {
      console.log('Translate x:' + position.x + ' y:'+ position.y);
    })

    editor.on('addReroute', function(id) {
      console.log("Reroute added " + id);
    })

    editor.on('removeReroute', function(id) {
      console.log("Reroute removed " + id);
    })



    var elements = document.getElementsByClassName('drag-drawflow');
    for (var i = 0; i < elements.length; i++) {
      elements[i].addEventListener('touchend', drop, false);
      elements[i].addEventListener('touchmove', positionMobile, false);
      elements[i].addEventListener('touchstart', drag, false );
    }

    var mobile_item_selec = '';
    var mobile_last_move = null;
   function positionMobile(ev) {
     mobile_last_move = ev;
   }

   function allowDrop(ev) {
      ev.preventDefault();
    }

    function drag(ev) {
      if (ev.type === "touchstart") {
        mobile_item_selec = ev.target.closest(".drag-drawflow").getAttribute('data-node');
      } else {
      ev.dataTransfer.setData("node", ev.target.getAttribute('data-node'));
      }
    }

    function drop(ev) {
      if (ev.type === "touchend") {
        var parentdrawflow = document.elementFromPoint( mobile_last_move.touches[0].clientX, mobile_last_move.touches[0].clientY).closest("#drawflow");
        if(parentdrawflow != null) {
          addNodeToDrawFlow(mobile_item_selec, mobile_last_move.touches[0].clientX, mobile_last_move.touches[0].clientY);
        }
        mobile_item_selec = '';
      } else {
        ev.preventDefault();
        var data = ev.dataTransfer.getData("node");
        addNodeToDrawFlow(data, ev.clientX, ev.clientY);
      }

    }

    function addNodeToDrawFlow(name, pos_x, pos_y) {
      if(editor.editor_mode === 'fixed') {
        return false;
      }
      pos_x = pos_x * ( editor.precanvas.clientWidth / (editor.precanvas.clientWidth * editor.zoom)) - (editor.precanvas.getBoundingClientRect().x * ( editor.precanvas.clientWidth / (editor.precanvas.clientWidth * editor.zoom)));
      pos_y = pos_y * ( editor.precanvas.clientHeight / (editor.precanvas.clientHeight * editor.zoom)) - (editor.precanvas.getBoundingClientRect().y * ( editor.precanvas.clientHeight / (editor.precanvas.clientHeight * editor.zoom)));
	  var arr = name.split(',');
	  var description = "point=" + pos_x + "," + pos_y;
	  var name = prompt("Please enter name", "");

		if (name == null || name == "") {
		 return;
		} 

	  var _url = "<?php echo URL;?>admin/workcenter_line/?ac=addFlow&line_id=<?php echo $rel_id;?>&workcenter_id="+ arr[0] + "&description=" + encodeURIComponent(description) + "&name=" + encodeURIComponent(name);
	 
		loadPage("pnTask", _url, function(status, message)
		{
			if(status== 0)
			{
				if(message.length == 36)
				{
					editor.addNode(message, 1,  1, pos_x, pos_y, '', {}, name + "(" + arr[1] + ")" );
				}
			}
			
		}, true);
	  
	  
      
    }

  var transform = '';
  function showpopup(e) {
    e.target.closest(".drawflow-node").style.zIndex = "9999";
    e.target.children[0].style.display = "block";
    //document.getElementById("modalfix").style.display = "block";

    //e.target.children[0].style.transform = 'translate('+translate.x+'px, '+translate.y+'px)';
    transform = editor.precanvas.style.transform;
    editor.precanvas.style.transform = '';
    editor.precanvas.style.left = editor.canvas_x +'px';
    editor.precanvas.style.top = editor.canvas_y +'px';
    console.log(transform);

    //e.target.children[0].style.top  =  -editor.canvas_y - editor.container.offsetTop +'px';
    //e.target.children[0].style.left  =  -editor.canvas_x  - editor.container.offsetLeft +'px';
    editor.editor_mode = "fixed";

  }

   function closemodal(e) {
     e.target.closest(".drawflow-node").style.zIndex = "2";
     e.target.parentElement.parentElement.style.display  ="none";
     //document.getElementById("modalfix").style.display = "none";
     editor.precanvas.style.transform = transform;
       editor.precanvas.style.left = '0px';
       editor.precanvas.style.top = '0px';
      editor.editor_mode = "edit";
   }

    function changeModule(event) {
      var all = document.querySelectorAll(".menu ul li");
        for (var i = 0; i < all.length; i++) {
          all[i].classList.remove('selected');
        }
      event.target.classList.add('selected');
    }

    function changeMode(option) {

    //console.log(lock.id);
      if(option == 'lock') {
        lock.style.display = 'none';
        unlock.style.display = 'block';
      } else {
        lock.style.display = 'block';
        unlock.style.display = 'none';
      }

    }
	

  </script>
<?php
}else if($ac == "addFlow")
{
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$workcenter_id = "";
	if(isset($_REQUEST['workcenter_id']))
	{
		$workcenter_id = $_REQUEST['workcenter_id'];
	}
	$line_id = "";
	if(isset($_REQUEST['line_id']))
	{
		$line_id = $_REQUEST['line_id'];
	}
	$description = "";
	if(isset($_REQUEST['description']))
	{
		$description = $_REQUEST['description'];
	}
	$name = "";
	if(isset($_REQUEST['name']))
	{
		$name = $_REQUEST['name'];
	}
	$builder = $appSession->getTier()->createBuilder("mrp_workcenter_line_flow");
	$id = $appSession->getTool()->getId();
	$builder->add("id", $id);
	$builder->add("write_uid", $appSession->getConfig()->getProperty("user_id"));
	$builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("create_uid", $appSession->getConfig()->getProperty("user_id"));
	$builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
	$builder->add("company_id", $appSession->getConfig()->getProperty("company_id"));
	$builder->add("workcenter_id", $workcenter_id);
	$builder->add("line_id", $line_id);
	$builder->add("status", 0);
	$builder->add("description", $description);
	$builder->add("name", $name);
	$sql = $appSession->getTier()->getInsert($builder);
	$msg->add("query", $sql);
	$r = $appSession->getTier()->exec($msg);
	echo $id;
	
}else if($ac == "updateDescription")
{
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$description = "";
	if(isset($_REQUEST['description']))
	{
		$description = $_REQUEST['description'];
	}
	
	$sql = "UPDATE mrp_workcenter_line_flow SET description='".$description."', write_date=NOW() WHERE id='".$id."'";
	$msg->add("query", $sql);
	$r = $appSession->getTier()->exec($msg);
	echo "OK";
	
}else if($ac == "del")
{
	$id = "";
	if(isset($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];
	}
	$sql = "UPDATE mrp_workcenter_line_flow SET status =1, write_date=NOW() WHERE id='".$id."'";
	$msg->add("query", $sql);
	$r = $appSession->getTier()->exec($msg);
	echo "OK";
}
?>