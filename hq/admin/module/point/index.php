<?php
$ac = "";
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "")
{
	$ac = "view";
}
$lat = '';
$lng = '';
$value = "";
if(isset($_REQUEST['value']))
{
	$value = $_REQUEST['value'];
}
if($value != "")
{
	$arr = $appSession->getTool()->split($value, ';');
	if(count($arr) == 2)
	{
		$lat = $arr[0];
		$lng = $arr[1];
	}
}
if($lat == '')
{
	$lat = COMPANY_LAT;
}
if($lng == '')
{
	$lng = COMPANY_LNG;
}
$name = "";
if(isset($_REQUEST['name']))
{
	$name = $_REQUEST['name'];
}


?>
LAT: <input type="text" id="editlat" value="<?php echo $lat;?>"/>
LNG: <input type="text" id="editlng" value="<?php echo $lng;?>"/>
<input type="button" value="Save" onclick="saveLocation()"/>
<br>
<div id="map" style="width:100%; height:800"></div>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_API_KEY;?>&callback=initMap"
  type="text/javascript"></script>
<script>

let map, infoWindow;

function initMap() {
	 const myLatLng = { lat: <?php echo $lat;?>, lng: <?php echo $lng;?> };
  const map = new google.maps.Map(document.getElementById("map"), {
    zoom: 11,
    center: myLatLng,
  });
  var marker = new google.maps.Marker({
    position: myLatLng,
    map,
    title: "<?php echo $name;?>",
	 draggable: true,
  });
  
   google.maps.event.addListener(marker, 'dragend', function() {
		document.getElementById('editlat').value = marker.getPosition().lat();
		document.getElementById('editlng').value = marker.getPosition().lng();
  });
 
}
function saveLocation()
{
	window.opener['<?php echo $name;?>'].value = document.getElementById('editlat').value + ';' + document.getElementById('editlng').value;
	window.close();
}
</script>