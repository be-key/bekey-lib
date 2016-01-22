<?php

namespace Bekey\Paybox;

use Bekey\Paybox\Config\ConfigPaybox;
use Bekey\Paybox\Config\ConfigErrorPaybox;
use Symfony\Component\Yaml\Yaml;
use Bekey\Database\Database;

class Paybox{
	
	private $_config;
	private $_date;
	private $_database;
	
	public $pbx_paymentCard = array(
		"AMEX" => array(
			  'name' => 'America express',
			  'code' => 'AMEX'),
		"CB" => array('name' => 'Carte Bleu',
		      'code' => 'CB'),
		"VISA" => array('name' => 'Visa',
			  'code' => 'VISA'),
		"EUROCARD_MASTERCARD" => array('name' => 'Eurocard Mastercard',
			  'code' => 'EUROCARD_MASTERCARD'),
		"E_CARD" => array('name' => 'E-Carte Bleue',
		 	  'code' => 'E_CARD')
	);
	
	//Langues disponible pour l'interface de paiement
	private $_pbx_langue = array('FRA', 'GBR', 'ESP', 'ITA', 'DEU', 'NLD', 'SWE', 'PRT');
	
	public function __construct( Database $database = null ){
		
		$this->_database = $database;
		
		$this->_config = ConfigPaybox::$config;
		//$this->_config['domainName'] = 'http://'.$_SERVER['HTTP_HOST'].'/';
		$this->_config['domainName'] = 'http://www.genedon.com/';
		
		$this->_yamlConfig = Yaml::parse( file_get_contents(YAML_PATH.'/paybox.yml') );
		$this->_yamlConfig = $this->_yamlConfig['paybox'];
		
		//Surcharge la variable config avec les données utilisateurs
		foreach($this->_yamlConfig as $key => $value):
			$this->_config[$key] = (isset($this->_yamlConfig[$key]))? (string)$this->_yamlConfig[$key] : $this->_config[$key];
		endforeach;
		
		$this->_config['pbx_urlEffectue'] =  $this->_config['domainName'].$this->_config['pbx_urlEffectue'];
		$this->_config['pbx_urlRefuse'] =  $this->_config['domainName'].$this->_config['pbx_urlRefuse'];
		$this->_config['pbx_urlAnnule'] =  $this->_config['domainName'].$this->_config['pbx_urlAnnule'];
		$this->_config['pbx_urlAttente'] =  $this->_config['domainName'].$this->_config['pbx_urlAttente'];
		
		$this->_date = new \DateTime('now');
		$this->_date = $this->_date->format('c');
		
	}
	
	public function payboxRequiredInput($pbx_total, $pbx_comandeName, $pbx_devise, $pbx_emailPorteur, $pbx_langue, $pbx_paymentCard = null, $pbx_nameButton = null){
		
		$pbx_langue = strtoupper($pbx_langue);
		
		$server = $this->_defineServer();
		$url = 'https://'.$server.'/cgi/MYchoix_pagepaiement.cgi';
		
		//Force la langue de la page de paiement si celle demandé n'éxiste pas
		if( !in_array($pbx_langue, $this->_pbx_langue) ){
			$pbx_langue = 'FRA';
		}

		//Mise en session de la devise
		$_SESSION['Devise'] = $pbx_devise;
			
		$startForm  = '<form id="payboxform" method="POST" action="'.$url.'">';
		$startForm .= '<input type="hidden" name="PBX_SITE" value="' .$this->_config['pbx_site']. '">';
		$startForm .= '<input type="hidden" name="PBX_RANG" value="' .$this->_config['pbx_rang']. '">';
		$startForm .= '<input type="hidden" name="PBX_IDENTIFIANT" value="' .$this->_config['pbx_id']. '">';
		$startForm .= '<input type="hidden" name="PBX_TOTAL" value="' .$this->_convertedIntoCents($pbx_total). '">';
		$startForm .= '<input type="hidden" name="PBX_DEVISE" value="' .$pbx_devise. '">';
		$startForm .= '<input type="hidden" name="PBX_CMD" value="' .$pbx_comandeName. '">';
		$startForm .= '<input type="hidden" name="PBX_PORTEUR" value="' .$pbx_emailPorteur. '">';
		$startForm .= '<input type="hidden" name="PBX_RETOUR" value="' .$this->_config['pbx_retour']. '">';
		$startForm .= '<input type="hidden" name="PBX_HASH" value="SHA512">';
		$startForm .= '<input type="hidden" name="PBX_TIME" value="' .$this->_date. '">';
		$startForm .= '<input type="hidden" name="PBX_LANGUE" value="' .$pbx_langue. '">';
		$startForm .= '<input type="hidden" name="PBX_EFFECTUE" value="' .$this->_config['pbx_urlEffectue']. '">';
		$startForm .= '<input type="hidden" name="PBX_REFUSE" value="' .$this->_config['pbx_urlRefuse']. '">';
		$startForm .= '<input type="hidden" name="PBX_ANNULE" value="' .$this->_config['pbx_urlAnnule']. '">';
		$startForm .= '<input type="hidden" name="PBX_ATTENTE" value="' .$this->_config['pbx_urlAttente']. '">';
			
		//test si le moyen de paiment à été forcé et si le type de carte à été renseigné 
		if( $this->_config['pbx_forcePaymentType'] === true && isset($pbx_paymentCard) ){ 
			$paymentType = $this->_paymentType($pbx_paymentCard);
			$startForm .= $paymentType['input'];
		}else{
			$startForm .= '<input type="hidden" name="PBX_TYPEPAIEMENT" value="CARTE">';
			$startForm .= '<input type="hidden" name="PBX_TYPECARTE" class="PBX_TYPECARTE" value="EUROCARD_MASTERCARD">';
		}
				
		$endForm = '<input type="hidden" class="pbx_hmac" name="PBX_HMAC" value="' .$this->generateKey($this->_date, $pbx_total, $pbx_comandeName, $pbx_devise, $pbx_langue, $pbx_emailPorteur, $pbx_paymentCard). '">';
		
		/*$endForm = "";
		if( isset($pbx_nameButton) )
			$endForm  .= '<input type="submit" id="submit" value="' .$pbx_nameButton. '">';
		$endForm .= '</form>';*/

		$form =array(
			"startForm" => $startForm,
			"endForm" 	=> $endForm
		);

		return $form;
	}
	
	//Génère la clef secret hmac
	public function generateKey($pbx_date, $pbx_total, $pbx_comandeName, $pbx_devise, $pbx_langue, $pbx_emailPorteur, $pbx_paymentCard = null, $convert = null){
		
		if( $convert == null ){
			$pbx_total = $this->_convertedIntoCents($pbx_total);
		}
		$key = "PBX_SITE=" .$this->_config['pbx_site'].
			   "&PBX_RANG=" .$this->_config['pbx_rang'].
			   "&PBX_IDENTIFIANT=" .$this->_config['pbx_id'].
			   "&PBX_TOTAL=".$pbx_total.
			   "&PBX_DEVISE=".$pbx_devise.
			   "&PBX_CMD=".$pbx_comandeName.
			   "&PBX_PORTEUR=".$pbx_emailPorteur.
			   "&PBX_RETOUR=" .$this->_config['pbx_retour'].
			   "&PBX_HASH=SHA512".
			   "&PBX_TIME=".$pbx_date.
			   "&PBX_LANGUE=".$pbx_langue.
			   "&PBX_EFFECTUE=".$this->_config['pbx_urlEffectue'].
			   "&PBX_REFUSE=".$this->_config['pbx_urlRefuse'].
			   "&PBX_ANNULE=".$this->_config['pbx_urlAnnule'].
			   "&PBX_ATTENTE=".$this->_config['pbx_urlAttente'];
		
		//test si le moyen de paiment à été forcé et si le type de carte à été renseigné 
		/*if( $this->_config['pbx_forcePaymentType'] === true  &&  isset($pbx_paymentCard) ) {
			$paymentType = $this->_paymentType($pbx_paymentCard);
			$key .= $paymentType['key'];
		}*/
		
		$paymentType = $this->_paymentType( $pbx_paymentCard );
		$key .= $paymentType['key'];

	
		$binKey = pack("H*", $this->_config['pbx_hmac']);
		$hmacKey = strtoupper(hash_hmac('sha512', $key, $binKey));
		
		return $hmacKey;
			
	}
	
	//Force le type de paiement pour passer en étape 2 de l'interface de paiement
	private function _paymentType($pbx_paymentCard){
		
		$cardType = $pbx_paymentCard['code'];
		
		try{
			
			//Test si le type de carte ('forcé') existe et envoi l'utilisateur sur la page de paiement
			//Sinon envoi l'utilisateur sur la page choix du type de paiement
			$cardTest = array();
			foreach ($this->pbx_paymentCard as $cardCode):
				array_push( $cardTest, in_array($cardType, $cardCode) );
			endforeach;
			
			if(in_array(true, $cardTest)){
				
				$paymentInfo = array();
		
				//Ajoute les input pour le type de carte au formulaire
				$input = '<input type="hidden" name="PBX_TYPEPAIEMENT" value="CARTE">'.
						 '<input type="hidden" name="PBX_TYPECARTE" value="'.$cardType.'">';
				
				$paymentInfo['input'] = $input;
						
				//Ajoute les valeurs du type de carte pour la genération de la key	
				$key = "&PBX_TYPEPAIEMENT=CARTE".
					   "&PBX_TYPECARTE=".$cardType;
				
				$paymentInfo['key'] = $key;
							
				return $paymentInfo;

			}else{
				
				//Si la carte n'est pas présente on lève une erreur en mode prod
				//Sinon envoi sur la page choix du type de paiment en mode dev
				if($this->_config['pbx_forcePaymentType'] != 'prod'){
					throw new \Exception("Ce type de paiement n'est pas reconnu par le systeme");
				}else{
					$this->_config['pbx_forcePaymentType'] === false;
				}
				
			}	
			
		}catch(\Exception $e){
			echo $e->getMessage();
		}
		
	}
	
	private function _defineServer(){
		$servers = array(
			'tpeweb.paybox.com', //serveur primaire
			'tpeweb1.paybox.com' //serveur secondaire
		); 
		
		if( $this->_config['pbx_environnement'] == "prod" ){
			$serverUrl = "";
			foreach($servers as $server){
				$doc = new \DOMDocument();
				$doc->loadHTMLFile('https://'.$server.'/load.html');
				$server_status = "";
				$element = $doc->getElementById('server_status');
				if($element){ $serverStatus = $element->textContent; }
				if($serverStatus == "OK"){
					//Le serveur est prêt et les services opérationnels
					$serverUrl = $server;
					return $serverUrl;
					break;
				}
			}
			
			if(!$serverUrl){
				die("Erreur : Aucun serveur n'a été trouvé");
			}
		}else{
			$serverUrl = 'preprod-tpeweb.paybox.com';
			return $serverUrl;
		}
		
		
	}
	
	//Récupère le message de retour après la transaction paybox
	public function getErrorMessage($errorCode){
		
		$this->_errorMessage = ConfigErrorPaybox::$errorMessage;
		
		//Stock le message de retour
		$errorMessage = array();
		
		//Teste les 3 derniers charactères du code d'erreur
		//Si le paiement à été refusé par le centre de paiement le code sera supérieur à 100
		//Et on récupère les message du centre paiement
		$lastCharErrorCode = substr($errorCode, -3);
		if( $lastCharErrorCode > 100 ){
			
			$centreAutorisationErrorCode = substr($errorCode, -2);
			$this->_messageCentreAutorisation = ConfigErrorPaybox::$messageCentreAutorisation;
			$this->_messageCentreAutorisation = $this->_messageCentreAutorisation[$centreAutorisationErrorCode];
			$errorCode = '001xx';
			
			$errorMessage['messageCentreAutorisation'] = $this->_messageCentreAutorisation;
			
		}
		
		$errorMessage['message'] = $this->_errorMessage[$errorCode];
		
		return $errorMessage;
		
	}
	
	//Sauvegarde les données de la transaction
	public function saveOrder($status, $name, $message, $amount, $currency){
		
		$this->_database->save(
			array(
				'fields' => array(
					'status' => $status,
					'name' => $name,
					'message' => $message,
					'amount' => $amount,
					'currency' => $currency
				),
				'table' => 'order_paybox'
			)
		);
		
	}
	
	//Converti le montant en centimes
	private function _convertedIntoCents($montant){
		
		//Remplace la virgule par un point pour avoir un nombre décimal
		$montant = str_replace(",", ".", $montant);
		$montant = $montant * 100;
		
		return $montant;
		
	}
	
	//Reconverti le montant dans l'autre sens
	public function unconvertedIntoCents($montant){
		$montant = $montant / 100;
		return $montant;
	}
	
}