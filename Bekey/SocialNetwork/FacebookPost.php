<?php

namespace Bekey\SocialNetwork;

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRedirectLoginHelper;

/**
 *
 * Permet de poster des données sur Facebook depuis l'application
 * @package Bekey/SocialNetwork
 * @version 1.0
 * @author Biquet <anthony.papillaud@gmail.com>
 * @copyright Copyright (c) 2015, Biquet
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 **/
class FacebookPost{

    /**
     *
     * Post automatique sur la page facebook d'un utilisateurs
     * @version 1.0
     * @param $fbSession objet session facebook renvoyé lors d'une connexion
     * @param $params informations à publier sur le post facebook
     *
     **/
    public function __construct($fbSession, $params = array()){

        if(  isset($fbSession) ){

            $access_token = $fbSession->getToken();

            $attachment = array( 'access_token' => $access_token );
            $attachment = array_merge($attachment, $params);

            $request_profile = new FacebookRequest($fbSession, 'POST', '/me/feed', $attachment);
            $request_profile->execute();

        }

    }

}
