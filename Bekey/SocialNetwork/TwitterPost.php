<?php

namespace Bekey\SocialNetwork;

use Symfony\Component\Yaml\Yaml;
use Twitter\TwitterOAuth;
use Twitter\TwitterOAuthException;

/**
 *
 * Permet de poster des données sur Twitter depuis l'application
 * @package Bekey/SocialNetwork
 * @version 1.0
 * @author Biquet <anthony.papillaud@gmail.com>
 * @copyright Copyright (c) 2015, Biquet
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 **/
class TwitterPost {

    private $_connect;

    /**
     *
     * Mise en place de la connexion à l'api Twitter
     * @since TwitterPost 1.0
     * @param $token : array
     *         mettre les oauth_token et oauth_token_secret pour publier sur le feed d'un utilisateur connecté,
     *         laissez la vairaible vide pour publier sur le feed de l'app Twitter
     * @param $configPath : string Destination des fichiers.yml
     * @param $configName : array Nom de la configuration
     *
    **/
    public function __construct($token = array(), $configPath = "lib/Bekey/yaml/", $configName = null ){

        $fileName = strtolower("twitter-connect");
        if( $configName == null )
            $configName = $fileName;

        $yml = $configPath.$fileName.'.yml';
        $twitterConfig = Yaml::parse( file_get_contents($yml) );
        if(!$twitterConfig)
            throw new \Exception("Un ou plusieurs fichiers de configuration sont manquants ou ne sont pas configuré");

        $infoConnect = $twitterConfig[$configName]['info-connect'];

        $accesToken = $infoConnect['accesToken'];
        $accesTokenSecret = $infoConnect['accesTokenSecret'];

        if( !empty($token) ){
            $accesToken = $token['oauth_token'];
            $accesTokenSecret = $token['oauth_token_secret'];
        }
        $this->_connect = new TwitterOAuth($infoConnect['consumerKey'], $infoConnect['consumerSecret'], $accesToken, $accesTokenSecret);

    }

    /**
     *
     * Post un tweet avec un média en pièces jointe
     * @since TwitterPost 1.0
     * @param $tweet: string contenu du tweet
     * @param $medias: array image en pièce jointe du tweet
     * @return array status du tweet
     *
    **/
    public function media( $tweet, $medias = array() ){

        $media = array();
        foreach( $medias as $k => $m ):
            $k = $this->_connect->upload('media/upload', array('media' => $m));
            array_push($media, $k->media_id_string);
        endforeach;

        $parameters = array(
            'status' => $tweet,
            'media_ids' => implode(',', $media),
        );

        $result = $this->_connect->post('statuses/update', $parameters);

        return $result;
        
    }

}
