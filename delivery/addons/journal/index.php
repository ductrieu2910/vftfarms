<?php
require_once(ABSPATH . 'api/Account.php');
validUser($appSession);
$msg = $appSession->getTier()->createMessage();

$page_id = "transaction";
$table_name = "sale";
$searchs = ["d2.code", "d2.name", "d1.order_no", "d1.receipt_no"];

$ac = "";
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
	$appSession->getLang()->load($appSession->getTier(), "HOME", $appSession->getConfig()->getProperty("lang_id"));
	


?>
<div class="page_title">
	<div class="row align-items-center mx-0">
		<div class="col-2 col-md-2">
			<div class="title_inner d-flex">
				<h3 class="d-flex align-items-center"><?php echo $appSession->getLang()->find("Transaction");?></h3>
				
			</div>
		</div>
		<div class="col-10  col-md-10 ">
		<table>
			<tr>
				<td><div id="reportrange" style="cursor: pointer; width: 100%">
					<img src="<?php echo URL;?>assets/images/calendar.png" border="0" /></i>&nbsp;
					<span></span> <img src="<?php echo URL;?>assets/images/menu_down.png" border="0" />
				</div></td>
				<td><form class="search_box col-12 col-sm-12 col-md-12 col-lg-8 col-xl-7 p-0 px-lg-3 mt-3 mt-lg-0 pb-3 pb-md-0 ml-auto" onsubmit="return false">
				<div class="form-group d-flex">
					<div class="input-group-prepend">
						<div class="input-group-text"><i class="zmdi zmdi-search" onclick="doSearchTransaction(0)"></i></div>
					</div>
					<input type="text" id="editsearch" class="form-control" placeholder="Search" value="" onKeyDown="if(event.keyCode == 13){doSearchTransaction(0);}">
					
			</div></form></td>
			</tr>
		</table>
			
			
		</div>
	</div>
</div>
<!-- Right Sidebar Start -->
<div class="right_sidebar" id="pnOrders">
	
</div>
<!-- Tab Content End -->

<script>
var fdate = "";
var tdate = "";
$( document ).ready(function() {
	var start = moment();
    var end = moment();

    function cb(start, end) {
		if(start != null)
		{
			$('#reportrange span').html(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
			fdate = start.format('YYYY-MM-D') + ' 00:00:00.000';
			tdate = end.format('YYYY-MM-D')+ ' 23:59:59.999';
		}else{
			$('#reportrange span').html(null);
		}
        
		doSearchTransaction(0);
    }

    $('#reportrange').daterangepicker({
        startDate: start,
        endDate: end,
        ranges: {
           '<?php echo $appSession->getLang()->find("Today");?>': [moment(), moment()],
           '<?php echo $appSession->getLang()->find("Yesterday");?>': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           '<?php echo $appSession->getLang()->find("Last 7 Days");?>': [moment().subtract(6, 'days'), moment()],
           '<?php echo $appSession->getLang()->find("Last 30 Days");?>': [moment().subtract(29, 'days'), moment()],
           '<?php echo $appSession->getLang()->find("This Month");?>': [moment().startOf('month'), moment().endOf('month')],
           '<?php echo $appSession->getLang()->find("Last Month");?>': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
		"locale": {
			"format": "DD/MM/YYYY",
			"separator": " - ",
			"applyLabel": "Đồng ý",
			"cancelLabel": "Đóng",
			"fromLabel": "Từ",
			"toLabel": "Đến",
			"customRangeLabel": "Chọn ngày",
			"daysOfWeek": [
				"Chủ nhật",
				"Thứ 2",
				"Thứ 3",
				"Thứ 4",
				"Thứ 5",
				"Thứ 6",
				"Thứ 7"
			],
			"monthNames": [
				"Tháng giêng",
				"Tháng hai",
				"Tháng 3",
				"Tháng tư",
				"Tháng năm",
				"Tháng sáu",
				"Tháng bảy",
				"Tháng tám",
				"Thang chín",
				"Tháng mười",
				"Tháng mười một",
				"Tháng mười hai"
			],
			"firstDay": 1
		}
    }, cb);

    cb(start, end);
});

function doSearchTransaction()
{

	var search = document.getElementById('editsearch').value;
	var _url = '<?php echo URL;?>addons/journal/?ac=orders&search=' + encodeURIComponent(search);
	_url = _url + "&fdate=" + encodeURIComponent(fdate);
	_url = _url + "&tdate=" + encodeURIComponent(tdate);
	loadPage('pnOrders', _url, function(status, message)
	{
		if(status== 0)
		{
			
		}
		
	}, false);
}
function showOrder(id)
{
	var _url = '<?php echo URL;?>addons/<?php echo $page_id;?>/?ac=line&id=' + id;
	showPopup(_url);
}
function printContent(el){
var data=document.getElementById(el).innerHTML;
            var myWindow = window.open('', 'my div', 'height=400,width=600');
            myWindow.document.write('<html><head><title>my div</title>');
            /*optional stylesheet*/ //myWindow.document.write('<link rel="stylesheet" href="main.css" type="text/css" />');
            myWindow.document.write('</head><body >');
            myWindow.document.write(data);
            myWindow.document.write('</body></html>');
            myWindow.document.close(); // necessary for IE >= 10

            myWindow.onload=function(){ // necessary if the div contain images

                myWindow.focus(); // necessary for IE >= 10
                myWindow.print();
                myWindow.close();
            };
}
function removeRedInvoice(id)
{
	var result = confirm("Bạn muốn hủy hóa đơn?");
	if (!result) {
		return;
	}
	
	var _url = '<?php echo URL;?>addons/journal/?ac=removeRedInvoice&local=&sale_id=' + id;

	loadPage('pnCategory', _url, function(status, message)
	{
		if(status== 0)
		{
			message = message.replace(/^\s+|\s+$/gm,'')
			if(message.length == 36)
			{
				doSearchTransaction(0);
			}else{
				alert(message);
			}
			
		}
		
	}, true);
}
function createInvoice(sale_id)
{
	var _url = '<?php echo URL;?>addons/journal/?ac=createJournal&sale_id=' + sale_id;
	loadPage('pnCategory', _url, function(status, message)
	{
		if(status== 0)
		{
			message = message.replace(/^\s+|\s+$/gm,'')
			if(message.length == 36)
			{
				printJournal(message);
			}else{
				alert(message);
			}
			
		}
		
	}, true);
}
function printJournal(id)
{
	window.open('<?php echo URL;?>addons/journal/journal_print/?journal_id=' + id);
}

</script>
<?php
}else if($ac == "orders")
{

	$search = "";
	if(isset($_REQUEST['search']))
	{
		$search = $_REQUEST['search'];
	}
	$p = 0;
	if(isset($_REQUEST['p']))
	{
		$p = $_REQUEST['p'];
	}

	$fdate="";
	if(isset($_REQUEST['fdate']))
	{
		$fdate = $_REQUEST['fdate'];
	}
	$tdate="";;
	if(isset($_REQUEST['tdate']))
	{
		$tdate = $_REQUEST['tdate'];
	}
	
	$sql = "SELECT d1.id, d1.receipt_no, d1.receipt_date, d1.order_no,d1.order_date, d2.code AS customer_code, d2.name AS customer_name, d2.vat, (SELECT SUM(quantity) from sale_product WHERE sale_id=d1.id AND status=0) AS items, (SELECT SUM((quantity * unit_price) + tax_amount+service_amount-discount_amount) from sale_product WHERE sale_id=d1.id AND status=0) AS amount, d6.id AS journal_id  FROM sale d1 LEFT OUTER JOIN customer d2 ON(d1.customer_id = d2.id) LEFT OUTER JOIN sale_shipping d5 ON(d1.id = d5.sale_id) LEFT OUTER JOIN account_journal d6 ON(d1.id = d6.rel_id) WHERE d1.status =0";
	$sql = $sql." AND d1.status =0";
	$sql = $sql." AND d5.start_date>='".$fdate."'";
	$sql = $sql." AND d5.start_date<='".$tdate."'";
	if($search != "")
	{
		$sql = $sql." AND (".$appSession->getTier()->buildSearch($searchs, $search).")";
	}
	$sql = $sql." ORDER BY d1.order_date DESC LIMIT 100";
	$msg->add("query", $sql);
	$dt = $appSession->getTier()->getTable($msg);
	
	
	
	$sql = "SELECT type FROM res_company WHERE id='".$appSession->getConfig()->getProperty("company_id")."'";
	$msg->add("query", $sql);
	$pin = $appSession->getTier()->getValue($msg);
	$company_currency_id = $appSession->getConfig()->getProperty("currency_id");
	
	?>
	<!-- Tab Content Start -->
	<div class="tab-content" id="nav-tabContent">

		<!-- Food Items Tab customers Start -->
		<div>
			<!-- Order List Start -->
			<div class="order_list">
			<div class="table-responsive">
				 <table class="table table-bordered table-hover table-sm">
					<tr class="list_header">
					<td class="text-left" nowrap="nowrap">Order No</td>
					<td class="text-left " nowrap="nowrap">Receipt Date</td>
					<td class="text-left " nowrap="nowrap">Receipt No</td>
					<td class="text-left " nowrap="nowrap">Receipt Date</td>
					<td class="text-left " nowrap="nowrap">Customer Code</td>
					<td class="text-left " nowrap="nowrap">Customer Name</td>
					<td class="text-left " nowrap="nowrap">Vat</td>
					<td class="text-left " nowrap="nowrap">Items</td>
					<td class="text-left " nowrap="nowrap">Amount</td>
					
					<td class="text-right Action" style="width: 5%;"><?php echo $appSession->getLang()->find("Action");?></td>
				 </tr>
					<tbody>
                           
					<?php
					$hasRequest = false;
					for($i =0; $i<$dt->getRowCount(); $i++)
					{
						$id = $dt->getString($i, "id");
						$order_no = $dt->getString($i, "order_no");
						$receipt_no = $dt->getString($i, "receipt_no");
						$receipt_date = $dt->getString($i, "receipt_date");
						$order_date = $dt->getString($i, "order_date");
						$customer_code = $dt->getString($i, "customer_code");
						$customer_name = $dt->getString($i, "customer_name");
						$vat = $dt->getString($i, "vat");
						$items = $dt->getString($i, "items");
						$amount = $dt->getString($i, "amount");
						$journal_id = $dt->getString($i, "journal_id");
		
					
					?>
						
									 
					<td class="text-left"><?php echo $order_no;?></td>
					<td  class="text-left" nowrap="nowrap"><?php
				  if($order_date != "")
					{
						$d = $appSession->getTool()->toDateTime($order_date);
						$stime = $appSession->getTool()->paddingLeft($d->getHours(), '0', 2).":".$appSession->getTool()->paddingLeft($d->getMinutes(), '0', 2);
						
						$order_date = $appSession->getTool()->paddingLeft($d->getDays(), '0', 2)."/".$appSession->getTool()->paddingLeft($d->getMonths(), '0', 2)."/".$appSession->getTool()->paddingLeft($d->getYears(), '0', 2);
						echo $order_date;
					}
				  ?></td>
						<td  class="text-left " nowrap="nowrap"><?php echo $receipt_no;?></td>
	  <td  class="text-left " nowrap="nowrap"><?php
	  if($receipt_date != "")
		{
			$d = $appSession->getTool()->toDateTime($receipt_date);
			$stime = $appSession->getTool()->paddingLeft($d->getHours(), '0', 2).":".$appSession->getTool()->paddingLeft($d->getMinutes(), '0', 2);
			
			$receipt_date = $appSession->getTool()->paddingLeft($d->getDays(), '0', 2)."/".$appSession->getTool()->paddingLeft($d->getMonths(), '0', 2)."/".$appSession->getTool()->paddingLeft($d->getYears(), '0', 2);
			echo $receipt_date;
		}
	  ?></td>
						
						<td class="text-left " nowrap="nowrap" ><?php echo $customer_code;?></td>
						<td class="text-left " nowrap="nowrap" ><?php echo $customer_name;?></td>
						<td class="text-left " nowrap="nowrap" ><?php echo $vat;?></td>
						<td class="text-left " nowrap="nowrap"><?php echo $items;?></td>
						<td class="text-left " nowrap="nowrap"><?php echo $appSession->getCurrency()->format($company_currency_id, $amount);?></td>
						<td  class="btn_container  d-flex mr-0 ml-auto" nowrap="nowrap">
							<?php 
							if($journal_id !="")
							{
							?>
							<button type="button" class="btn" onclick="printJournal('<?php echo $journal_id;?>')">Print</button>
							<?php
							}else{
							?>
							<button type="button" class="btn" onclick="createInvoice('<?php echo $id;?>')">Create Invoice</button>
							<?php
							}
							?>
						</td>
					</tr>
					<?php
					}
					?>
					

				</tbody>
				</table>
				</div>
				</div>
				
			</div>
			<!-- Order List End -->

			</div>
			<!-- Tab Footer End -->
		</div>
	
	<?php
}
else if($ac == "createJournal")
{
	
	$sale_id = "";
	if(isset($_REQUEST['sale_id']))
	{
		$sale_id = $_REQUEST['sale_id'];
	}
	$account = new Account($appSession);
	$journal_id = $account->createInvoice($sale_id);
	echo $journal_id;
	
}else if($ac == "updateCustomer")
{
	$sale_id = "";
	if(isset($_REQUEST['sale_id']))
	{
		$sale_id = $_REQUEST['sale_id'];
	}
	$customer_id = "";
	if(isset($_REQUEST['customer_id']))
	{
		$customer_id = $_REQUEST['customer_id'];
	}
	$sql = "UPDATE sale SET write_date=NOW(), customer_id='".$customer_id."'";
	$sql = $sql." WHERE id ='".$sale_id."'";
	$msg->add("query", $sql);
	$r = $appSession->getTier()->exec($msg);
	
	echo "OK";
}
	
?>
