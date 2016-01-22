<?php

namespace Bekey\SocialNetwork;

use Bekey\Database\Database;

class GoogleConnect extends SocialNetworkConnect{

	private $_className = "Google";
	private $_user;
	private $_userprofile;
	private $_urlRedirect;
	private $_permission;
	private $_session;

	public function __construct($config, $urlRedirect, Database $database = null){

		$this->_urlRedirect = $urlRedirect;
		$this->database = $database;
		$this->config = $config;
		$this->_permission = $config['info-connect']['scope'];

		require_once realpath(dirname(__FILE__) . '/../../Google/autoload.php');

		$this->_user = new \Google_Client();
		$this->_user->setClientId($config['info-connect']['client_id']);
		$this->_user->setClientSecret($config['info-connect']['client_secret']);
		$this->_user->setRedirectUri($this->_urlRedirect);
		$this->_user->addScope($this->_permission);

		$this->_service = new \Google_Service_Oauth2($this->_user);

	}

	public function login(){

		//Si $_GET['code'] est vide on redirige l'utilisateur vers la page d'authentification Google, le code est requis pour obtenir un accesToken
		//Puis on enregistre l'accesToken en session avant de rediriger l'utilisteur

		if (isset($_GET['code']) && strlen($_GET['code']) <= 45) {

		  $this->_user->authenticate($_GET['code']);
		  $_SESSION['google_token'] = $this->_user->getAccessToken();
		  header('Location: ' . filter_var($this->_urlRedirect, FILTER_SANITIZE_URL));
		}

		//Si la session éxiste on créer le token et on le stock dans $_SESSION['access_token']
		//Sinon on demande à l'utilisateur de se connecter
		if (isset($_SESSION['google_token']) && $_SESSION['google_token']) {
		  $this->_user->setAccessToken($_SESSION['google_token']);
		  $this->_session = $_SESSION['google_token'];
		} else {
		  $this->_authUrl = $this->_user->createAuthUrl();
		}

		//Retourne le lien de connexion ou les informations utilisateurs
		if ( isset($this->_authUrl) ) {
		  	return $this->_authUrl;
		}else{

			$this->_userprofile = $this->_service->userinfo->get();

			//Test si l'utilisateur est déjà enregistré en bdd
			if( $this->database !== null ){
				$userinfos = $this->getInfosUser();
				$user = parent::findUserInfo( $userinfos['User']['network_id'], $this->config['save-table']['name'] );
				if( !$user ){
					//Formate les données pour l'enregistrement des données utilisateurs
					foreach ($this->config['save-fields'] as $field => $value) {
						$user[$field] = ( isset($value) && !empty($value) )? $value : $userinfos['User'][$field];
					}
					parent::saveUserInfo( $user, $this->config['save-table']['name'] );

				}
			}

			return $this->_userprofile;

		}

	}

	//Récupère les informations personnel de l'utilisateur
	public function getInfosUser(){

		if($this->_session){

			try{

				//Formate les données utilisateurs sous forme de tableau
				$userprofile = array();
				$userprofile['User']['networkID'] = $this->_userprofile->id;
				$userprofile['User']['first_name'] = $this->_userprofile->givenName;
				$userprofile['User']['last_name'] = $this->_userprofile->familyName;
				$userprofile['User']['gender'] = $this->_userprofile->gender;
				$userprofile['User']['profile_picture'] = $this->_userprofile->picture;

				//Test si l'email est requis dans les permissions de l'applicaion
				if( in_array( 'https://www.googleapis.com/auth/userinfo.email', $this->_permission) )
					$userprofile['User']['email'] = $this->_userprofile->email;

				return $userprofile;

			}catch(\Exception $e){

				parent::messageError($e);

			}

		}else{
			return false;
		}

	}

}
