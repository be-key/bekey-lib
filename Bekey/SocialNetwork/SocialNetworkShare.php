<?php

namespace Bekey\SocialNetwork;

/**
 *
 * Librairie php pour générer dynamiquement les liens de partage vers les réseaux sociaux
 * @package SocialNetworkShare
 * @version 1.0.2
 * @author Biquet <anthony.papillaud@gmail.com>
 * @copyright Copyright (c) 2015, Biquet
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 **/

class SocialNetworkShare{

    public static $url = array(
        'Facebook' => "https://www.facebook.com/sharer/sharer.php?",
        'Twitter'  => "https://twitter.com/intent/tweet?",
        'Google'   => "https://plus.google.com/share?",
        'Linkedin' => "https://www.linkedin.com/shareArticle?mini=true&"
    );

    /**
     *
     * Création des url de partages
     * @since SocialNetworkShare 1.0.2
     * @param $network : string nom du réseaux social
     * @param $messageParams : array informations à partager (content, url, summary, source, img)
     * @return string url de partage
     *
    **/
    public static function url( $network, $messageParams = array() ){

        $network = ucfirst(strtolower($network));
        $url = self::$url[$network];

        //Partage facebook
        if( $network == "Facebook" && isset($messageParams['url']) )
            $url .= 'u='.$messageParams['url'];

        if( $network != "Facebook" && isset($messageParams['url']) )
            $url .= 'url='.$messageParams['url'];

        //Partage Twitter
        if( $network == "Twitter"){

            if( isset($messageParams['content']) ){

                $regex = "/([#][a-z0-9])\\w+/i";
                preg_match_all($regex, $messageParams['content'], $matches);

                //Supprime les hashtags du contenu
                $content = preg_replace_callback($regex, function ($value){ $result = ""; } , $messageParams['content']);
                //Supprime les doubles espace
                $content = preg_replace('/\s{2,}/', '', $content);
                $url .= ( isset($messageParams['url']) )? "&text=" . $content : "text=" . $content;

                if(!empty($matches[0]))
                    $url .= "&hashtags=" . str_replace('#', '', implode(",", $matches[0]) );

            }

        }

        //Partage Linkedin
        if( $network == "Linkedin"){
            foreach($messageParams as $key => $params):
                if( $key != 'url' )
                    $url .= "&" . $key . "=" . $params;
            endforeach;
        }

        return $url;

    }

    /**
     *
     * Création des balises openGraph pour facebook
     * @since SocialNetworkShare 1.0.1
     * @param $openGraphParams : array nom des différentes balises open graph mis en place par facebook
     * plus d'infos https://developers.facebook.com/docs/sharing/opengraph/object-properties - https://developers.facebook.com/docs/reference/opengraph
     * @important linkedin utlise également les balises openGraph pour le partage
     * plus d'infos https://developer.linkedin.com/docs/share-on-linkedin
     * @return string balises openGraph
     *
    **/
    public static function openGraph( $openGraphParams = array() ){

        $og = "";
        foreach($openGraphParams as $k => $v):
            $og .='<meta property="' . $k . '" content="' . $v . '"/>'."\n";
        endforeach;
        return $og;

    }

    /**
     *
     * Création des balises card type (image) pour le partage twitter
     * @since SocialNetworkShare 1.0.1
     * @param $cardTypesParams : array nom des différentes balises "image" mis en place par twitter
     * plus d'infos https://dev.twitter.com/cards/types
     * @return string balises card type
     *
    **/
    public static function cardTypes($cardTypesParams = array()){

        $ct = "";
        foreach($cardTypesParams as $k => $v):
            $ct .='<meta name="twitter:' . $k . '" content="' . $v . '"/>'."\n";
        endforeach;
        return $ct;

    }

}
