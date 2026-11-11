<?php

class Accounting
{

	public $rootAccount;
	public function __construct() {
		$this->rootAccount = new Account();
	}
	
	public function load($appSession, $type_id, $start_date, $end_date)
	{
		$index = $appSession->getTool()->indexOf($start_date, ' ');
		if($index != -1)
		{
			$start_date = $appSession->getTool()->substring($start_date, 0, $index);
		}
		$index = $appSession->getTool()->indexOf($end_date, ' ');
		if($index != -1)
		{
			$end_date = $appSession->getTool()->substring($end_date, 0, $index);
		}
		
		$msg = $appSession->getTier()->createMessage();
		$sql = "SELECT d1.id, d1.parent_id, d1.code, d1.name FROM account_account d1 LEFT OUTER JOIN account_account d2 ON(d1.parent_id = d2.id) LEFT OUTER JOIN account_account d3 ON(d2.parent_id = d3.id) LEFT OUTER JOIN account_account d4 ON(d3.parent_id = d4.id) WHERE d1.status =0 AND (d1.parent_id='".$type_id."' OR d2.parent_id='".$type_id."' OR d3.parent_id='".$type_id."')";
		$sql = $sql." ORDER BY d1.sequence ASC";
		
		$msg->add("query", $sql);
		$dt = $appSession->getTier()->getTable($msg);
		$this->fillAccount($dt, $type_id, $this->rootAccount);
		
		$sql = "SELECT d1.id, d1.receipt_date FROM account_balance d1 WHERE d1.receipt_date<'".$start_date."' AND d1.type_id ='".$type_id."' ORDER BY d1.receipt_date DESC LIMIT 1";
		$msg->add("query", $sql);
		$dt = $appSession->getTier()->getTable($msg);
		if($dt->getRowCount()>0)
		{
			$balance_id = $dt->getString(0, "id");
			$receipt_date = $dt->getString(0, "receipt_date");
			$index = $appSession->getTool()->indexOf($receipt_date, ' ');
			if($index != -1)
			{
				$receipt_date = $appSession->getTool()->substring($receipt_date, 0, $index);
			}
		
			
			$sql = "SELECT d2.account_id,  SUM(d1.credit) AS credit, SUM(d1.debit) AS debit FROM account_balance_line d1 WHERE d1.balance_id='".$balance_id."' AND d1.status =0";
			$sql = $sql." GROUP BY d2.account_id";
			$msg->add("query", $sql);
			$dt = $appSession->getTier()->getTable($msg);
			for($i = 0; $i<$dt->getRowCount(); $i++)
			{
				$account_id = $dt->getString($i, "account_id");
				$credit = $dt->getFloat($i, "credit");
				$debit = $dt->getFloat($i, "debit");
				$account = $this->rootAccount->findId($account_id);
				if($account != NULL)
				{
					$account->open_credit = $account->open_credit + $credit;
					$account->open_debit = $account->open_debit + $debit;
				}
			}
			$sql = "SELECT d1.credit_account_id, d1.debit_account_id, SUM(d1.amount) AS amount FROM account_journal d1 LEFT OUTER JOIN account_account d2 ON(d1.credit_account_id = d2.id) LEFT OUTER JOIN account_account d3 ON(d1.debit_account_id = d3.id) WHERE d1.status =0 AND d1.receipt_date>'".$receipt_date." 23:59.59' AND d1.receipt_date<'".$start_date." 00:00:00' AND (d2.type_id='".$type_id."' OR d3.type_id='".$type_id."') GROUP BY d1.credit_account_id, d1.debit_account_id";
			$msg->add("query", $sql);
			$dt = $appSession->getTier()->getTable($msg);
			for($i = 0; $i<$dt->getRowCount(); $i++)
			{
				$account_id = $dt->getString($i, "credit_account_id");
				$amount = $dt->getFloat($i, "amount");
				$account = $this->rootAccount->findId($account_id);
				if($account != NULL)
				{
					$account->open_credit = $account->open_credit + $amount;
				}
				$account_id = $dt->getString($i, "debit_account_id");
				$account = $this->rootAccount->findId($account_id);
				if($account != NULL)
				{
					$account->open_debit = $account->open_debit + $amount;
				}
			}
		}
		$sql = "SELECT d1.credit_account_id, d1.debit_account_id, SUM(d1.amount) AS amount FROM account_journal d1 LEFT OUTER JOIN account_account d2 ON(d1.credit_account_id = d2.id) LEFT OUTER JOIN account_account d3 ON(d1.debit_account_id = d3.id) WHERE d1.status =0 AND d1.receipt_date>='".$start_date." 00:00:00' AND d1.receipt_date<'".$end_date." 23:59:59' AND (d2.type_id='".$type_id."' OR d3.type_id='".$type_id."') GROUP BY d1.credit_account_id, d1.debit_account_id";
		
		
		$msg->add("query", $sql);
		$dt = $appSession->getTier()->getTable($msg);
		for($i = 0; $i<$dt->getRowCount(); $i++)
		{
			$account_id = $dt->getString($i, "credit_account_id");
			
			$amount = $dt->getFloat($i, "amount");
			$account = $this->rootAccount->findId($account_id);
			if($account != NULL)
			{
				$account->credit = $account->credit + $amount;
				
			}
			$account_id = $dt->getString($i, "debit_account_id");
			$account =  $this->rootAccount->findId($account_id);
			if($account != NULL)
			{
				$account->debit = $account->debit + $amount;
				
			}
			
		}
		$this->fillAmountAccount($this->rootAccount);
		//$this->print($this->rootAccount);
		
		
	}
	function fillAccount($dt, $parent_id, $parent)
	{
		for($i=0; $i<$dt->getRowCount(); $i++)
		{
			if($dt->getString($i, "parent_id") == $parent_id)
			{
				$account = new Account();
				$account->id = $dt->getString($i, "id");
				$account->code = $dt->getString($i, "code");
				$account->name = $dt->getString($i, "name");
				$parent->add($account);
				$this->fillAccount($dt, $account->id, $account);
			}
		}
		
	}
	function fillAmountAccount($account)
	{
		$l = $this->findChildren($account);
		$a = 0;
		$b = 0;
		$c = 0;
		$d = 0;
		for($i = 0; $i<count($l); $i++)
		{
			$a = $a + $l[$i]->open_credit;
			$b = $b + $l[$i]->open_debit;
			$c = $c + $l[$i]->credit;
			$d = $d + $l[$i]->debit;
		}
		$account->open_credit = $a;
		$account->open_debit = $b;
		$account->credit = $c;
		$account->debit = $d;
		$end = ($a + $b) + ($b -$d);
		if($end>0)
		{
			$account->end_credit = $end;
		}else{
			$account->end_debit = -1 * $end;
		}
		for($i=0; $i<count($account->children); $i++)
		{
			$this->fillAmountAccount($account->children[$i]);
		}
	}
	
	function findChildren($account)
	{
		$l =  [];
		if(count($account->children) == 0)
		{
			$l[count($l)] = $account;
		}else{
			for($i=0; $i<count($account->children); $i++)
			{
				if(count($account->children[$i]->children) == 0)
				{
					$l[count($l)] = $account->children[$i];
				}else{
					for($j = 0; $j<count($account->children[$i]->children); $j++)
					{
						$arr = $this->findChildren($account->children[$i]->children[$j]);
						for($n=0; $n<count($arr); $n++)
						{
							$l[count($l)] = $arr[$n];
						}
					}
				}
			}
		}
		
		return $l;
	}
	
	function findOpenCredit($code)
	{
		$account = $this->rootAccount->findCode($code);
		if($account != NULL){
			return $account->open_credit;
		}
		return 0;
	}
	function findOpenDebit($code)
	{
		$account = $this->rootAccount->findCode($code);
		if($account != NULL){
			return $account->open_debit;
		}
		return 0;
	}
	function findCredit($code)
	{
		$account = $this->rootAccount->findCode($code);
		if($account != NULL){
			return $account->credit;
		}
		return 0;
	}
	function findDebit($code)
	{
		$account = $this->rootAccount->findCode($code);
		if($account != NULL){
			return $account->debit;
		}
		return 0;
	}
	function findEndCredit($code)
	{
		$account = $this->rootAccount->findCode($code);
		if($account != NULL){
			return $account->credit;
		}
		return 0;
	}
	function findEndDebit($code)
	{
		$account =$this->rootAccount->findCode($code);
		if($account != NULL){
			return $account->debit;
		}
		return 0;
	}
	public function print($account)
	{
		echo $account->id.": ".$account->code.": oc: ".$account->open_credit.", od: ".$account->open_debit.", c: ".$account->credit.", d: ".$account->debit."<br>";
		
		for($i =0; $i<count($account->children); $i++)
		{
			$this->print($account->children[$i]);
		}
	}
}
class Account
{
	public $id;
	public $parent = NULL;
	public $code;
	public $name;
	public $open_credit = 0;
	public $open_debit = 0;
	public $credit = 0;
	public $debit = 0;
	public $end_credit = 0;
	public $end_debit = 0;
	public $children = [];
	public function __construct() {
		
	}
	public function add($account)
	{
		$account->parent = $this;
		$this->children[count($this->children)] = $account;
	}
	public function findId($id)
	{
		if($this->id == $id)
		{
			return $this;
		}
		for($i =0; $i<count($this->children); $i++)
		{
			$found = $this->children[$i]->findId($id);
			if($found != NULL)
			{
				return $found;
			}
		}
		return NULL;
	}
	public function findCode($code)
	{
		if($this->code == $code)
		{
			return $this;
		}
		for($i =0; $i<count($this->children); $i++)
		{
			$found = $this->children[$i]->findCode($code);
			if($found != NULL)
			{
				return $found;
			}
		}
		return NULL;
	}
}
?>