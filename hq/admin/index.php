<?php
validUser($appSession);

$appSession->getLang()->load($appSession->getTier(), "admin", $appSession->getConfig()->getProperty("lang_id"));
$company_ids = "rel_id='".$appSession->getUserInfo()->getCompanyId()."'";
$pid = $appSession->getUserInfo()->getCompanyId();


$msg = $appSession->getTier()->createMessage();

while(true)
{
	$sql = "SELECT parent_id FROM res_company WHERE status =0 AND id='".$pid."'";

	$msg->add("query", $sql);
	$result = $appSession->getTier()->getArray($msg);
	
	$numrows = count($result);	
	if($numrows>0)
	{
		$pid = $result[0][0];
		if($pid == "")
		{
			break;
		}
		$company_ids = $company_ids." OR rel_id='".$pid."'";
	}else
	{
		break;
	}
}
$sql = "SELECT module_id FROM ir_module_rel WHERE status =0 AND (".$company_ids.")";

$msg->add("query", $sql);
$result = $appSession->getTier()->getArray($msg);
$numrows = count($result);	
$rels =[];
for($i =0; $i<$numrows; $i++)
{
	$row = $result[$i];
	$module_id = $row[0];
	$rels[count($rels)] = $module_id;
	
}

	
$sql = "SELECT d1.id, d1.parent_id, d1.name, d1.icon, d2.icon AS icon_rel, d1.rel_id, lg.description FROM ir_module d1 LEFT OUTER JOIN ir_module d2 ON(d1.rel_id = d2.id) LEFT OUTER JOIN res_lang_line lg ON(lg.lang_id='".$appSession->getConfig()->getProperty("lang_id")."' AND lg.rel_id = d1.id AND lg.name='module_name' AND lg.status =0) WHERE d1.status =0 AND d1.publish=1 AND (d1.grouped=0 OR d1.grouped IS NULL) AND d1.type='' ORDER BY d1.sequence ASC";

$msg->add("query", $sql);
$result = $appSession->getTier()->getArray($msg);
$numrows = count($result);	
$menus = [];
for($j =0; $j<$numrows; $j++)
{
	
	$row = $result[$j];
	
	if(developer != "1")
	{
		if($row[1] == "")
		{
			$hasItem = false;
			for($n =0; $n<count($rels); $n++)
			{
				if($rels[$n] == $row[0])
				{
					$hasItem = true;
					break;
				}
			}
			
			if($hasItem == true)
			{
				$menus[count($menus)] =  [$row[0], $row[1], $row[2],  $row[3], $row[4], $row[5], $row[6]];
			}
		}else{
			$menus[count($menus)] =  [$row[0], $row[1], $row[2],  $row[3], $row[4], $row[5], $row[6]];
		}
	}else{
		$menus[count($menus)] = [$row[0], $row[1], $row[2],  $row[3], $row[4], $row[5], $row[6]];
	}
	
}


$sql = "SELECT module_name, ac_name, ac_value FROM res_user_group_per WHERE status =0 AND group_id='".$appSession->getUserInfo()->getGroupId()."'";


$msg->add("query", $sql);
$result = $appSession->getTier()->getArray($msg);
$numrows = count($result);
$perList = array();
for($i = 0; $i < $numrows; $i++) 
{
	$row = $result[$i];
	$arr = array();
	$arr[0] = $row[0];
	$arr[1] = $row[1];
	$arr[2] = $row[2];
	$perList[$i] = $arr;
}
	
function per($appSession, $perList, $module_name, $ac_name)
{
	//return true;
	if($appSession->getUserInfo()->getGroupId() == "ADMIN")
	{
		return true;
	}
	
	for($i=0; $i<count($perList); $i++)
	{
		
		if($perList[$i][0] == $module_name && $perList[$i][1]== $ac_name)
		{
			if( $perList[$i][2] == "1"){
				return true;
			}
			return false;
		}
	}
	return true;
}		
function buildNav($appSession, $arr, $parent_id, $perList, $space)
{
	
	$s = "";
	for($i =0; $i<count($arr); $i++)
	{
		
		if($arr[$i][1] == $parent_id)
		{
			
			if(per($appSession, $perList, $arr[$i][0], "view") == true)
			{
				$hasSub = false;
				if($arr[$i][0] != 'report')
				{
					for($j =0; $j<count($arr); $j++)
					{
						if($arr[$j][1] == $arr[$i][0])
						{
							$hasSub = true;
							break;
						}
					}
					
				}
				$s = $s."<li>";
				$name = $arr[$i][6];
				if($name == "")
				{
					$name = $arr[$i][2];
				}
				$icon = $arr[$i][3];
				if($icon == "")
				{
					$icon = $arr[$i][4];
				}
				if($icon != "")
				{
					$icon = '<i class="'.$icon.'"></i>  ';
				}
				if($hasSub == true)
				{
					$s = $s.'<span class="caret" ><a href="javascript:clearNav(0);" style="padding: 0 '.$space.'px;">'.$icon.$name.'</a></span>';
					$s = $s.'<ul class="nested" style="margin:0; padding:0">';
					$s = $s.buildNav($appSession, $arr, $arr[$i][0], $perList, $space + 12);
					$s = $s.'</ul>';
				}else
				{
					$s = $s.'<a style="padding: 0 '.$space.'px;" href="javascript:menuItem(\''.$arr[$i][0].'\', \''.$arr[$i][5].'\');">'.$icon.$name.'</a>';
				}
				$s = $s."</li>";
			}
			
		}
	}
	
	return $s;
}
function buildMenu($appSession, $arr, $parent_id, $perList)
{
	$s = "";
	for($i =0; $i<count($arr); $i++)
	{
		if($arr[$i][1] == $parent_id)
		{
			if(per($appSession, $perList, $arr[$i][0], "view") == true)
			{
				$hasSub = false;
				if($arr[$i][0] != 'report')
				{
					for($j =0; $j<count($arr); $j++)
					{
						if($arr[$j][1] == $arr[$i][0])
						{
							$hasSub = true;
							break;
						}
					}
					
				}
				$s = $s."<li class=\"hide-small\">";
				$name = $arr[$i][6];
				if($name == "")
				{
					$name = $arr[$i][2];
				}
				if($hasSub == true)
				{
					$s = $s.'<a href="javascript:javascript:menuItem(\''.$arr[$i][0].'\', \''.$arr[$i][5].'\');">'.$name;
					if($parent_id == "")
					{
							$s = $s.' <i class="icon-arrow-down" style="font-size:8pt"></i>';
					}else{
						$s = $s.' <i class="icon-arrow-right" style="font-size:8pt"></i>';
					}
					$s = $s.'</a>';
					
					$s = $s.'<ul>';
					$s = $s.buildMenu($appSession, $arr, $arr[$i][0], $perList);
					$s = $s.'</ul>';
				}else
				{
					$s = $s.'<a href="javascript:javascript:menuItem(\''.$arr[$i][0].'\', \''.$arr[$i][5].'\');">'.$name.'</a>';
				}
				$s = $s."</li>";
			}
			
		}
	}
	
	return $s;
}

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
		//$style = $styles[$i][0];
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
$logo = logo;
if($logo == "")
{
	$logo = "logo.png";
}
;
?>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?php echo META_TITLE;?></title>
<meta http-equiv="x-ua-compatible" content="ie=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="shortcut icon" href="<?php echo URL;?>favicon.ico" type="image/png"/>
<link rel="stylesheet" href="<?php echo URL;?>assets/simple-line/css.css?v=3">
<link rel="stylesheet" href="<?php echo URL;?>assets/css/style.css">
<link href="<?php echo URL;?>assets/datepicker/foopicker.css" rel="stylesheet" type="text/css" />
<link href="<?php echo URL;?>assets/daterange/daterangepicker.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="<?php echo URL;?>assets/calendar/dist/fullcalendar.css">
<link rel="stylesheet" href="<?php echo URL;?>assets/grantchart/dhtmlxgantt.css">
<?php if($font != "")
{
?>
<link rel="stylesheet" href="<?php echo URL;?>assets/css/<?php echo $font;?>.css">
<?php
}
?>
<?php if($theme != "")
{
?>
<link rel="stylesheet" href="<?php echo URL;?>assets/css/<?php echo $theme;?>.css">
<?php
}
?>
<script>
var API_ID = '';
</script>
<script src="<?php echo URL;?>assets/js/jquery.js"></script>
<script src="<?php echo URL;?>assets/js/controller.js"></script>
<script src="<?php echo URL;?>assets/js/tool.js"></script>
<script src="<?php echo URL;?>assets/js/sha1.js"></script>
<script src="<?php echo URL;?>assets/js/attribute.js"></script>
<script src="<?php echo URL;?>assets/moment/moment.js"></script>
<script src="<?php echo URL;?>assets/daterange/daterangepicker.min.js"></script>

<script>
var URL = '<?php echo URL?>';
</script>
<script src="<?php echo URL;?>assets/js/admin.js"></script>
</head>
<body style="background-color: #ECEAEA;" >
<div id="T_HISTORY" style="position:fixed; bottom:10; padding-left:5px">&nbsp;</div>
<?php if($style == "style_top_menu")
{
?>
<style>
.content-body
{
	padding-left:16px;
	padding-bottom:250px;
}


.side-panel-trigger a {
	display: block;
	position: relative;
	width: 14px;
	height: 14px;
	font-size: 14px;
	line-height: 20px;
	text-align: center;
	color: #333;
	-webkit-transition: color .3s ease-in-out;
	-o-transition: color .3s ease-in-out;
	transition: color .3s ease-in-out;
}


</style>
<div class="top" >
 <div style="background-color: #efefef; height:34px">
	<menu>
		<ul  style="margin:0; padding:0px" style="background-color: blue;" >
			<li class="hide-large hide-medium"><a href="javascript:menu_show()">Menu</a></li>
			
			<?php
			echo buildMenu($appSession, $menus, "", $perList);
			?>
			<li><a href="javascript:loadView('', 'profile', '');">Profile</a></li>
			
		</ul>
	</menu>
 </div>


  <!-- Navbar on small screens -->
  <nav>
	<div id="mySidebar" class="sidebar shadow--1dp hide hide-large hide-medium" style="width:100%">
		<div class="container " style="text-align:center; background-color:#c1d7d7; border-bottom: 1px solid rgba(0,0,0, 0.12); ">
			<a href="javascript:loadView('', 'profile', '');">
		  <img src="<?php echo URL;?>assets/img/<?php echo $logo;?>" style="width:46" alt="<?php echo $appSession->getUserInfo()->getName();?>" class="circle hover-opacity">
		  </a>
		  <br>
		<b><?php echo $appSession->getUserInfo()->getName();?></b> 
		(<a href="">Task</a>)
			 
		  </div>
		  <div class="container">
		   <ul class="nav">
			<?php
			echo buildNav($appSession, $menus, "", $perList, 12);
			?>
			</ul>
			
			<hr>
			<a href="javascript:logout()">Exit</a>
		  </div>
	  </div>
  </nav>
</div>
<div class="main" style="margin-top:36px">
	<div id="contentView" style="margin:0;padding:0">
</div>
<script>

function menu_show() {
  var x = document.getElementById("mySidebar");
	  if (x.className.indexOf("show") == -1) {
		x.className += " show";
	  } else { 
		x.className = x.className.replace(" show", "");
	  }
}


</script>
<?php
}else
{
?>
<nav class="sidebar collapse shadow--1dp" style="z-index:3;width:230px;" id="mySidebar" >
  <div class="container" >
    <a href="#" onclick="nav_close()" class="hide-large" style="position:fixed; top:0; left:190px" title="close menu">
      <img  src="<?php echo URL;?>assets/img/menu_left.png">
    </a>
  
  </div>
  <div class="container " style="text-align:center; background-color:#c1d7d7; border-bottom: 1px solid rgba(0,0,0, 0.12); ">
	<a href="javascript:loadView('', 'profile', '');">
  <img src="<?php echo URL;?>assets/img/<?php echo $logo;?>" style="width:46" alt="<?php echo $appSession->getUserInfo()->getName();?>" class="circle hover-opacity">
  </a>
  <br>
	  <b><?php echo $appSession->getUserInfo()->getName();?></b> <br>
	  <span id="pnNotification"></span>&nbsp;&nbsp;&nbsp;<a href="javascript:todo()"><i class="icon-calendar"></i></a>
	  &nbsp;&nbsp;&nbsp;<a href="javascript:company()"><i class="icon-organization"></i></a>
	  &nbsp;&nbsp;&nbsp;<a href="javascript:logout()"><i class="icon-logout"></i></a>
	  
	 
  </div>
  <div class="container">
   <ul class="nav" style="list-style-type: none;">
	<?php
	
	echo buildNav($appSession, $menus, "", $perList, 12);
	?>
	</ul>
  </div>
 
</nav>

<div class="overlay hide-large animate-opacity" onclick="nav_close()" style="cursor:pointer" title="close side menu" id="myOverlay"></div>

<!-- !PAGE CONTENT! -->
<div class="main" style="margin-left:230px; padding:0" >
	<span class=" hide-large" onclick="nav_open()" style="position:fixed; top:0; right:2px"><img  src="<?php echo URL;?>assets/img/list.png"></span>

	<div id="contentView" style="margin:0;padding:0">
	</div>
</div>
<?php
}
?>
<div id="popView" class="modal" style="z-index:3000; width:100%">
    <div class="modal-content">
      <header style=" padding: 8px 8px 8px 24px;"> 
        <button onclick="document.getElementById('popView').style.display='none'" 
        class="button display-topright"><b>&times;</b></button>
      </header>
	  <hr>
      <div class="container">
        <div id="pnFullDialogContent" style="padding: 20px 24px 24px 24px;"></div>
      </div>
 
    </div>
 </div>
<script src="<?php echo URL;?>assets/tinymce/tinymce.min.js"></script>
<script>
<?php if($style == "style_top_menu")
{
?>

<?php
}
else
{
?>

var toggler = document.getElementsByClassName("caret");
var i;

for (i = 0; i < toggler.length; i++) {
  toggler[i].addEventListener("click", function() {

    this.parentElement.querySelector(".nested").classList.toggle("nav-active");
    this.classList.toggle("caret-down");
  });
}
function nav_open() {
    document.getElementById("mySidebar").style.display = "block";
    document.getElementById("myOverlay").style.display = "block";
}
function nav_close() {
    document.getElementById("mySidebar").style.display = "none";
    document.getElementById("myOverlay").style.display = "none";
}
<?php
}
?>
 


<?php
$sql = "SELECT d1.id, d1.code, (SELECT rate FROM res_currency_rate WHERE status =0 AND currency_id = d1.id ORDER BY receipt_date DESC limit 1) AS rate, d1.rounding, d1.decimal_places , d1.thousands_sep, d1.decimal_point, d1.symbol, d1.symbol_position FROM res_currency d1 WHERE d1.status =0";
$msg = $appSession->getTier()->createMessage();
$msg->add("root_company_id", $appSession->getConfig()->getProperty("root_company_id"));
$msg->add("company_id", $appSession->getUserInfo()->getCompanyId());
$msg->add("user_id", $appSession->getUserInfo()->getId());
$msg->add("query", $sql);
$currencyList = $appSession->getTier()->getArray($msg);
?>
var currencyList =[
<?php
for($i =0; $i<count($currencyList); $i++)
{
	if($i>0)
	{
		echo ",";
	}
	?>
	['<?php echo $currencyList[$i][0];?>','<?php echo $currencyList[$i][1];?>' , '<?php echo $currencyList[$i][2];?>', '<?php echo $currencyList[$i][3];?>', '<?php echo $currencyList[$i][4];?>', '<?php echo $currencyList[$i][5];?>', '<?php echo $currencyList[$i][6];?>', '<?php echo $currencyList[$i][7];?>', '<?php echo $currencyList[$i][8];?>']
	<?php
}
?>
];
function menuItem(module_id, root_module_id)
{
	clearNav(0);
	gt('contentView', module_id, '', '', '', root_module_id);
}
function logout()
{
	document.location.href='<?php echo URL;?>account/?ac=logout&continue=' + encodeURIComponent('<?php echo URL;?>admin') + '&api_id=';
}
function assignTask(id, rel_id, type)
{
	openPopup('<?php echo URL;?>admin/task/action/?ac=form&rel_id=' + rel_id + '&table_name=' + type + '&id=' + id);
}
function todo()
{
	clearNav(0);
	gt('contentView', '8e92b44e-ec28-44b3-e31f-dee5792253a1', '', '', '', '');
	
}
function company()
{
	openPopup('<?php echo URL;?>admin/company/');
}
todo();
function loadNotification()
{
	var ctr = document.getElementById('pnNotification');
	if(ctr == null)
	{
		return;
	}
	var _url = '<?php echo URL;?>api/notification/?user_id=<?php echo $appSession->getUserInfo()->getId();?>';
	
	loadPage('contentView', _url, function(status, message)
	{
		if(status== 0)
		{
			var notification = 0;
			if(numeric(message) == true)
			{
				notification = parseInt(message);
			}
			
			var s = "";
			if(notification>0)
			{
				s = '<a href="javascript:menuItem(\'1370edb8-296a-4836-ad0e-461399f14f7a\')"><i class="icon-bell"></i><span style="display:inline; position:relative; font-size: 10px; color: white; text-shadow: 1px 1px 1px rgba(0,0,0,0.2); width: 24px; height: 24px; line-height: 16px; text-align: center; background-color: red; border-radius: 50%"> '+ notification +' </span></a>';
			}
			ctr.innerHTML = s;
			
		}
		
	}, true);
}
loadNotification();
var running = true;
var ws  = null;
var listeners = [];
function listenItem(name, f)
{
	this.name = name;
	this.f = f;
}
function addListener(name, f)
{
	var items = [];
	for(var i=0; i<listeners.length; i++)
	{
		if(listeners[i].name != name)
		{
			items.push(listeners[i]);
		}
	}
	items.push(new listenItem(name, f));
	listeners = items;
}

<?php
if(WS_URL != "")
{
?>
function connect() {
	var ws = new WebSocket('<?php echo WS_URL;?>');
	ws.onopen = function() {

	ws.send('id=<?php echo $appSession->getUserInfo()->getId();?>&company_id=<?php echo $appSession->getUserInfo()->getCompanyId();?>&session_id=<?php echo $appSession->getSessionId();?>');
	};
	
	ws.onmessage = function(e) {
		try {
		  var obj = JSON.parse(e.data);
		  if(obj != null && obj.body != null)
		  {
			  for(var i=0; i<listeners.length; i++)
			  {
				  if(listeners[i] != null)
				  {
					  var att = new Attribute();
					
					  att.parse(obj.body, '&');
					  if(att.getProperty('type') == "table_changed" && att.getProperty('name') == "res_notification")
					  {
						  
						  loadNotification();
					  }
					  listeners[i].f(obj.from, att);
				  }
			  }
		  }
	  }
	catch(err) {
		console.log(err.message);
	 
	}
	  
	};
	ws.onclose = function(e) {
		
		ws = null;
		if(running == true)
		{
			console.log('Socket is closed. Reconnect will be attempted in 1 second.', e.reason);
			setTimeout(function() {
			  connect();
			}, 10000);
		}
		
	};

	ws.onerror = function(err) {
		console.error('Socket encountered error: ', err.message, 'Closing socket');
		ws.close();
		
	};
	
}

connect();
function sendMessage(data)
{
	if(ws != null)
	{
		ws.send(data);
		return true;
	}
	return false;
}
window.addEventListener("unload", function () {
	if(ws != null)
	{
		running = false;
		if(ws.readyState == WebSocket.OPEN){
			ws.close();
		}
	}
    
}
);
<?php
}
?>
	
</script>

</body>
</html>
