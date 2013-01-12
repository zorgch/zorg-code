<?PHP
require_once($_SERVER['DOCUMENT_ROOT']."/includes/stockbroker.inc.php");


if($_GET['pw'] == 'schmelzigel') {
	
	foreach (Stockbroker::getStocksOldest() as $symbol) {
		Stockbroker::updateKurs($symbol);
	}
	
}
?>