<?	
function getCurrency(){
	$valute = get('valute', 'RUR','pgs');
	session_start();
	if (!isset($_SESSION['currency']) || !isset($_SESSION['valute']) || $_SESSION['valute']!=$valute){
		$_SESSION['valute'] = $valute;
		$currency = sql_getRow("SELECT value, display, name FROM currencies WHERE name = '".$valute."'");
		if (!empty($currency)){
			$_SESSION['currency'] = $currency;
		} else {
			unset($_SESSION['currency']);
		}
	} else {
		$currency = $_SESSION['currency'];
	}
	
	session_write_close();
	return $currency;
}

function getCurrencyVal(){
	$currency = getCurrency();

	return $currency['value'];
}

function getCurrencyDisplay(){
	$currency = getCurrency();
	return $currency['display'];
}

function getCurrencyName(){
	$currency = getCurrency();
	return $currency['name'];
}

function getCurrencies(){
	$currencies = sql_getRows("SELECT value, display, name FROM currencies WHERE name!='Base'");
	return $currencies;
}
?>