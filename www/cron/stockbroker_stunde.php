<?PHP

require_once($_SERVER['DOCUMENT_ROOT']."/includes/stockbroker.inc.php");

if($_GET['pw'] == 'schmelzigel') {
	
	foreach (Stockbroker::getStocksTraded() as $symbol) {
		Stockbroker::updateKurs($symbol);
	}
	
}
?>