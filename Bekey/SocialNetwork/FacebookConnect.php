<?php

namespace Bekey\SocialNetwork;

use Bekey\SocialNetwork\SocialNetworkConnect;
use Bekey\Database\Database;
use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRedirectLoginHelper;

class FacebookConnect extends SocialNetworkConnect{

	private $_className = "Facebook";
	private $_session;
	private $_helper;

	private $_permission;
	private static $_userprofile;
	private $_picturesize;

	//public function __construct($config, $urlRedirect = null, Database $database = null){
	public function __construct($config, $urlRedirect, Database $database = null){

		$this->config = $config;
		$infoConnect = $this->config['info-connect'];
		$this->database = $database;

		parent::logout($urlRedirect);

		$this->_permission = $infoConnect['scope'];
		$this->_urlRedirect = $infoConnect['redirect_uri'];
		$this->_picturesize = ( isset($infoConnect['picturesize']) && !empty($infoConnect['picturesize']) )? $infoConnect['picturesize'] : 'normal';

		//initialise l'appID et l'appSecretKey pour toute l'application
		FacebookSession::setDefaultApplication($infoConnect['appID'], $infoConnect['appSecretKey']);
		$this->_helper = new FacebookRedirectLoginHelper($infoConnect['redirect_uri']);

		//Si il y une session active et un acces_token on initialise l'objet FacebookSession
		//Sinon on génère une nouvelle session
		if(isset($_SESSION) && isset($_SESSION['fb_token'])){
			$this->_session = new FacebookSession($_SESSION['fb_token']);
			$this->urllogin = $this->login();
		}else{
			$this->_session = $this->_helper->getSessionFromRedirect();
		}

	}

	public function login(){

		//Si la session éxiste on créer le token et on le stock dans $_SESSION['fb_token']
		//Sinon on demande à l'utilisateur de se connecter
		if($this->_session){

			try{

				//if (isset($_GET['code']) && strlen($_GET['code']) >= 45) {
					$_SESSION['fb_token'] = $this->_session->getToken();

					//header('Location: ' . filter_var($this->_urlRedirect, FILTER_SANITIZE_URL));

					//Retourne les informations du profil utilisateur
					$request_profile = new FacebookRequest($this->_session, 'GET', '/me');
					self::$_userprofile = $request_profile->execute()->getGraphObject('Facebook\graphUser');
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

					//header('Location: ' . filter_var($this->_urlRedirect, FILTER_SANITIZE_URL));
					return array('graphUser' => self::$_userprofile, 'session' => $this->_session);

				//}

			}catch(\Exception $e){
				//Si une erreur est détecter ou vide et détruit la session active
				$this->messageError($e);
			}

		}else{
			return $this->_helper->getReRequestUrl($this->_permission);
		}

	}

	public static function getEmailUser(){

		//Si l'adresse mail de l'utilisateur est accessible on récupère les infos
		//Sinon on leve une exception
		if(self::$_userprofile->getEmail() != null){

			return self::$_userprofile->getEmail();

		}else{

			throw new \Exception("Votre adresse mail doit être accessible dans les paramètres Facebook");

		}

	}

	public function getPictureUrl(){

		try{

			if($this->_session){
				$request_picture = new FacebookRequest($this->_session, 'GET', '/me/picture?type='.$this->_picturesize.'&redirect=false');
				$picture = $request_picture->execute()->getGraphObject('Facebook\graphUser');

				return $picture->getProperty('url');
			}else{
				return null;
			}

		}catch(\Exception $e){

			$this->messageError($e);

		}

	}

	//Récupère les informations personnel de l'utilisateur
	public function getInfosUser(){

		if($this->_session){

			try{
				$birthday = self::$_userprofile->getBirthday();

				//Formate les données utilisateurs sous forme de tableau
				$userprofile = array();
				$userprofile['User']['networkID'] = self::$_userprofile->getId();
				$userprofile['User']['first_name'] = self::$_userprofile->getFirstName();
				$userprofile['User']['last_name'] = self::$_userprofile->getLastName();
				$userprofile['User']['gender'] = self::$_userprofile->getGender();
				$userprofile['User']['profile_picture'] = $this->getPictureUrl();
				$userprofile['User']['birthday'] = $birthday->format('Y-m-d');
				//Test si l'email est requis dans les permissions de l'applicaion
				if(in_array( 'email', $this->_permission ))
					$userprofile['User']['email'] = $this->getEmailUser();

				return $userprofile;

			}catch(\Exception $e){

				$this->messageError($e);

			}

		}else{
			return false;
		}

	}


}
