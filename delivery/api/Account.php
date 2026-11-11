<?php

class Account
{
	public $appSession;
	public $msg;
	public function __construct($appSession) {
		$this->appSession = $appSession;
		$this->msg = $appSession->getTier()->createMessage();
	}
	public function employeeToPartner($employee_id)
	{
		$partner_id = "";
		$sql = "SELECT d1.partner_id, d1.code, d1.first_name, d1.middle_name, d1.last_name, d1.address FROM hr_employee d1 WHERE d1.id='".$employee_id."'";
		
		$this->msg->add("query", $sql);
		
		$arr = $this->appSession->getTier()->getArray($this->msg);
		if(count($arr)>0)
		{
			$partner_id = $arr[0][0];
			$builder = $this->appSession->getTier()->createBuilder("res_partner");
			if($partner_id == "")
			{
				$code = $arr[0][1];
				$name =  $arr[0][2]." ". $arr[0][2]. " ". $arr[0][4];
				$address =  $arr[0][5];
				$partner_id = $this->appSession->getTool()->getId();
				$builder->add("id", $partner_id);
				$builder->add("create_uid", $user_id);
				$builder->add("write_uid", $user_id);
				$builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
				$builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
				$builder->add("status", 0);
				$builder->add("company_id", $this->appSession->getConfig()->getProperty("company_id"));
				$builder->add("code", $code);
				$builder->add("name", $name);
				$builder->add("address", $address);
				$sql = $this->appSession->getTier()->getInsert($builder);
				$this->msg->add("query", $sql);
				$this->appSession->getTier()->exec($this->msg);
				$sql = "UPDATE hr_employee SET partner_id='".$partner_id."', write_date=".$this->appSession->getTier()->getDateString()." WHERE id='".$employee_id."'";
				$this->msg->add("query", $sql);
				$this->appSession->getTier()->exec($this->msg);
			}
			
		}
		
		return $partner_id;
	}
	public function customerToPartner($customer_id)
	{
		$partner_id = "";
		$sql = "SELECT d1.partner_id, d1.code, d1.name, d1.phone, d1.vat, d1.address FROM customer d1 WHERE d1.id='".$customer_id."'";
		
		$this->msg->add("query", $sql);
		
		$arr = $this->appSession->getTier()->getArray($this->msg);
		if(count($arr)>0)
		{
			$partner_id = $arr[0][0];
			$builder = $this->appSession->getTier()->createBuilder("res_partner");
			if($partner_id == "")
			{
				$code = $arr[0][1];
				$name =  $arr[0][2];
				$phone = $arr[0][2];
				$vat = $arr[0][4];
				$address =  $arr[0][5];
				$partner_id = $this->appSession->getTool()->getId();
				$builder->add("id", $partner_id);
				$builder->add("create_uid", $this->appSession->getConfig()->getProperty("user_id"));
				$builder->add("write_uid", $this->appSession->getConfig()->getProperty("user_id"));
				$builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
				$builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
				$builder->add("status", 0);
				$builder->add("company_id", $this->appSession->getConfig()->getProperty("company_id"));
				$builder->add("code", $code);
				$builder->add("name", $name);
				$builder->add("phone", $phone);
				$builder->add("vat", $vat);
				$builder->add("address", $address);
				$sql = $this->appSession->getTier()->getInsert($builder);
				$this->msg->add("query", $sql);
				$this->appSession->getTier()->exec($this->msg);
				$sql = "UPDATE customer SET partner_id='".$partner_id."', write_date=".$this->appSession->getTier()->getDateString()." WHERE id='".$customer_id."'";
				$this->msg->add("query", $sql);
				$this->appSession->getTier()->exec($this->msg);
			}
			
		}
		
		return $partner_id;
	}
	public function createInvoice($sale_id)
	{
		$sql = "SELECT id FROM account_journal WHERE rel_id ='".$sale_id."'";
		$this->msg->add("query", $sql);

		$dt = $this->appSession->getTier()->getTable($this->msg);
		if($dt->getRowCount()>0)
		{
			return $dt->getString(0, "id");
		}
		$sql = "SELECT customer_id, receipt_no, receipt_date FROM sale WHERE id ='".$sale_id."'";
		
		$this->msg->add("query", $sql);

		$dt = $this->appSession->getTier()->getTable($this->msg);
		$origin_no = "";
		$origin_date = "";
		
		$customer_id = "";
		if($dt->getRowCount()>0)
		{
			$customer_id = $dt->getString(0, "customer_id");
			$origin_no = $dt->getString(0, "receipt_no");
			$origin_date = $dt->getString(0, "receipt_date");
		}
		
		$partner_id = $this->customerToPartner( $customer_id);

		$sql = "SELECT code, id FROM account_journal_category WHERE type='SALE'";
		$this->msg->add("query", $sql);
		$dt = $this->appSession->getTier()->getTable($this->msg);
		$category_code = "";
		$category_id = "";
		
		if($dt->getRowCount()>0)
		{
			$category_code = $dt->getString(0, "code");
			$category_id = $dt->getString(0, "id");
		}

		 $receipt_type = date("y");
		 $m = date("m");
	   
		 $receipt_no = $this->appSession->getTool()->findReceiptNo($this->appSession->getTier(), $this->appSession->getUserInfo()->getCompanyId(), "account_journal.".$receipt_type.".".$category_id);
		$receipt_no = $this->appSession->getTool()->padLeft($receipt_no, 4, "0");
		$receipt_no = $category_code."-".$receipt_type.".".$receipt_no;
		$journal_id = $this->appSession->getTool()->getId();
		$builder = $this->appSession->getTier()->createBuilder("account_journal");
		$builder->add("id", $journal_id);
		$builder->add("create_uid", $this->appSession->getUserInfo()->getId());
		$builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
		$builder->add("write_uid", $this->appSession->getUserInfo()->getId());
		$builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
		$builder->add("company_id", $this->appSession->getUserInfo()->getCompanyId());
		$builder->add("status", 0);
		$builder->add("rel_id", $sale_id);
		$builder->add("partner_id", $partner_id);
		$builder->add("category_id", $category_id);
		$builder->add("currency_id", $this->appSession->getConfig()->getProperty("currency_id"));
		$builder->add("receipt_no",$receipt_no);
		$builder->add("receipt_date", $this->appSession->getTier()->getDateString(), 'f');
		$builder->add("journal_date", $this->appSession->getTier()->getDateString(), 'f');
		$builder->add("origin_no", $origin_no);
		if($origin_date != "")
		{
			$builder->add("origin_date", $origin_date);
		}
	
		$builder->add("description", "Bán hàng");
		$builder->add("rel_id", $sale_id);
		$builder->add("type", "SALE");
	
		$sql = $this->appSession->getTier()->getInsert($builder);
		$this->msg->add("query", $sql);
		$this->appSession->getTier()->exec($this->msg);
		
		
		$sql = "SELECT d1.id, d1.product_id, d1.unit_id, d1.attribute_id, d1.type_id, d1.currency_id, d1.quantity, ((d1.quantity * d1.unit_price) -  d1.discount_amount) AS amount FROM sale_product d1 WHERE d1.status =0 AND d1.sale_id ='".$sale_id."' ORDER BY d1.sequence ASC, d1.create_date ASC";
		
		$this->msg->add("query", $sql);
		$dt = $this->appSession->getTier()->getTable($this->msg);
		$sql = "";
		$total = 0;
		for($i =0; $i<$dt->getRowCount(); $i++)
		{
			if($sql != "")
			{
				$sql = $sql."; ";
			}
			$sale_product_id = $dt->getString($i, "id");
			$product_id = $dt->getString($i, "product_id");
			$unit_id = $dt->getString($i, "unit_id");
			$attribute_id = $dt->getString($i, "attribute_id");
			$type_id = $dt->getString($i, "type_id");
			$currency_id = $dt->getString($i, "currency_id");
			$quantity = $dt->getFloat($i, "quantity");
			$amount = $dt->getFloat($i, "amount");
			$unit_price = $amount / $quantity;
			$unit_price = $unit_price/1.05;
			$total = $total + ($quantity * $unit_price);
			
			$builder = $this->appSession->getTier()->createBuilder("account_journal_line");
			$builder->add("id", $this->appSession->getTool()->getId());
			$builder->add("create_uid", $this->appSession->getUserInfo()->getId());
			$builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
			$builder->add("write_uid", $this->appSession->getUserInfo()->getId());
			$builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
			$builder->add("company_id", $this->appSession->getUserInfo()->getCompanyId());
			$builder->add("status", 0);
			$builder->add("journal_id", $journal_id);
			$builder->add("product_id", $product_id);
			$builder->add("unit_id", $unit_id);
			$builder->add("attribute_id", $attribute_id);
			$builder->add("type_id", $type_id);
			$builder->add("currency_id", $currency_id);
			$builder->add("quantity",$quantity);
			$builder->add("unit_price", $unit_price);
			$builder->add("rel_id", $sale_product_id);
			$builder->add("sequence", $i);
			$sql = $sql.$this->appSession->getTier()->getInsert($builder);
			
			
		}
		if($sql != "")
		{
			$sql = $sql."; ";
		}
			
		$builder = $this->appSession->getTier()->createBuilder("account_journal_line");
		$builder->add("id", $this->appSession->getTool()->getId());
		$builder->add("create_uid", $this->appSession->getUserInfo()->getId());
		$builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
		$builder->add("write_uid", $this->appSession->getUserInfo()->getId());
		$builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
		$builder->add("company_id", $this->appSession->getUserInfo()->getCompanyId());
		
		$builder->add("status", 0);
		$builder->add("journal_id", $journal_id);
		$builder->add("product_id", "");
		$builder->add("unit_id", "");
		$builder->add("attribute_id", "");
		$builder->add("type_id", "");
		$builder->add("currency_id", $this->appSession->getConfig()->getProperty("currency_id"));
		$builder->add("quantity",1);
		$builder->add("name","Thuế VAT");
		$builder->add("unit_price", $total * 0.05);
		$builder->add("rel_id", "");
		$builder->add("sequence", $dt->getRowCount() + 1);
		$sql = $sql.$this->appSession->getTier()->getInsert($builder);

		if($sql != ""){
			$this->msg->add("query", $sql);
			$this->appSession->getTier()->exec($this->msg);
		}
		return $journal_id;
		
	}
	public function addPaymentLocal($rel_id, $payment_id, $currency_id, $amount, $description)
	{
		$sql = "SELECT id FROM account_payment_line_local WHERE id='".$payment_id."'";
		$this->msg->add("query", $sql);
		$payment_line_id = $this->appSession->getTier()->getValue($this->msg);
		if($payment_line_id == "")
		{
			$receipt_no = $this->appSession->getTool()->findReceiptNo($this->appSession->getTier(), $this->appSession->getUserInfo()->getCompanyId(), "account_payment_line");
			$receipt_no = $this->appSession->getTool()->padLeft($receipt_no, 4, "0");
				
			$payment_line_id = $this->appSession->getTool()->getId();
			$builder = $this->appSession->getTier()->createBuilder("account_payment_line_local");
			$builder->add("id", $payment_line_id);
			$builder->add("create_uid", $this->appSession->getUserInfo()->getId());
			$builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
			$builder->add("write_uid", $this->appSession->getUserInfo()->getId());
			$builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
			$builder->add("company_id", $this->appSession->getUserInfo()->getCompanyId());
			
			$builder->add("status", 0);
			$builder->add("receipt_no", $receipt_no);
			$builder->add("payment_id", $payment_id);
			$builder->add("currency_id", $currency_id);
			$builder->add("amount",$amount);
			$builder->add("receipt_date", $this->appSession->getTier()->getDateString(), 'f');
			$builder->add("description", $description);
			$builder->add("rel_id", $rel_id);
			$builder->add("line_id", $rel_id);
			$sql = $this->appSession->getTier()->getInsert($builder);
			$this->msg->add("query", $sql);
			$this->appSession->getTier()->exec($this->msg);
		}else{
			$sql = "UPDATE account_payment_line_local SET amount=".$amount.", write_date=NOW(), status =0 WHERE id='".$payment_line_id."'";
			$this->msg->add("query", $sql);
			$this->appSession->getTier()->exec($this->msg);
		}
		
		
		return $payment_line_id;
	}
}

?>