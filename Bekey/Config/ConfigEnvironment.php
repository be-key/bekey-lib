<?php

namespace Bekey\Config;

/**
 *
 * Librairie php qui permet de définir l'environnement du site en fonction de domaine
 * @package Config
 * @version 1.0
 * @author Biquet <anthony.papillaud@gmail.com>
 * @copyright Copyright (c) 2015, Biquet
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 **/


class ConfigEnvironment{

    public $environment;
    public $defaultEnvironment = 'dev';

    /**
     *
     * Identifie la valeur des url pour et retourne l'environnement en cours, si aucun paramètre
     * n'est spécifié l'environment de défaut est dev
     * @param $url: array: $url = array('prod' =>, 'preprod', 'dev')
     * @since ConfigEnvironment 1.0
     *
    **/
    public function __construct( $url = array() ){

        if( !empty($url) && isset($_SERVER['SERVER_NAME']) ):

            foreach($url as $key => $value):
                if( $value == $_SERVER['SERVER_NAME'] ):
                    $this->environment = $key;
                    break;
                else:
                    $this->environment = $this->defaultEnvironment;
                endif;
            endforeach;

        else:
            $this->environment = $this->defaultEnvironment;
        endif;

    }

}
