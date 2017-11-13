<?php
/**
 * retourne le code http venant de fsockopen
 * 
 * @param string $header
 * @return string 
 */

function getHttpResponse($header)
{
	if($header != '')
		return substr($header,9,3);
	else
		return false;
}


/**
 * génère un code aléatoire composé de chiffres et de lettres (maj et min) sur $i caractères (10 par défaut)
 *
 * @param int $i nombre de caractères du code
 * @return string code aléatoire 
 */
function generateRandomRefId($i = 10)
{
	$characts = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$characts .= '1234567890';
	$code = '';

	for ($k = 0; $k < $i; $k++)
	{	//10 est le nombre de caractères
		$code .= substr($characts, rand() % (strlen($characts)), 1);
	}

	return $code;
}

/**
 * retourne vrai si $string est uns chaine XML valide, faux sinon
 *
 * @param string $string chaine à tester
 * @return bool 
 */
function isXMLstring($string, $debug = false)
{
	if ($debug)
		var_dump("string = $string");
	//on vérifie si des balises sont présentes avec ou sans déclaration xml
	preg_match('#^(<\?xml.+\?>[\r\n ]*)?<([^( |>)]+).*>.*</(.+)>$#s', $string, $output);
	preg_match('#^(<\?xml.+\?>[\r\n ]*)?<([^( |>)]+).*/>$#s', $string, $output2);
	if ($debug)
	{
		var_dump($output);
		var_dump($output2);
	}

	//retourne vrai si des balises sont présentes et si les balises ont le meme nom
	return (count($output) != 0 && ($output[2] == $output[3])) || count($output2) != 0;
}

/**
 * retourne vrai si l'objet $object et de type $type, faux sinon
 *
 * @param string $type nom de la classe attendue
 * @param mixed $object objet à tester
 * @return bool 
 */
function isType($type, $object)
{
	//retourne faux directement si le paramètre n'est pas un objet
	if (!is_object($object))
		return false;

	return (get_class($object) == $type || in_array($type, class_parents($object)));
}

/**
 * retour vrai si l'objet en paramètre est un objet XMLElement, faux sinon
 * 
 * @param mixed $input
 * @return bool 
 */
function isXMLElement($input)
{
	return isType('XMLElement', $input);
}

/**
 * retour vrai si l'objet en paramètre est un objet FormField, faux sinon
 * 
 * @param mixed $input
 * @return boolean 
 */
function isFormField($input)
{
	return isType('FormField', $input);
}

/**
 * retour vrai si l'objet en paramètre est un objet Form, faux sinon
 * 
 * @param mixed $input
 * @return boolean 
 */
function isForm($input)
{
	return isType('Form', $input);
}

/**
 * retourne vrai si $input est un objet de classe SimpleXMLElement, faux sinon
 *
 * @param mixed $input objet à tester
 * @return bool 
 */
function isSimpleXMLElement($input)
{
	return isType('SimpleXMLElement', $input);
}

/**
 * converti une chaine en chaine valide pour une balise XML, exemple : OptionsPaiement devient options-paiment
 * @param string $name 
 */
function normalizeName($name)
{
	$string = strtolower($name[0]);
	$i = 1;
	for ($i; $i < strlen($name); $i++)
	{
		if (ord($name[$i]) >= ord('A') && ord($name[$i]) <= ord('Z'))
		{
			$string .= '-';
		}

		$string .= strtolower($name[$i]);
	}

	return $string;
}

/**
 * insère une erreur en haut du fichier de log, en le créant s'il n'existe pas déjà
 *
 * @param string $func nom de la fonction reportant le bug
 * @param string $msg description de l'erreur
 */
function insertLog($func, $msg)
{
	Top3Logger::insertLogTop3($func, $msg);
}

function is_utf8($string)
{
	return !strlen(
			preg_replace(
				',[\x09\x0A\x0D\x20-\x7E]'			# ASCII
				.'|[\xC2-\xDF][\x80-\xBF]'			 # non-overlong 2-byte
				.'|\xE0[\xA0-\xBF][\x80-\xBF]'		 # excluding overlongs
				.'|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}'  # straight 3-byte
				.'|\xED[\x80-\x9F][\x80-\xBF]'		 # excluding surrogates
				.'|\xF0[\x90-\xBF][\x80-\xBF]{2}'	  # planes 1-3
				.'|[\xF1-\xF3][\x80-\xBF]{3}'		  # planes 4-15
				.'|\xF4[\x80-\x8F][\x80-\xBF]{2}'	  # plane 16
				.',sS', '', $string));
}