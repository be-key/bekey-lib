<?php

namespace Bekey\SocialNetwork;

use Symfony\Component\Yaml\Yaml;
use Bekey\Database\Database;

/**
 *
 * Permet de mettre en place une connexion sur l'application via les réseaux sociaux
 * Réseaux sociaux disponible : Google, Facebook, Twitter
 * @package Bekey/SocialNetwork
 * @version 1.0
 * @author Biquet <anthony.papillaud@gmail.com>
 * @copyright Copyright (c) 2015, Biquet
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 **/

class SocialNetworkConnect{

	private $_networks;
	private $_nameActionLogout = "logout";

	private static $_instance = array();

	/**
     *
     * Créer les connexion avec les différents réseaux, renvoie les urls de connexion ou les informations de l'utilisateur
     * @since SocialNetworkConnect 1.0
	 * @param $database : object
	 * @param $networks : array listing des reseaux utilisés
	 * @param $configPath : string Destination des fichiers.yml
	 * @param $configName : array Nom de la configuration
	 * @param $urlRedirect : array Url de redirection apres la connexion
     * @return array
     *
    **/
	public function __construct( Database $database = null, $networks = array( 'Facebook', 'Google', 'Twitter'), $configPath = "lib/Bekey/Yaml/", $configName = null , $urlRedirect = null){

		$this->_networks = $networks;
		$config = $this->_getYmlConfig( array( 'path' => $configPath,  'name' => $configName) );

		//Créer les instance pour chaque réseaux sociaux
		foreach ($networks as $network) {
			$networkClass = "Bekey\SocialNetwork\\".$network."Connect";
			$newNetwork = new $networkClass( $config[$network], $this->_getUrlRedirect($urlRedirect), $database );
			self::$_instance[$network . 'Instance'] = array( 'connectInfo' => $newNetwork->login(), 'userInfo' => $newNetwork->getInfosUser());
		}

	}

	/**
     *
     * Récupère les fichiers de configuration de chaque réseaux et le compile en un seul tableau
     * @since SocialNetworkConnect 1.0
	 * @param $config : array('path', 'name') nom et destination des fichiers.yml dans l'application
     * @return array
     *
    **/
	private function _getYmlConfig( $config = array() ){

		foreach ($this->_networks as $network) {

			$fileName = $configName = strtolower($network."-connect");

			if( $config['name'] != null )
				$configName = $config['name'];

			$yml = $config['path'].$fileName.'.yml';

			$yamlConfig[$network] = Yaml::parse( file_get_contents($yml) );
			$yamlConfig[$network] = $yamlConfig[$network][ $configName ];

			if(!$yamlConfig)
				throw new \Exception("Un ou plusieurs fichiers de configuration sont manquants ou ne sont pas configuré");

		}

		return $yamlConfig;

	}

	/**
     *
     * Si aucune url de redirection n'est précisée on retourne les l'url de la page de connexion
     * @since SocialNetworkConnect 1.0
	 * @param $urlRedirect : string url de redirection
     * @return string
     *
    **/
	private function _getUrlRedirect($urlRedirect = null){
		if( $urlRedirect == null ):
			return 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
		else:
			return $urlRedirect;
		endif;
	}

	/**
     *
     * Déconnecte l'utilisateur, et supprime les informations de connexion
     * @since SocialNetworkConnect 1.0
     * @param $urlRedirect: sting
	 *
    **/
	public function logout($urlRedirect = null){

		if( isset($_GET['action']) && $_GET['action'] == $this->_nameActionLogout ){

			/*if( isset($_SESSION['fb_token']) && !empty($_SESSION['fb_token']) )
			unset($_SESSION['fb_token']);

			if( isset($_SESSION['google_token']) && !empty($_SESSION['google_token']) )
			unset($_SESSION['google_token']);*/

			if( isset($_SESSION['User']) && !empty($_SESSION['User']) )
				unset($_SESSION['User']);

			$urlRedirect = $this->_getUrlRedirect($urlRedirect);

			header('Location: ' . filter_var($urlRedirect, FILTER_SANITIZE_URL));
		}

	}

	/**
     *
     * Récupère les messages d'erreur
     * @since SocialNetworkConnect 1.0
     * @param $e: object
     * @return string
	 *
    **/
    public function error($e){
		var_dump($e);
		die();
		//Test si il s'agît d'un erreur de connexion au réseaux sociaux. si oui redirige vers la page de login
        if( array_key_exists('errors', get_object_vars($e)) || strpos(get_class($e),'Facebook') !== false){

            session_unset();
            session_destroy();

            $urlRedirect = $this->_getUrlRedirect($urlRedirect);
            header('Location: ' . filter_var($urlRedirect, FILTER_SANITIZE_URL));

        }

    }

	/**
     *
     * Sauvegarde les nouveaux utilisateurs en base
     * @since SocialNetworkConnect 1.0
	 * @param $database : object
	 * @param $fields : array
	 * @param $table : array
     *
    **/
	public function saveUser(Database $database, $fields, $table){
		$database->save( array( 'fields' => $fields, 'table' => $table) );
	}

	/**
     *
     * Recheche un utilisateur en base
     * @since SocialNetworkConnect 1.0
	 * @param $database : object
	 * @param $fields : array
	 * @param $table : array
     * @return string
     *
    **/
	public function findUser(Database $database, $fields, $table){

		$result = $database->find(
			'first',
			array( 'conditions' => $fields, 'table' => $table )
		);
		return $result;

	}

	public static function getInstance($name = null) {
		return self::$_instance[ $name . 'Instance'];
	}

}
