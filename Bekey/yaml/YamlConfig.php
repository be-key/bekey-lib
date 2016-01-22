<?php

namespace Bekey\Yaml;

use Symfony\Component\Yaml\Yaml;

/**
 *
 * Librairie pour extraire le contenu des fichier yml, et les utiliser comme fichier de configuration externe
 * @package Yaml
 * @version 1.0
 * @author Biquet <anthony.papillaud@gmail.com>
 * @copyright Copyright (c) 2015, Biquet
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 **/

class YamlConfig{

    /**
     *
     * lis et récupère le contenur du fichier
     * @since YamlConfig 1.0
     * @param $options: array('path', 'filename')
     * @return le contenu du ficher sous forme de array
     *
    **/
    public static function config( $options = array() ){

        $path = ( isset($options['path']) )? $options['path'] : 'default';
        $fileName = ( isset($options['filename']) )? $options['filename'] : 'default';
        $yml = $path.'/'.$fileName.'.yml';

        $config = Yaml::parse( file_get_contents($yml) );

        if(!$config)
            throw new \Exception("Un ou plusieurs fichiers de configuration sont manquants ou ne sont pas configuré");

        return $config;

    }

}
