<?
/*******************************
Version : 1.0.0.0
Revised : samedi 25 mars 2017, 16:23:58 (UTC+0100)
*******************************/

/**
 * Core Localization
 *
 * common [French]
 *
*/

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	// List and over numbered things
	'PREVIOUS' 				=> 'Précédent',
	'NEXT' 					=> 'Suivant',

	// 404
	'DIAPO'					=> 'Erreur 404',
	'E404'					=> 'Erreur 404',
	'I404'					=> 'Illustration pour l’erreur 404, page non trouvée',
	'PAGE_NOT_FOUND'		=> 'Page non trouvée',
	'PAGE_NOT_FOUND_LONG'	=> 'Cette page ne semble pas exister pour l’instant. Heureusement, il y en a plein d’autres ! Utilisez le menu afin de poursuivre votre navigation.',
	
));
?>