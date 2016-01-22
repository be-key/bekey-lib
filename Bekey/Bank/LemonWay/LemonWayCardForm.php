<?php

namespace Bekey\Bank\LemonWay;

class LemonWayCardForm{

    public $_url;
    public $_moneyInToken;
    public $_cssUrl;
    public $_language;

    public static function printCardForm($url, $moneyInToken, $cssUrl = '', $language = 'fr'){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . "?moneyintoken=" . $moneyInToken .'&p=' . urlencode($cssUrl) . '&lang=' . $language);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $server_output = curl_exec($ch);

        if( curl_errno($ch) ){
			print( curl_error($ch) ); //TODO : erreur curl
		}else{
            $returnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            switch($returnCode){
                case 200:
                    curl_close($ch);
                    //print($server_output);
                    return $server_output;
                    break;
                default:
                    print($returnCode); //TODO : erreur http
                    break;
            }
        }

    }

}
