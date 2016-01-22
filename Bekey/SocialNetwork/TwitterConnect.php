<?php

namespace Bekey\SocialNetwork;

use Bekey\SocialNetwork\SocialNetworkConnect;
use Bekey\Database\Database;
use Twitter\TwitterOAuth;
use Twitter\TwitterOAuthException;

/**
 *
 * Permet de mettre en place la connexion avec Twitter, étend la class "SocialNetworkConnect"
 * @package Bekey/SocialNetwork
 * @version 1.0
 * @author Biquet <anthony.papillaud@gmail.com>
 * @copyright Copyright (c) 2015, Biquet
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 **/

class TwitterConnect extends SocialNetworkConnect{

    private $_loginUrl;
    private $_database;
    private $_session;
    private $_saveTable;
    private $_saveFields;
    private $_infoConnect;
    private $_urlRedirect;

    /**
     *
     * Mise en place de la fonction Twitter connect
     * @since Twitterconnect 1.0
     * @param $config: array('info-connect', 'save-fields', 'save-table')
     * @param $urlRedirect: string
     * @param $database: object
     *
    **/
    public function __construct($config = array(), $urlRedirect, Database $database = null){

        $this->_database = $database;
        $this->_urlRedirect = $urlRedirect;

        $this->_infoConnect = $config['info-connect'];
        $this->_saveFields = $config['save-fields'];
        $this->_saveTable = $config['save-table'];

        //Génère un request_token et construit une url valide
        $connect = new TwitterOAuth($this->_infoConnect['consumerKey'], $this->_infoConnect['consumerSecret']);
        $request_token = $connect->oauth('oauth/request_token', array('oauth_callback' => $urlRedirect ));

        parent::logout($urlRedirect);

        if( !isset($_SESSION['User']['profil']) && !isset($_GET['oauth_token']) ){
            $token = $request_token['oauth_token'];
            $_SESSION['request_token'] = $token;
            $_SESSION['request_token_secret'] = $request_token['oauth_token_secret'];
            $this->_loginUrl = $connect->url( 'oauth/authorize', array('oauth_token' => $token) );
        }

    }

    /**
     *
     * Renvoi les données de l'utilisateur lorsque celui-ci est connecté sinon on renvoi l'url de connexion
     * Sauvegarde les nouveaux utilisateurs
     * @since Twitterconnect 1.0
     * @return array/string
     *
    **/
    public function login(){

        if( isset($_GET['oauth_token']) && $_SESSION['request_token']){

            //Génère un access_token pour accéder au données utilisateurs
            $connect = new TwitterOAuth($this->_infoConnect['consumerKey'], $this->_infoConnect['consumerSecret'], $_SESSION['request_token'], $_SESSION['request_token_secret']);
            $access_token = $connect->oauth("oauth/access_token", array("oauth_verifier" => $_REQUEST['oauth_verifier']));
            $_SESSION['User']['access_token'] = $access_token;

            //Authentification de l'utilisateurs
            $connect = new TwitterOAuth($this->_infoConnect['consumerKey'], $this->_infoConnect['consumerSecret'], $access_token['oauth_token'], $access_token['oauth_token_secret']);

            //Récupère les informations de l'utilisateur qui viens de ce connecter
            $_SESSION['User']['profil'] = $connect->get( 'account/verify_credentials' );
            $this->_session = $_SESSION['User']['profil'];

            parent::error($this->_session);

            //Test si une bdd est passée dans les paramètres de la fonction __construct()
            //Permet l'enregistrement des nouveaux utilisateurs
            if( $this->_database !== null ){
                $userInfos = $this->getInfosUser();
                $user = parent::findUser( $this->_database, array('network_id' => $userInfos['User']['network_id']), $this->_saveTable['name'] );

                if(!$user){
                    //Formate les données pour l'enregistrement des données utilisateurs
					foreach ($this->_saveFields as $field => $value) {
						$user[$field] = ( isset($value) && !empty($value) )? $value : $userInfos['User'][$field];
					}
					parent::saveUser( $this->_database, $user, $this->_saveTable['name'] );
                }
                $_SESSION['User']['connected'] = $user;

            }

            header('Location: ' . filter_var($this->_urlRedirect, FILTER_SANITIZE_URL));
            die();
        }

        if( isset($this->_loginUrl) && !isset($_SESSION['User']['profil']) ){
            return $this->_loginUrl;
        }else{
            $connectedUser = (isset($_SESSION['User']['connected']) && !empty($_SESSION['User']['connected']))? $_SESSION['User']['connected'] : null;
            return array('connected' => $connectedUser, 'profil' => $_SESSION['User']['profil'], 'accesToken' => $_SESSION['User']['access_token']);
        }

    }

    /**
     *
     * Récupère et formatte les informations personnelles de l'utilisateur
     * @since Twitterconnect 1.0
     * @return array
     *
    **/
    public function getInfosUser(){

        $userprofile = array();

        if( isset($_SESSION['User']['profil']) ){
            $this->_session = $_SESSION['User']['profil'];
            $userprofile['User']['network_id'] = $this->_session->id;
            $userprofile['User']['first_name'] = $this->_session->screen_name;
            $userprofile['User']['picture'] = $this->_session->profile_image_url;
        }
        return $userprofile;

    }

}
