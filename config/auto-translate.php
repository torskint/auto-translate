<?php

return [

	'preserve_words' => [
		'(WEBSITE_NAME)' ,
		'(CREATED_ANNO)' ,
		'(WEBSITE_URL)' ,
		'(WEBSITE_EMAIL)' ,
		'(WEBSITE_ADDRESS)' ,
		'(WEBSITE_PHONE)' ,
		'(WEBMASTER_EMAIL)' ,
		'(WEBMASTER_NAME)' ,
		'(AUTHOR_NAME)' ,
		'(AUTHOR_EMAIL)' ,
		'(TEAG)',
		'%s',
		'\\',

		# For DropZone
		'(PDF)',
		'({{filesize}}MiB)',
		'{{maxFilesize}}MiB',
		'{{statusCode}}',
	],


	'to_replace' => [
		'(NOME DO SITE)'            => '(WEBSITE_NAME)',
		'(WEBSEITEN-NAME)'          => '(WEBSITE_NAME)',
		'(WEBSITE NAAM)' 			=> '(WEBSITE_NAME)',
		'(ИМЯ_ВЕБ-САЙТА)' 			=> '(WEBSITE_NAME)',
		'(NOME_SITO WEB)' 			=> '(WEBSITE_NAME)',
		'(NOMBRE_SITIO WEB)' 		=> '(WEBSITE_NAME)',
		'(NOMBRE DEL SITIO WEB)' 	=> '(WEBSITE_NAME)',

		'(ΤΕΑΓ)' 					=> '(TEAG)',
		'(SITO WEB_EMAIL)' 			=> '(WEBSITE_EMAIL)',

		'(SITO WEB_TELEFONO)' 		=> '(WEBSITE_PHONE)',
		'(SITIO WEB_TELÉFONO)' 		=> '(WEBSITE_PHONE)',

		'(SITIO WEB_DIRECCIÓN)' 	=> '(WEBSITE_ADDRESS)',
		'(INDIRIZZO_SITO WEB)' 		=> '(WEBSITE_ADDRESS)',
	],

	/*
	 * 
	 * Locales managed by auto-translation package, will be used by the 
	 * command "auto:translate" to generate a JSON file for each of this 
	 * locales, and by the command "translate:missing" to generate their
	 * missing translations
	 * 
	 */
	'locales' => [
		'en',
		'de',
		'bg',
		'da',
		'es',
		'it',
		'lb',
		'lt',
		'lv',
		'ro',
		'sv',
		'et',
		'pt',
		'no',
		'fi',
		'ru',
		'nl',
		'sl',
		'mn',
		'hu',
		'el',
		'pl',
		'uz',
		'hr',
		'ky',
		'hy',
		'kk',
		'tg',
		'tr',
		'sk',
		'sq',
	],

	'files' => [
		'torskint/email-service-terms-and-conditions.php',
		'torskint/liability-in-case-of-fraud-or-unauthorized-use.php',
		'torskint/privacy-policy.php',
		'torskint/refund-and-return-policy.php',
		'torskint/terms-and-conditions-of-use.php',
		'torskint/translation.php',
		'torskint/account-closing-terms.php',
		'torskint/account-terms.php',
		'torskint/anti-fraud-policy.php',

		'torskint/cookie-policy.php',
		'torskint/legal-notice.php',
	],

	/*
	 * 
	 * The base locale to use when using the command "translate:missing" to
	 * generate missing translations for other JSON files
	 * 
	 */
	'base_locale' => 'fr'

];