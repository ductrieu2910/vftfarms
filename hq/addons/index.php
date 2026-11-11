<?php
$position = strpos($uri, "?"); 
$PARAMS = "";
if($position == true)
{
	$PARAMS = substr($uri, $position + 1);
	$uri = substr($uri, 0, $position);
}
$page = "home";
$lang = "vi";
$page_id = '';
if($uri !='/' && $uri != '')
{
	$items = explode("/", $uri);
	if(count($items) ==1)
	{
		$page = $items[0];
	}
	if(count($items)>0)
	{
		$page_id = $items[count($items)-1];
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?php echo META_TITLE;?></title>
<meta http-equiv="x-ua-compatible" content="ie=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="shortcut icon" href="<?php echo URL;?>favicon.ico" type="image/png"/>
<link rel="stylesheet" href="<?php echo URL;?>assets/css/style.css?v=2">
<script src="<?php echo URL;?>assets/js/jquery.min.js"></script>
<script src="<?php echo URL;?>assets/js/controller.js"></script>
<script src="<?php echo URL;?>assets/js/tool.js"></script>
<script src="<?php echo URL;?>assets/js/back_to_top.js"></script>
</head>
<body>
<!-- Navbar -->
<div class="top">
  <div class="bar card white">
    <a class="bar-item button hide-medium hide-large  right" href="javascript:void(0);" onclick="nav_sidebar_action()" title="Navigation Menu"><img id="nav_action" src="<?php echo URL;?>assets/img/list.png"></a>
    <a href="<?php echo URL;?>" class="bar-item white"><img src="<?php echo URL;?>assets/img/logo.png" height="24"></a>
	<div class="right">
    
   
	<?php
	if($appSession->getUserInfo()->getId() == "")
	{
	?>
    <a href="<?php echo URL;?><?php echo $routing->find("account");?>" class="bar-item " ><?php echo $appSession->getLang()->find("Account");?></a>
	<?php
	}else{
	?>
	 <a href="<?php echo URL;?><?php echo $routing->find("profile");?>" class="bar-item " ><?php echo $appSession->getUserInfo()->getName();?></a>
	 <a href="<?php echo URL;?><?php echo $routing->find("admin");?>" class="bar-item " ><?php echo $appSession->getLang()->find("App");?></a>
     <a href="<?php echo URL;?><?php echo $routing->find("account");?>/?ac=logout&continue=<?php echo $appSession->getTool()->urlEncode(URL);?>" class="bar-item "><?php echo $appSession->getLang()->find("Sign Out");?> </a>
	<?php
	}
	?>
	</div>
  </div>

  <!-- Navbar on small screens -->
  <div id="nav_sidebar" class="bar-block card white hide hide-large hide-medium">
    <a href="#" class="bar-item  padding-large">Link 1</a>
    
  </div>
</div>

<div class="row-padding padding-64 container">
  <div class="content">
    <?php
	if(file_exists(ABSPATH .'addons/'.$page.'/index.php') == true)
	{
		include( ABSPATH .'addons/'.$page.'/index.php');
	}
	
	?>
  </div>
</div>



<script>
// Used to toggle the menu on small screens when clicking on the menu button
var mySidebar = document.getElementById("nav_sidebar");
function nav_sidebar_action() {
 
  if (mySidebar.className.indexOf("show") == -1) {
    mySidebar.className += " show";
	document.getElementById('nav_action').src= '<?php echo URL;?>assets/img/clear.png';
  } else { 
    mySidebar.className = mySidebar.className.replace(" show", "");
	document.getElementById('nav_action').src= '<?php echo URL;?>assets/img/list.png';
	
  }
}

</script>


</body>
</html>
