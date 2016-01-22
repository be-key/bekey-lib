<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>
<body>

<?php 
	require './lib/Bekey/Autoloader/autoloader.php'; 
	use Bekey\Autoloader\Autoloader;
	Autoloader::register(); 
	
	session_start();
	
	use Bekey\Paybox\Paybox;
	use Bekey\Paybox\PayboxDevise;
	use Bekey\Database\Database;
	
	$database = new Database();
	$payboxReturn = new Paybox( $database );
	$devise = new PayboxDevise();
	
	$deviseSymbole = $devise->devise;
	foreach($deviseSymbole as $langue => $v):
		if( $v['iso_num'] == $_SESSION['Devise'])
		$deviseSymbole = $v['symbol'];
	endforeach;
	
	$montant = ( !isset($_GET['Mt']) )? null : $_GET['Mt'];
	$message = $payboxReturn->getErrorMessage($_GET['Erreur']);
	
	echo 'Récapitulatif de la commande : <br><br>';
	echo 'Référence de la commande : '.$_GET['Ref'].'<br>';
	echo 'Montant de la commande : '.$payboxReturn->unconvertedIntoCents($montant).' '.$deviseSymbole.'<br><br>';
	echo 'Etat du paiement : '. $message['message'];
	
	$payboxReturn->saveOrder( $_GET['Erreur'], $_GET['Ref'], $message['message'], $montant, $_SESSION['Devise'] );

?>

</body>
</html>
