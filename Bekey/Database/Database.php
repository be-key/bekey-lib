<?php

namespace Bekey\Database;
use Symfony\Component\Yaml\Yaml;

class Database{

	public $database;

	/**
     *
     * Récupérations des informations de connexion à la base de données
     * @since Database 1.0
	 * @param $useDbConfig : string - nom dela configutation yml à utilisé
	 * @param $useDbConfig : array - information de connexion de l'application parent
     *
    **/
	public function __construct($useDbConfig = 'default'){

		if(is_array($useDbConfig) ){
			$this->database = $useDbConfig;
			if( !isset($useDbConfig['charset']) )
				$this->database['charset'] = 'utf8';
		}elseif( file_get_contents('lib/Bekey/yaml/database.yml') && !is_array($useDbConfig) ){
			$this->database = Yaml::parse( file_get_contents('lib/Bekey/yaml/database.yml') );
			$this->database = $this->database['databases'][$useDbConfig];
		}

	}

	public function databaseConnect(){

		$databaseConnect = new \PDO('mysql:host='.$this->database['hostname'].';dbname='.$this->database['database'].';charset='.$this->database['charset'], $this->database['username'], $this->database['password']);
		return $databaseConnect;

	}

	public function save($params){

		try{
			foreach( $params['fields'] as $field => $value){
				$fieldsNames[] = $field;
				$fieldsValues[] = ":".$field;
			}

			$database = $this->databaseConnect();
			$insert = $database->prepare("INSERT INTO `".$params['table']."` (".implode(', ', $fieldsNames).") VALUES (".implode(', ', $fieldsValues).")");

			foreach( $params['fields'] as $field => $value){
				$insert->bindValue(":".$field, $value);
			}

			$insert->execute();
		}catch(\Exception $e){
			echo $e->getMessage();
		}

	}

	public function find( $type = 'first', $params = array() ){

		$tableName = $params['table'];
		$database = $this->databaseConnect();

		$status = "";
		$where = "";

		if( isset($params['conditions']['status']) )
			$status = "WHERE status = " . $params['conditions']['status'];

		if( (empty($params['conditions']['id']) || !isset($params['conditions']['id'])) && $type != 'all'){
			$minId = $database->prepare("SELECT * FROM `".$tableName."`" . $status . " LIMIT 1");
			$minId->execute();
			$resultMinId = $minId->fetch(\PDO::FETCH_ASSOC);
			$params['conditions']['id'] = $resultMinId['id'];
		};

		if( isset($params['conditions']) ){

			foreach($params['conditions'] as $condition => $value){
				if( !empty($value) ){
					$condition = ( strpos($value, '=') === false ) ? $condition." =" : $condition ;
					$conditions[] = $condition." ".$value;
				}
			}
			$where .= implode(' AND ', $conditions);

		}

		if( isset($params['order']) && count($params['order']) == 2 && $type != 'neighbors')
			$where .= ' ORDER BY ' . $params['order'][0] . ' ' . $params['order'][1];

		if( empty($params['conditions']['id']) && $type == 'first')
			$where .= ' LIMIT 1 ';

		if( isset($params['limit']) && $type != 'neighbors')
			$where .= ' LIMIT ' . $params['limit'];

		$fields = '*';
		if( isset($params['fields']) )
			$fields = $params['fields'][0];

		if( $type == 'neighbors' ){
			$request = $this->neighborsRequest($fields, $tableName, $where);
		}else{
			if( !empty($where) )
				$where = " WHERE " . $where;

			$request = "SELECT " . $fields . " FROM `".$tableName."`" . $where;
		}

		$read = $database->prepare($request);
		$read->execute();

		if($type == 'first'){
			$result = $read->fetch(\PDO::FETCH_ASSOC);
		}elseif($type == 'neighbors'){
			$result = $read->fetchAll(\PDO::FETCH_ASSOC);

			$neighbors = array('item' => '', 'neighbors' => array('prev' => '', 'next' => ''));
			foreach ($result as $k => $v):
				if( $v['position'] == 'current' )
					$neighbors['item'] = $v;
				if( $v['position'] == 'next' || $v['position'] == 'prev' )
					$neighbors['neighbors'][$v['position']] = $v;
			endforeach;
			$result = $neighbors;

		}else{
			$result = $read->fetchAll(\PDO::FETCH_ASSOC);
		}

		return $result;
	}

	/**
     *
     * Requête qui permet de rechercher un ligne ainsi que ces deux extrémitées
	 * Utilisé pour connaitre la ligne suivante et précédente
     * @param $fields: string champs de la base à afficher
	 * @param $tableName: string nom de la table
	 * @param $where: string conditions de la requête
     * @since Database 1.0
     * @return sting
     *
    **/
	public function neighborsRequest($fields, $tableName, $where){

		$nextId = preg_replace('/id =/', 'id >', $where, 1);
		$prevId = preg_replace('/id =/', 'id <', $where, 1);

		$request = "SELECT " . $fields . ", 'current' AS `position` FROM `" . $tableName . "` WHERE " . $where ." LIMIT 1 UNION
					(SELECT " . $fields . ", 'next' AS `position` FROM `" . $tableName . "` WHERE " . $nextId . " ORDER BY id ASC LIMIT 1 ) UNION
					(SELECT " . $fields . ", 'prev' AS `position` FROM `" . $tableName . "` WHERE " . $prevId . " ORDER BY id DESC LIMIT 1 ) ORDER BY id ASC";

		return $request;

	}

}
