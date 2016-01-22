<?php

namespace Bekey\Paybox;
use Bekey\Paybox\Config\ConfigDevisePaybox;

class PayboxDevise{
	
	public $devise;
	
	public function __construct(){
		$this->devise = ConfigDevisePaybox::$devise;
	}
	
	//on calcule le montant de la transaction en fonction de la locale
	//et on rcupère la devise de la monnaie
	public function postAmountAndDevise($amount, $locale){
		
		$devise = $this->devise[$locale];
		$exchangeRate = $this->getExchangeRate();
		$amountAndDevise = array();
		
		if($devise['name'] !== 'Euro')
			$amount = round( $exchangeRate[$devise['iso_str']] * $amount, 2);

		$amountAndDevise['amount'] = $amount;
		$amountAndDevise['symbol'] = $devise['symbol'];
		
		return $amountAndDevise;
		
	}
	
	//On récupére les taux de change des devise par rapport à l'euro
	public function getExchangeRate(){

		$url = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
		$xml = simplexml_load_file($url);
		$xml = $xml->Cube->Cube;
		
		$exchangeRate = array();
		foreach($xml->children() as $tauxDevise) { 
			
			$currency = (string) $tauxDevise['currency'];
			$rate = (float) $tauxDevise['rate'];
			$exchangeRate[$currency] = $rate;
		
		}
		
		return $exchangeRate;	
		
	}
	
}