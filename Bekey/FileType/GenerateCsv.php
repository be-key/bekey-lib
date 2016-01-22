<?php

namespace Bekey\FileType;

/**
 *
 * Librairie php pour générer des fichers CSV à la volée depuis une base de donnée, un tableau
 * @package FileType
 * @version 1.0.1
 * @author Biquet <anthony.papillaud@gmail.com>
 * @copyright Copyright (c) 2015, Biquet
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 **/

class GenerateCsv{

    /**
     *
     * Retourne la génération d'un fichier csv
     * @since GenerateCsv 1.0.1
     * @param $cellName : array nom des cellules à générer
     * @param $data : array données à insérer dans le fichier csv
     * @param $output : string emplacement de sauvegarde du fichier apres l'export
     * (ATTENTION : Les données doivent être dans le même ordre que le nom des cellules)
     * @param $filename : string nom du fichier de sortie
     * @return file.csv
     *
    **/
    //public static function getFile( $cellName = array(), $data = array(), $output, $filename){

    public static function getFile( $cellName = array(), $data = array(), $filename = 'default', $output = null){

        //Nom du dossier de sortie
        if( $output == null )
            $output = 'php://output';

        //Formatage noms des cellules
        $content = implode(';', $cellName) . "\n";

        //Formatage données
        foreach($data as $key => $value):
            $content .= implode(';', $value) . "\n";
        endforeach;

		$csv = fopen($output, "w");
		fwrite($csv, $content);
		fclose($csv);

    }

}
