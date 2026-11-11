<?php


class OrderRepository {
  private static $ORDER_PER_PAGE = 10;

  static function add($appSession, Array $data, $customer_id) {
   
	$d = date("Y-m-d H:i:s", substr($data["apptime"], 0, 10));
	
    $sql = sprintf("INSERT INTO zalopay_orders(id, zptransid, description, amount, timestamp, channel, status, customer_id) VALUES ('%s',NULL,'%s',%d,'%s',NULL,0, '%s')",
      $data["apptransid"],
      $data["description"],
      $data["amount"],
      $d, $customer_id);
    $msg = $appSession->getTier()->createMessage();
	$msg->add("query", $sql);
    $ok = $appSession->getTier()->exec($msg);
   
    return $ok;
  }

  static function update($appSession, Array $data) {
   
    $apptransid = $data["apptransid"];
    $zptransid = $data["zptransid"];
    $channel = $data["channel"];
    $status = $data["status"];
    $sql = "UPDATE `orders` SET `zptransid`='$zptransid', `channel`=$channel, `status`=$status WHERE `apptransid`='$apptransid'";
	$msg = $appSession->getTier()->createMessage();
	$msg->add("query", $sql);
	$ok = $appSession->getTier()->exec($msg);
    return $ok;
  }

  static function paginate(int $page = 1) {
    $offset = ($page - 1) * self::$ORDER_PER_PAGE;
    $orders = Provider::select(
      "SELECT `o`.*, sum(
        CASE 
          WHEN `r`.`amount` IS NULL
          THEN 0 
          ELSE `r`.`amount` 
        END
      ) as `total_refund_amount`
      FROM `orders` as `o` LEFT JOIN `refunds` as `r`
      ON `o`.`zptransid`=`r`.`zptransid`
      GROUP BY `o`.`apptransid`, `o`.`zptransid`, `o`.`description`, `o`.`amount`, `o`.`timestamp`, `o`.`status`, `o`.channel
      ORDER BY `o`.`timestamp` DESC
      LIMIT $offset, ".self::$ORDER_PER_PAGE);
    $totalOrder = Provider::select("SELECT COUNT(*) as totalOrder FROM `orders`")[0]["totalOrder"];

    return [
      "currentPage" => $page,
      "totalOrder" => $totalOrder,
      "orders" => $orders,
      "orderPerPage" => self::$ORDER_PER_PAGE
    ];
  }

  static function getByApptransId(string $apptransid) {
    return Provider::select("SELECT * FROM `orders` WHERE `apptransid`='$apptransid'")[0];
  }
}