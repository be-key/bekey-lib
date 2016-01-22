<?php

namespace Bekey\Paybox\Config;

class ConfigPaybox{
	
	static public $config = array(
		'pbx_site' => '1999888',
		'pbx_rang' => '32',
		'pbx_id' => '2',
		'pbx_retour' => 'Mt:M;Ref:R;Auto:A;Erreur:E;Sign:K',
		'pbx_hmac' => '0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF',
		'pbx_forcePaymentType' => false,
		'pbx_environnement' => 'dev',
		'pbx_urlEffectue' => 'validate_payment.php',
		'pbx_urlRefuse' => 'denied_payment.php',
		'pbx_urlAnnule' => 'cancel_payment.php',
		'pbx_urlAttente' => 'waiting_payment.php'
	);
	
}

