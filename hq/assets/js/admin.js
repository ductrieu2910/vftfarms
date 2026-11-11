
var navList = new Array();
function NavItem(caption, v, module_id, model_name, id, params, root_module_id)
{
	this.caption = caption;
	this.v = v;
	this.module_id = module_id;
	this.model_name = model_name;
	this.id = id;
	this.params = params;
	this.root_module_id = root_module_id;
}
function addNav(caption, v, module_id, model_name, id, params, root_module_id)
{
	navList.push(new NavItem(caption, v, module_id, model_name, id, params, root_module_id));
}
function clearNav(start)
{
	var arr = new Array();
	for(var i = 0; i<start; i++)
	{
		arr.push(navList[i]);
	}
	navList = arr;
}
function printNav(contentName)
{
	var ctr = document.getElementById(contentName);
	if(ctr == null)
	{
		return;
	}
	var s = '';
	for(var i = 0; i<navList.length; i++)
	{
		if(s != "")
		{
			s = s + " > ";
		}
		s = s + "<a href=\"javascript:clearNav(" + i + ");gt('" +navList[i].v + "', '" +navList[i].module_id + "', '" +navList[i].model_name + "', '" +navList[i].id + "', '" +navList[i].params + "', '" +navList[i].root_module_id + "')\">" + navList[i].caption + "</a>";
	}
	ctr.innerHTML = s;
}
function gt(v, module_id, model_name, id, params, root_module_id, oncompleted)
{
	
	
	var _url = URL + 'admin/module/?module_id=' + module_id;
	_url = _url + "&model_name=" + model_name + '&id=' + id;
	_url = _url + "&root_module_id=" + root_module_id;
	

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
			if(oncompleted != null)
			{
				oncompleted(status, message);
			}
		}
		
	}, false);
}
function loadView(v, page, params)
{
	var _url = URL + 'admin/' + page + '/';
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

function openPopup(_url)
{
	loadPage('pnFullDialogContent', _url, function(status, message)
	{
		if(status== 0)
		{
			document.getElementById('popView').style.display='block';
		}
		
	}, false);
	
	
}
function closePopup()
{
	document.getElementById('popView').style.display='none';
}
function addLine(p, complete)
{

	var _url = URL +'/action/?ac=add';
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
	var _url = URL + 'admin/action/?ac=save&id=' + id;
	_url = _url + '&table_name=' + table_name;
	_url = _url + '&name=' + name;
	_url = _url + '&type=' + type;
	_url = _url + '&id=' + id;
	_url = _url + '&value=' + encodeURIComponent(value);
	_url = _url + "&api_id=" + API_ID;
	
	loadPage('pnRoutingLine', _url, function(status, message)
	{
		if(complete != null)
		{
			complete(status, message);
		}
		
	}, true);
}
function saveLineValue(table_name, name, type, id, value, complete)
{
	var _url = URL + 'admin/action/?ac=save&id=' + id;
	_url = _url + '&table_name=' + table_name;
	_url = _url + '&name=' + name;
	_url = _url + '&type=' + type;
	_url = _url + '&id=' + id;
	_url = _url + '&value=' + encodeURIComponent(value);
	_url = _url + "&api_id=" + API_ID;
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

	var _url = URL + 'admin/action/?ac=del&id=' + id;
	_url = _url + '&table_name=' + table_name;
	_url = _url + "&api_id=" + API_ID;
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
	if(theInput.type == "checkbox" || theInput.type == "radio")
	{
		if(theInput.checked)
		{
			column_value = 0;
		}else{
			column_value = 1;
		}
	}else{
		column_value = theInput.value;
	}
	if(type == 'n')
	{
		if(column_value == "")
		{
			column_value = '0';
		}
	}
	var _url = URL + 'admin/action/?ac=updateRel';
	_url = _url + '&table_name=' + table_name;
	_url = _url + '&type=' + type;
	_url = _url + '&name=' + name;
	_url = _url + '&rel_id=' + rel_id;
	_url = _url + '&name_value=' + name_value;
	_url = _url + '&rel_value=' + rel_value;
	_url = _url + '&column=' + column;
	_url = _url + '&column_value=' + encodeURIComponent(column_value);
	_url = _url + "&api_id=" + API_ID;
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

	var _url = URL + 'admin/action/?ac=postStatus';
	_url = _url + '&rel_id=' + rel_id;
	_url = _url + '&status_id=' + id;
	_url = _url + "&api_id=" + API_ID;
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
	var _url = '<?php echo URL?>addons/module/action/?ac=multipleRel&table_name=' + table_name + '&column_src=' + column_src + '&column_rel=' + column_rel + '&src_value=' + src_value + '&ids=' + encodeURIComponent(ids);

	_url = _url + "&api_id=" + API_ID;
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
function fixed_format(v, decimal_places)
{
	return v.toFixed(decimal_places);
}
function currency_format(currency, amount)
{
	if(amount == "" || isNaN(amount))
	{
		amount = 0;
	}
	if(currency == "")
	{
		for(var i =0; i<currencyList.length; i++)
		{
			if(currencyList[i][2] == "1")
			{
				currency = currencyList[i][0];
				
				break;
			}
		}
	}
	var round_number = 0;
	var decimal_places = 2;
	var symbol = "";
	var symbol_position = "";
	for(var i =0; i<currencyList.length; i++)
	{
		if(currencyList[i][0] == currency || currencyList[i][1] == currency)
		{
			round_number = parseFloat(currencyList[i][3]);
			decimal_places = parseInt(currencyList[i][4]);
			thousands_sep = currencyList[i][5];
			decimal_point = currencyList[i][6];
			symbol = currencyList[i][7];
			symbol_position = currencyList[i][8];
			break;
		}
	}
	if(decimal_point == "")
	{
		decimal_point = ".";
	}
	if(thousands_sep == "")
	{
		thousands_sep = ",";
	}
	if(decimal_point == thousands_sep)
	{
		decimal_point = ".";
		thousands_sep = ",";
	}
	decimal_places = Math.abs(decimal_places);
	decimal_places = isNaN(decimal_places) ? 2 : decimal_places;

	amount = parseFloat(amount);
	var sign = "";
	if(amount<0)
	{
		sign = "-";
	}
	amount = Math.abs(amount);
	var num1 = 1;
	for (var index = 0; index < round_number; index++)
	{
		 num1 = num1 * 10;
	}
	
	if (round_number > 1)
	{
		amount = amount/num1;
		amount = Math.round(amount);
		amount = amount * num1;
	}
	var newstr = '';
	var pieces = amount.toFixed(decimal_places).split('.');


	var symbol_left = symbol;
	var symbol_right = "";
	if(symbol_position == "r")
	{
		symbol_left = "";	
		symbol_right = symbol;
	}
	var chars = pieces[0].split("").reverse();
	
	var count = 0;
	for (x in chars) {
		count++;
		if(count%3 == 1 && count != 1) {
			newstr = chars[x] + thousands_sep + newstr;
		} else {
			newstr = chars[x] + newstr;
		}
	}
	if(decimal_places>0)
	{
		newstr = newstr + decimal_point + pieces[1];
	}
	newstr = sign + symbol_left + newstr  + symbol_right;
	
	return newstr;
}
function currency_parse(currency, value)
{
	if(currency == "")
	{
		for(var i =0; i<currencyList.length; i++)
		{
			if(currencyList[i][2] == "1")
			{
				currency = currencyList[i][0];
				
				break;
			}
		}
	}
	
	var round_number = 0;
	var decimal_places = 2;
	var symbol = "";
	var symbol_position = "";
	for(var i =0; i<currencyList.length; i++)
	{
		if(currencyList[i][0] == currency || currencyList[i][1] == currency)
		{
			round_number = parseFloat(currencyList[i][3]);
			decimal_places = parseInt(currencyList[i][4]);
			thousands_sep = currencyList[i][5];
			decimal_point = currencyList[i][6];
			symbol = currencyList[i][7];
			symbol_position = currencyList[i][8];
			break;
		}
	}
	if(decimal_point == "")
	{
		decimal_point = ".";
	}
	if(thousands_sep == "")
	{
		thousands_sep = ",";
	}
	if(decimal_point == thousands_sep)
	{
		decimal_point = ".";
		thousands_sep = ",";
	}
	if(symbol != "")
	{
		while(true)
		{
			var index = value.indexOf(symbol);
			if(index == -1)
			{
				break;
			}
			value = value.replace(symbol, "");
		}
	}
	if(thousands_sep != "")
	{
		while(true)
		{
			var index = value.indexOf(thousands_sep);
			if(index == -1)
			{
				break;
			}
			value = value.replace(thousands_sep, "");
		}
	}
	if(decimal_point != "")
	{
		value = value.replace(decimal_point, ".");
	}
	return value;
}

function currency_convert(from, to, amount)
{
	if(amount == "" || isNaN(amount))
	{
		amount = 0;
	}
	amount = parseFloat(amount);
	var from_rate = 1;
	for(var i =0; i<currencyList.length; i++)
	{
		if(currencyList[i][0] == from || currencyList[i][1] == from)
		{
			from_rate = parseFloat(currencyList[i][2]);
			break;
		}
	}
	var to_rate = 1;
	var rounding = 0;
	var decimal_places = 2;
	for(var i =0; i<currencyList.length; i++)
	{
		if(currencyList[i][0] == to || currencyList[i][1] == to)
		{
			to_rate = parseFloat(currencyList[i][2]);
			rounding = parseFloat(currencyList[i][3]);
			decimal_places = parseInt(currencyList[i][4]);
			break;
		}
	}
	if(from_rate == 0)
	{
		from_rate = 1;
	}
	if(to_rate == 0)
	{
		to_rate = 1;
	}
	

	var a = to_rate/from_rate;
	
	a = a * amount;
	
	return a;
}

function tab(evt, name, index) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByName('tab_content' + name);
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
	if(tabcontent[i].id == index)
	{
		tabcontent[i].style.display = "block";
	}
  }
  tablinks = document.getElementsByName('tab_' + name);
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  evt.currentTarget.className += " active";
}

function autocomplete(inp, url, func) {
  
  var currentFocus;

  inp.addEventListener("input", function(e) {
      var a, b, i, val = this.value;
      closeAllLists();
      if (!val) { return false;}
      currentFocus = -1;
      
      a = document.createElement("DIV");
      a.setAttribute("id", this.id + "autocomplete-list");
      a.setAttribute("class", "autocomplete-items");
    
      this.parentNode.appendChild(a);
      
	  loadPage('contentView', url + encodeURIComponent(val), function(status, message)
	  {
		if(status== 0)
		{
			var arr = [];
			var items = message.split("\n");
			for(i =0; i<items.length; i++)
			{
				var index = items[i].indexOf("=");
				if(index != -1)
				{
					var key = items[i].substring(0, index);
					var val = items[i].substring(index + 1);
					arr.push(new Array(key, val));
				}
			}
			
			for (i = 0; i < arr.length; i++) {
			  b = document.createElement("DIV");
			  b.innerHTML = arr[i][1];
			 
			  b.innerHTML += "<input type='hidden' value='" + arr[i][1] + "' id='" + arr[i][0]+ "'>";
			  b.addEventListener("click", function(e) {
				 
				  inp.value = this.getElementsByTagName("input")[0].value;
				 
				  closeAllLists();
				  if(func != null)
				  {
					  func(inp, this.getElementsByTagName("input")[0].id);
				  }
			  });
			  a.appendChild(b);
			
		  }
			
		}
		
	  }, true);
      
  });
 
  inp.addEventListener("keydown", function(e) {
      var x = document.getElementById(this.id + "autocomplete-list");
      if (x) x = x.getElementsByTagName("div");
      if (e.keyCode == 40) {
       
        currentFocus++;
        
        addActive(x);
      } else if (e.keyCode == 38) { 
       
        currentFocus--;
        
        addActive(x);
      } else if (e.keyCode == 13) {
       
        e.preventDefault();
        if (currentFocus > -1) {
        
          if (x) x[currentFocus].click();
        }
      }
  });
  function addActive(x) {
    
    if (!x) return false;
   
    removeActive(x);
    if (currentFocus >= x.length) currentFocus = 0;
    if (currentFocus < 0) currentFocus = (x.length - 1);
  
    x[currentFocus].classList.add("autocomplete-active");
  }
  function removeActive(x) {
   
    for (var i = 0; i < x.length; i++) {
      x[i].classList.remove("autocomplete-active");
    }
  }
  function closeAllLists(elmnt) {
    var x = document.getElementsByClassName("autocomplete-items");
    for (var i = 0; i < x.length; i++) {
      if (elmnt != x[i] && elmnt != inp) {
        x[i].parentNode.removeChild(x[i]);
      }
    }
  }

  document.addEventListener("click", function (e) {
      closeAllLists(e.target);
  });
}