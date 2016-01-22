<?php

namespace Bekey\Paybox\Config;

class ConfigErrorPaybox{
	
	static public $errorMessage = array(
		'00000' => "Opération réussie.",
		'00001' => "La connexion au centre d’autorisation a échoué ou une erreur interne est survenue.",
		'001xx' => "Paiement refusé par le centre d'autorisation.",
		'00003' => "Erreur Paybox.",
		'00004' => "Numéro de porteur ou cryptogramme visuel invalide.",
		'00006' => "Accès refusé ou site/rang/identifiant incorrect.",
		'00008' => "Date de fin de validité incorrecte.",
		'00009' => "Erreur de création d’un abonnement.",
		'00010' => "Devise inconnue.",
		'00011' => "Montant incorrect.",
		'00015' => "Paiement déjà effectué.",
		'00016' => "Abonné déjà existant.",
		'00021' => "Carte non autorisée.",
		'00029' => "Carte non conforme.",
		'00030' => "Temps d’attente > 15 mn au niveau de la page de paiements.",
		'00031' => "Réservé",
		'00032' => "Réservé",
		'00033' => "Code pays de l’adresse IP du navigateur de l’acheteur non autorisé.",
		'00040' => "Opération sans authentification 3-DSecure, bloquée par le filtre.",
		'99999' => "Opération en attente de validation par l’émetteur du moyen de paiement."
	);
	
	static public $messageCentreAutorisation = array(
		'00' => 'Transaction approuvée ou traitée avec succès',
		'01' => 'Contacter l\'emetteur de la carte',
		'02' => 'Contacter l\'emetteur de la carte',
		'03' => 'Commerçant invalide',
		'04' => 'Conserver la carte',
		'05' => 'Ne pas honorer',
		'07' => 'Conserver la carte, conditions spéciales',
		'08' => 'Approuver après identification du porteur',
		'12' => 'Transaction invalide',
		'13' => 'Montant invalide',
		'14' => 'Numéro de porteur invalide',
		'15' => 'Emetteur de carte inconnu',
		'17' => 'Annulation client',
		'19' => 'Répéter la transaction ultérieurement',
		'20' => 'Réponse erronée (erreur dans le domaine serveur)',
		'24' => 'Mise à jour de fichier non supportée',
		'25' => 'Impossible de localiser l’enregistrement dans le fichier',
		'26' => 'Enregistrement dupliqué, ancien enregistrement remplacé',
		'27' => 'Erreur en « edit » sur champ de mise à jour fichier',
		'28' => 'Accès interdit au fichier',
		'29' => 'Mise à jour de fichier impossible',
		'30' => 'Erreur de format',
		'33' => 'Carte expirée',
		'38' => 'Nombre d’essais code confidentiel dépassé',
		'41' => 'Carte perdue',
		'43' => 'Carte volée',
		'51' => 'Provision insuffisante ou crédit dépassé',
		'54' => 'Date de validité de la carte dépassée',
		'55' => 'Code confidentiel erroné',
		'56' => 'Carte absente du fichier',
		'57' => 'Transaction non permise à ce porteur',
		'58' => 'Transaction interdite au terminal',
		'59' => 'Suspicion de fraude',
		'60' => 'L\'accepteur de carte doit contacter l\'acquéreur',
		'61' => 'Dépasse la limite du montant de retrait',
		'63' => 'Règles de sécurité non respectées',
		'68' => 'Réponse non parvenue ou reçue trop tard',
		'75' => 'Nombre d’essais code confidentiel dépassé',
		'76' => 'Porteur déjà en opposition, ancien enregistrement conservé',
		'89' => 'Echec de l’authentification',
		'90' => 'Arrêt momentané du système',
		'91' => 'Emetteur de cartes inaccessible',
		'94' => 'Demande dupliquée',
		'96' => 'Mauvais fonctionnement du système',
		'97' => 'Echéance de la temporisation de surveillance globale'
	);
	
}