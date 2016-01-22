<?php

namespace Bekey\Bitly;

use Bekey\Yaml\YamlConfig;

/**
 *
 * Librairie php pour intéragir avec avec l'api bit.ly v3
 * @package Bitly
 * @version 1.0
 * @author Biquet <anthony.papillaud@gmail.com>
 * @copyright Copyright (c) 2015, Biquet
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 **/

/* url de l'api bit.ly v3 avec parametres oAuth */
define('bitly_oauth_api', 'https://api-ssl.bit.ly/v3/');

class Bitly{

    /**
     *
     * Formate un appel GET à l'api bit.ly
     * @param $url: string url à convertir
     * @param $params: array utilisé pour passer des paramètres en GET à l'url
     * @since Bitly 1.0
     * @return string url bit.ly
     *
    **/
    public function getBitlyUrl( $url, $params = array() ){

        $configName = 'bitly-connect';
        $config = YamlConfig::Config( array('path' => 'lib/Bekey/Yaml/FileConfig', 'filename' => 'bitly') );
        $token = $config[$configName]['genericToken']['token'];

        $bitlyUrl = bitly_oauth_api . 'shorten?access_token='. $token . '&longUrl=' . $url . "?" . http_build_query($params);
        $result = json_decode($this->_getBitly_cUrl($bitlyUrl), true);

        if($result['status_code'] == 200)
            var_dump($result['data']['url']);

    }

    /**
     *
     * Configure un appel GET à l'api bit.ly
     * @param $url: string url à convertir
     * @since Bitly 1.0
     * @return array bit.ly réponse
     *
    **/
    private function _getBitly_cUrl($url){

        $result = "";
        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 4);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_exec($ch);
            $result = curl_exec($ch);
        } catch (Exception $e) {}

        return $result;

    }

}
