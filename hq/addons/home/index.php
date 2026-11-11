
<!-- Header -->
<header class="container white center" style="padding:128px 16px">
  <h1 class="margin jumbo">WELCOME TO VIDU.VN</h1>
  <button class="button btn-primary" id="txt"></button>
</header>
<script>
function startTime() {
  var today = new Date();
  var h = today.getHours();
  var m = today.getMinutes();
  var s = today.getSeconds();
  m = checkTime(m);
  s = checkTime(s);
  document.getElementById('txt').innerHTML =
  h + ":" + m + ":" + s;
  var t = setTimeout(startTime, 1000);
}
function checkTime(i) {
  if (i < 10) {i = "0" + i};  // add zero in front of numbers < 10
  return i;
}
startTime();
</script>