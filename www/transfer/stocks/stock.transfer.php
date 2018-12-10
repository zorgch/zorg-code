<?php
include(__DIR__ .'/../../../www/includes/main.inc.php');
include(__DIR__ .'/../../../www/includes/stock.inc.php');

$file = join("",file("swx_swiss_shares_reference_data"));
$file_array = explode("\n",$file);

foreach($file_array as $val) {
	$sym = explode(";",$val);
	//print_r($sym);
	$sym_symbol = $sym[5];
	$sym_company = $sym[2];

	$sql = "INSERT into stock_symbols (symbol, market, company, description, date) 
	VALUES ('".$sym_symbol."', 4,'".$sym_company."', 'N/A', now())";
	echo $db->query($sql);
	echo "<br /><br />";
	//echo $sql."<br ><br ><br >";
	
}

/*
function more($var) {
	if(substr_count($var, "... More...")) {
		return 0;
	} else {
		return 1;
	}	
}
$file = join("",file("symbols_Amex"));

$file_array = explode(";",$file);
$file_array = array_unique($file_array);
$file_array = array_filter($file_array,"more");

/*
foreach($file_array as $key => $val) {
	$sym = str_replace("\"\"","",$val);
	if(substr_count($sym,",") && $key != 0) {
		$sym = explode("\",\"",$sym);
		if(!substr_count($sym[0],"^"))	{
			$sym_company = substr($sym[0],4);
			$sym_symbol = $sym[1];
			$sym_value = str_replace("\$","",str_replace(",","",$sym[2]));
			$sym_desc = $sym[3];
			
			
			$sql = "INSERT into stock_symbols (symbol, market, value, company, description, date) 
			VALUES ('".$sym_symbol."', 3,'".$sym_value."', '".$sym_company."', '".$sym_desc."', now())";
			
			if(strlen($sql) > 150) {
				//echo $sql;
				//echo $db->query($sql);
				echo "<br /><br /><br />";
			}
		}
	}
}
*/
