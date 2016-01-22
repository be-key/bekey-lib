<?php

require './lib/Bekey/Autoloader/autoloader.php'; 
use Bekey\Autoloader\Autoloader;
Autoloader::register(); 

use Bekey\Paybox\Paybox;
use Bekey\Paybox\PayboxDevise;

session_start();

$locale = 'en-us';
$paybox = new Paybox();
$devise = new PayboxDevise();

$montant = 2;

$montant = $devise->postAmountAndDevise($montant, $locale);
$deviseCode = $devise->devise[$locale]['iso_num'];

$card = $paybox->pbx_paymentCard;

$form = $paybox->payboxRequiredInput($montant['amount'], 'bon de commande n° 11', $deviseCode, 'test@paybox.com', 'fra', 'Envoyer', $card[3]);

echo $form;


/*use Bekey\Facebook\Facebook;

$appID = '951219708253883';
$appSecretKey = '1f8e7507dacda8821b6bf52b715ce8f3';
$urlRedirect = 'http://test.dev/index.php';

$facebook = new Facebook($appID, $appSecretKey, $urlRedirect);

$profile = $facebook->login();

if($profile === null){
	
}else{
	var_dump($profile);
	echo '<a href="?action=logout">Se déconnecter</a>';
}

var_dump($facebook->getPagesLike());
var_dump($facebook->getInfosUser());*/



