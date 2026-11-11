class Attribute {
  constructor() {
    this.lists =[];
  }
  setProperty(key, value)
  {
	  for(var i=0; i<this.lists.length; i++)
	  {
		  if(this.lists[i][0] == key)
		  {
			  this.lists[i][1] = value;
			  return;
		  }
	  }
	  this.lists.push([key, value]);
  }
   getProperty(key)
  {
	  for(var i=0; i<this.lists.length; i++)
	  {
		  if(this.lists[i][0] == key)
		  {
			  return this.lists[i][1];
		  }
	  }
	  return "";
  }
  parse(s, delimit)
  {
	  var arr = s.split(delimit);
	  var index = -1;
	  for(var i=0; i<arr.length; i++)
	  {
		  index = arr[i].indexOf('=');
		  if(index != -1)
		  {
			  var key = arr[i].substring(0, index);
			  var value = arr[i].substring(index + 1);
			  value = value.trim();
			  value = decodeURIComponent(value);
			  this.setProperty(key, value);
		  }
	  }
  }
  getMessage()
  {
	  var s = "";
	  for(var i=0; i<this.lists.length; i++)
	  {
		  if(s != "")
		  {
			  s = s + "&";
		  }
		  s = s + this.lists[i][0] + "=" + encodeURIComponent(this.lists[i][1]);
	  }
	  return s;
  }
}