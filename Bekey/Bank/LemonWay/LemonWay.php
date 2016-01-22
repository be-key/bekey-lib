<?php

namespace Bekey\Bank\LemonWay;
use Bekey\Bank\LemonWay\LemonWayError;
use Bekey\Bank\LemonWay\LemonWayCardForm;

class LemonWay{

    public static $lemonWayError;
    private static $accessConfig = array(
        'dev' => array(
            'wlLogin' => 'society',
    	    'wlPass' => '123456',
    	    'language' => 'fr'
        ),
        'preprod' => array(
            'wlLogin' => 'society',
    	    'wlPass' => '123456',
    	    'language' => 'fr'
        ),
        'prod' => array(
            'wlLogin' => '',
    	    'wlPass' => '',
    	    'language' => ''
        )
    );

    private static $directKitUrl = array(
        'dev' => 'https://ws.lemonway.fr/mb/demo/dev/directkit/service.asmx',
        'preprod' => 'https://ws.lemonway.fr/mb/demo/dev/directkit/service.asmx',
        'prod' => 'https://ws.lemonway.fr/mb/genedon/dev/directkit/service.asmx'
    );

    private static $webkitUrl = array(
        'dev' => 'https://m.lemonway.fr/mb/demo/dev/',
        'preprod' => 'https://m.lemonway.fr/mb/demo/dev/',
        'prod' => 'https://m.lemonway.fr/mb/genedon/dev/'
    );

    private static $paymentUrl = array(
        'dev' => array(
            'returnUrl' => 'http://genedon.dev/fr/payment?url=return',
			'cancelUrl' => 'http://genedon.dev/fr/payment?url=cancel',
			'errorUrl'  => 'http://genedon.dev/fr/payment?url=error',
			'cssUrl'    => 'http://genedon.dev/css/vendors/lemonway.genedon.css'
        ),
        'preprod' => array(
            'returnUrl' => 'http://preprod.genedon.com/fr/payment?url=return',
			'cancelUrl' => 'http://preprod.genedon.com/fr/payment?url=cancel',
			'errorUrl'  => 'http://preprod.genedon.com/fr/payment?url=error',
			'cssUrl'    => 'http://preprod.genedon.com/css/vendors/lemonway.genedon.css'
        ),
        'prod' => array(
            'returnUrl' => 'http://genedon.dev/fr/payment?url=return',
			'cancelUrl' => 'http://genedon.dev/fr/payment?url=cancel',
			'errorUrl'  => 'http://genedon.dev/fr/payment?url=error',
			'cssUrl'    => 'https://www.lemonway.fr/mercanet_lw.css'
        )
    );

    public static function registerWallet($params) {
		$result = self::_postRequest('RegisterWallet', $params, '1.1');
		return $result;
	}

    public static function updateWalletDetails($params) {
		$result = self::_postRequest('UpdateWalletDetails', $params, '1.1');
		return $result;
	}

    public static function registerIBAN($params) {
		$result = self::_postRequest('RegisterIBAN', $params, '1.1');
		return $result;
	}

    public static function getWalletTransHistory($params) {
		$result = self::_postRequest('GetWalletTransHistory', $params, '1.1');
		return $result;
	}

    public static function moneyInWebInit($params){

        $defaultParams = array(
            'wkToken'        => self::_getRandomId(),
            'returnUrl'      => urlencode(self::$paymentUrl[BEKEY_ENV]['returnUrl']),
            'cancelUrl'      => urlencode(self::$paymentUrl[BEKEY_ENV]['cancelUrl']),
            'errorUrl'       => urlencode(self::$paymentUrl[BEKEY_ENV]['errorUrl']),
            'autoCommission' => '0'
        );
        $params = array_merge($defaultParams, $params);
        $result = self::_postRequest('MoneyInWebInit', $params, '1.2');

        if ( isset(self::$lemonWayError) ):
            return $result;
        else:
            $cardForm = LemonWayCardForm::printCardForm( self::$webkitUrl[BEKEY_ENV], $result->MONEYINWEB->TOKEN, self::$paymentUrl[BEKEY_ENV]['cssUrl'] );
            $result = array(
                'id' => $result->MONEYINWEB->ID,
                'cardForm' => $cardForm
            );
            return $result;
        endif;

    }

    public static function registerWalletAndmoneyInWebInit($paramsWallet = array(), $paramsMoneyIn = array()) {

        $result = self::_postRequest('RegisterWallet', $paramsWallet, '1.1');

        if ( !isset(self::$lemonWayError) ):

            $defaultParams = array( 'wallet' => (string) $result->WALLET->ID );
            $paramsMoneyIn = array_merge($defaultParams, $paramsMoneyIn);

            $result = self::moneyInWebInit($paramsMoneyIn);

        else:
            return $result;
		endif;

	}

    private static function _postRequest($methodName, $params, $version){
		$ua = '';
		if (isset($_SERVER['HTTP_USER_AGENT']))
			$ua = $_SERVER['HTTP_USER_AGENT'];
		$ip = '';
		if (isset($_SERVER['REMOTE_ADDR']))
			$ip = $_SERVER['REMOTE_ADDR'];

		$xml_soap = '<?xml version="1.0" encoding="utf-8"?><soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope"><soap12:Body><'.$methodName.' xmlns="Service_mb">';

		foreach ($params as $key => $value) {
			$xml_soap .= '<'.$key.'>'.$value.'</'.$key.'>';
		}
		$xml_soap .= '<version>'.$version.'</version>';
		$xml_soap .= '<wlPass>'.self::$accessConfig[BEKEY_ENV]['wlPass'].'</wlPass>';
		$xml_soap .= '<wlLogin>'.self::$accessConfig[BEKEY_ENV]['wlLogin'].'</wlLogin>';
		$xml_soap .= '<language>'.self::$accessConfig[BEKEY_ENV]['language'].'</language>';
		$xml_soap .= '<walletIp>'.$ip.'</walletIp>';
		$xml_soap .= '<walletUa>'.$ua.'</walletUa>';

		$xml_soap .= '</'.$methodName.'></soap12:Body></soap12:Envelope>';

		$headers = array("Content-type: text/xml;charset=utf-8",
						"Accept: application/xml",
						"Cache-Control: no-cache",
						"Pragma: no-cache",
						'SOAPAction: "Service_mb/'.$methodName.'"',
						"Content-length: ".strlen($xml_soap),
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::$directKitUrl[BEKEY_ENV]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_soap);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($ch);

        $returnCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        switch($returnCode){
            case 200:
                $response = html_entity_decode($response);
                $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
                $response = str_replace('xmlns="Service_mb"', '', $response); //suppress absolute uri warning
                $xmlResponse = new \SimpleXMLElement($response);
                $output = $xmlResponse->soapBody->{$methodName.'Response'}->{$methodName.'Result'};

                //Retourne les informations sur l'erreur renvoyé par LemonWay
                if (isset($output->E)){
                    self::$lemonWayError = new LemonWayError($output->E->Code, $output->E->Msg);
                }
                return $output;

                curl_close($ch);
                break;
            default:
                print('http code : '. $returnCode);
                break;
        }
	}

    private static function _getRandomId(){
		return str_replace('.', '', microtime(true).rand());
	}

}
