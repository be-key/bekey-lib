<?php

namespace Bekey\Inflector;

/**
 *
 * Librairie php pour gérer les variations de mot comme les mises au pluriel ou les mises en Camel
 * @package IpAddress
 * @version 1.0
 * @author Biquet <anthony.papillaud@gmail.com>
 * @copyright Copyright (c) 2015, Biquet
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 **/

class Inflector{

    /**
     *
     * Convertit les caratères accentués et les espaces par le cartère de remplacement
     * @since Inflector 1.0
     * @param $word : string chaine de caratère à convertir
     * @param $replace : string caratère de remplacement
     * @return string
     *
    **/
    public static function slug($word, $replace = "_"){
        $word = str_replace(' ',$replace , $word);
        //$word = strtolower();
        return $word;

    }

}
