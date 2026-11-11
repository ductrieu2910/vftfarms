<?php
$msg = $appSession->getTier()->createMessage();
error_reporting(E_ALL);
ini_set('display_errors', FALSE);
ini_set('display_startup_errors', FALSE);
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
require_once ABSPATH. '/app/PHPExcel/PHPExcel.php';
$attribute_id = '';
if(isset($_REQUEST['attribute_id']))
{
	$attribute_id = $_REQUEST['attribute_id'];
}
if(isset($_FILES['file'])){
	$file = $_FILES['file'];
	$temp = $file['name'];
	$path = ABSPATH.'disk/'.$temp;
	move_uploaded_file($_FILES["file"]["tmp_name"], $path);
	
	$objFile = PHPExcel_IOFactory::identify($path);
	$objData = PHPExcel_IOFactory::createReader($objFile);
	$objData->setReadDataOnly(true);
	$objPHPExcel = $objData->load($path);
	$sheetCount = $objPHPExcel->getSheetCount();
	$sheetNames = $objPHPExcel->getSheetNames();
	$sheetList = [];
	for($p =0; $p<$sheetCount; $p++)
	{
		
		$sheet = $objPHPExcel->setActiveSheetIndex($p);
		$name = $sheetNames[$p];
		$Totalrow = $sheet->getHighestRow();
		$LastColumn = $sheet->getHighestColumn();
		$TotalCol = PHPExcel_Cell::columnIndexFromString($LastColumn);
		$data =[];
		for ($i = 1; $i <= $Totalrow; $i++) {
			
			for ($j = 0; $j < $TotalCol; $j++) {
				$cell = $sheet->getCellByColumnAndRow($j, $i);
				$v = $cell->getValue();
				$f = "";
				if($cell->isFormula())
				{
					$f = $v;
					$v = $cell->getCalculatedValue();
				}
				$data[$i - 1][$j] = ["v" => $v, "f" => $f];
				
			}
		}
		$sheetList[$name] = $data;
	}
	$value = json_encode($sheetList);
	$sql = "UPDATE res_attribute SET value ='".$appSession->getTool()->replace($value, "'", "''")."', write_date=".$appSession->getTier()->getDateString()." WHERE id ='".$attribute_id."'";
	$msg->add("query", $sql);
	$appSession->getTier()->exec($msg);
	echo "OK";
}
	
?>