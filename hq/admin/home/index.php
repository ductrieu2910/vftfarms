<style>
.sidenav {
  width: 230px;
  z-index: 1;
  top: 0;
  left: 0;
  overflow-x: hidden;
  padding-top: 0px;
  padding-bottom: 35px;
}

.sidenav span, .dropdown-btn {
  padding: 8px 8px 8px 4px;
  text-decoration: none;
  color: black;
  display: block;
  border: none;
  background: none;
  width: 100%;
  text-align: left;
  cursor: pointer;
  outline: none;
}

/* On mouse-over */
.sidenav span:hover, .dropdown-btn:hover {
  background-color:#f1f1f1;
}

.active {
  
}

.dropdown-container {
  display: none;
  padding-left: 8px;
}


.caret-down {
  float: right;
  padding-right: 15px;
}
.dropbtn{
  color: white;
}
.dropbtn:hover,.dropbtn:focus {
  color: blue;
}


</style>
<nav class="sidebar collapse white" style="z-index:3;width:230px;" id="mySidebar"><br>
 
    <a href="#" onclick="nav_close()" class="hide-large" style="position:fixed; top:0; left:230px" title="close menu">
      <img  src="<?php echo URL;?><?php echo $appSession->getConfig()->getProperty("addons");?>/assets/images/arrow_back.png">
    </a>
	<a href="javascript:loadView('', 'install', '');">
   <img src="<?php echo URL;?><?php echo $appSession->getConfig()->getProperty("addons");?>/assets/images/add.png"></a>
  <div class="sidenav" id="navList"></div>
  <div class="footer" style="width:250px">
	<a href="javascript:logout()">Exit</a>
  </div>
</nav>


<div class="overlay hide-large animate-opacity" onclick="nav_close()" style="cursor:pointer" title="close side menu" id="myOverlay"></div>

<!-- !PAGE CONTENT! -->
<div class="main" style="margin-left:230px">
		<span class=" hide-large" onclick="nav_open()" style="position:fixed; top:0; right:2px"><img  src="<?php echo URL;?><?php echo $appSession->getConfig()->getProperty("addons");?>/assets/images/list.png"></span>
  
  
  <div class="container row" style="padding:4px; text-align:left">
	<div class="white" id="contentView">
	</div>
  </div>

</div>

<script>
// Script to open and close sidebar
function nav_open() {
    document.getElementById("mySidebar").style.display = "block";
    document.getElementById("myOverlay").style.display = "block";
}
 
function nav_close() {
    document.getElementById("mySidebar").style.display = "none";
    document.getElementById("myOverlay").style.display = "none";
}
</script>

<script src="<?php echo URL;?><?php echo $appSession->getConfig()->getProperty("addons");?>/assets/js/js.js"></script>
<dialog class="dialog full-width" >
    <h4 class="dialog__title">Allow data collection?</h4>
    <div class="dialog__content">
      <p>
        Allowing us to collect data will let us get you the information you want faster.
      </p>
    </div>
    <div class="dialog__actions">
      <button type="button" class="button">Agree</button>
      <button type="button" class="button close">Disagree</button>
    </div>
  </dialog>
  
  <script>
  
		function gt(v, module_id, model_name, id, params)
		{
			
			
			var _url = '<?php echo URL?><?php echo $appSession->getConfig()->getProperty("addons");?>/module/index.php?module_id=' + module_id;
			_url = _url + "&model_name=" + model_name + '&id=' + id;
			if(params == undefined)
			{
				params = "";
			}
			_url = _url + "&params=" + encodeURIComponent(params);
			if(v == '')
			{
				v = 'contentView';
			}
			
			loadPage(v, _url, function(status, message)
			{
				if(status== 0)
				{
					
				}
				
			}, false);
		}
		function loadView(v, page, params)
		{
			var _url = '<?php echo URL?><?php echo $appSession->getConfig()->getProperty("addons");?>/' + page + '/index.php';
			if(params != '')
			{
				_url = _url + '?' + params;
			}
		
			if(v == '')
			{
				v = 'contentView';
			}
			
			loadPage(v, _url, function(status, message)
			{
				if(status== 0)
				{
					
				}
				
			}, false);
		}
		
    var dialog = document.querySelector('dialog');
  
    if (! dialog.showModal) {
      dialogPolyfill.registerDialog(dialog);
    }
	function openPopup(_url)
	{
		loadPage('fullDialogBody', _url, function(status, message)
		{
			if(status== 0)
			{
				dialog.showModal();
			}
			
		}, false);
			
		
	}
	function closePopup()
	{
		 dialog.close();
	}
	function loadModule()
	{
		var _url = '<?php echo URL;?><?php echo $appSession->getConfig()->getProperty("addons") ?>/home/menu.php';
		loadPage('navList', _url, function(status, message)
		{
			if(status== 0)
			{
				
			}
			
		}, false);
	}
	loadModule();
	
	function addLine(p, complete)
	{
		
		var _url = '<?php echo URL;?><?php echo $appSession->getConfig()->getProperty("addons");?>/action.php?ac=add';
		_url = _url + '&p=' + encodeURIComponent(p);
		loadPage('pnRoutingLine', _url, function(status, message)
		{
			if(complete != null)
			{
				complete(status, message);
			}
			
		}, true);
	}
	function saveLine(table_name, name, type, id, theInput, complete)
	{
		var value = theInput.value;
		var _url = '<?php echo URL;?><?php echo $appSession->getConfig()->getProperty("addons");?>/action.php?ac=save&id=' + id;
		_url = _url + '&table_name=' + table_name;
		_url = _url + '&name=' + name;
		_url = _url + '&type=' + type;
		_url = _url + '&id=' + id;
		_url = _url + '&value=' + encodeURIComponent(value);
		loadPage('pnRoutingLine', _url, function(status, message)
		{
			if(complete != null)
			{
				complete(status, message);
			}
			
		}, true);
	}
	function removeLine(table_name, id, complete)
	{
		var result = confirm("Want to remove?");
		if (!result) {
			return;
		}

		var _url = '<?php echo URL;?><?php echo $appSession->getConfig()->getProperty("addons");?>/action.php?ac=del&id=' + id;
		_url = _url + '&table_name=' + table_name;
		loadPage('pnRoutingLine', _url, function(status, message)
		{
			if(complete != null)
			{
				complete(status, message);
			}
			
		}, true);
	}
	function updateRel(theInput, table_name, type, name, name_value, rel_id, rel_value, column, complete)
	{
		var column_value = "";
		if(theInput.type == "checkbox")
		{
			if(theInput.checked)
			{
				column_value = 0;
			}else{
				column_value = 1;
			}
		}
		if(type == 'n')
		{
			if(column_value == "")
			{
				column_value = '0';
			}
		}
		var _url = '<?php echo URL;?><?php echo $appSession->getConfig()->getProperty("addons");?>/action.php?ac=updateRel';
		_url = _url + '&table_name=' + table_name;
		_url = _url + '&type=' + type;
		_url = _url + '&name=' + name;
		_url = _url + '&rel_id=' + rel_id;
		_url = _url + '&name_value=' + name_value;
		_url = _url + '&rel_value=' + rel_value;
		_url = _url + '&column=' + column;
		_url = _url + '&column_value=' + encodeURIComponent(column_value);
		loadPage('pnRoutingLine', _url, function(status, message)
		{
			if(complete != null)
			{
				complete(status, message);
			}
			
		}, true);
	}
	function postStatus(rel_id, id, complete)
	{
		var result = confirm("Want to post status?");
		if (!result) {
			return;
		}
		var _url = '<?php echo URL;?><?php echo $appSession->getConfig()->getProperty("addons");?>/action.php?ac=postStatus';
		_url = _url + '&rel_id=' + rel_id;
		_url = _url + '&status_id=' + id;
		loadPage('pnRoutingLine', _url, function(status, message)
		{
			if(message == "OK")
			{
				if(complete != null)
				{
					complete(status, message);
				}
			}else
			{
				alert(message);
			}
			
		}, true);
	}
	function multipleRel(itemList, table_name, column_src, column_rel, src_value)
	{
		var collection = itemList.selectedOptions;
		var ids = "";
		for (var i=0; i<collection.length; i++) 
		{
			 if(ids != "")
			 {
				 ids = ids + ",";
			 }
			 ids = ids + collection[i].value;
		}
		var _url = '<?php echo URL?><?php echo $appSession->getConfig()->getProperty("addons");?>/module/action.php?ac=multipleRel&table_name=' + table_name + '&column_src=' + column_src + '&column_rel=' + column_rel + '&src_value=' + src_value + '&ids=' + encodeURIComponent(ids);
		
		
		loadPage('contentView', _url, function(status, message)
		{
			if(status== 0)
			{
				if(message != "OK")
				{
					alert(message);
				}
				
			}
			
		}, true);
		
		 
	}

	function module(id)
	{
		loadView('contentView', 'module_list', 'id=' + id);
	}
	function logout()
	{
		document.location.href='<?php echo URL;?><?php echo $appSession->getConfig()->getProperty("addons");?>/logout/index.php';
	}
	
  </script>