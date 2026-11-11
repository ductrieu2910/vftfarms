<?php 
require ABSPATH."zalopay/zalopay/helper.php";
require ABSPATH."zalopay/repository/order_repository.php";

$ac = "";
if(isset($_REQUEST['ac']))
{
	$ac = $_REQUEST['ac'];
}
if($ac == "add")
{
	$customer_id = $appSession->getConfig()->getProperty("customer_id");
	$amount = 0;
	if(isset($_REQUEST['amount']))
	{
		$amount = (int) $_REQUEST['amount'];
	}
    if ($amount < 1000) {
      $error = "Số tiền không hợp lệ";
    } else {
		$bankcode = "";
		if(isset($_REQUEST['bankcode']))
		{
			$bankcode = $_REQUEST['bankcode'];
		}
		$description = "";
		if(isset($_REQUEST['description']))
		{
			$description = $_REQUEST['description'];
		}
      $data = [];
	  $data["amount"] =$amount;
	  $data["bankcode"] =$bankcode;
	  $data["description"] =$description;
      if ($data["bankcode"] === "ATM") {
        $data["bankcode"] = "";
        $data["embeddata"] = ["bankgroup" => "ATM"];
      }
      $orderData = ZaloPayHelper::newCreateOrderData($data);
      $order = ZaloPayHelper::createOrder($orderData);

      if ($order["returncode"] === 1) {
        OrderRepository::add($appSession, $orderData, $customer_id);
        $orderUrl = $order["orderurl"];
        echo "Location: ". $orderUrl;
      }
      else {
        $error = "Tạo đơn hàng thất bại";
      }
    }
}
 
?>
