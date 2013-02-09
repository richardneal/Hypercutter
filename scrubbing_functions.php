<?php
function remove_stopWords($text, $stopWords) {
	if (empty($text)) {
		print("You must include some text from which to have the text removed.");
		return $text;
	}
	elseif ($stopwordlist == "") {
		print("Nothing to do, since there are no stopwords.");
		return $text;
	}
	else {
		$allStopWords = array();
		foreach(preg_split("/(\r?\n)/", $stopwordlist) as $line){
			$eachStopWord = explode(", ", $line);
			foreach($eachStopWord as $stopword){
				array_push($allStopWords, "/\b" . $stopword . "\b/iu");
			}
		}
	$removedString = preg_replace($allStopWords, "", $text);

	return $removedString;

	}
}

function lemmatize($text, $lemmas) {
	if (empty($text)) {
		print("You must include some text from which to have the text lemmatized.");
		return $text;
	}
	elseif ($lemmas == "") {
		print("Nothing to do, since there are no lemmas.");
		return $text;
	}
	else {

	$allLemmas = array();
	$allLemmaKEYS = array();

	foreach(preg_split("/(\r?\n)/", $lemmas) as $line){
		$lemmaLine = explode(", ", $line);
		array_push($allLemmaKEYS, $lemmaLine[0]);
		array_push($allLemmas, $lemmaLine[1]);
	}

	if (count($allLemmas) != count($allLemmaKEYS)) {
		print("The lemma list and lexeme list need the same number of elements.");
		return $text;
	}

	foreach($allLemmaKEYS as &$nextKEY){
		$nextKEY = "/\b" . $nextKEY . "\b/i";
	}

	$lemmatizedString = preg_replace($allLemmaKEYS, $allLemmas, $text);

	return $lemmatizedString;
	}
}

function removePunctuation($text, $apos, $hyphens) {
	if (empty($text)) {
		print("You must include some text from which to have the punctuation removed.");
		return $text;
	}

	switch(true) {
	case $apos == "on" && $hyphens == "on":
		$text = trim(preg_replace("#((?!['-])\pP)+#", ' ', $text));
	break;
	case $apos == "on" && $hyphens != "on":
		$text = trim(preg_replace("#((?!['])\pP)+#", ' ', $text));
	break;
	case $apos != "on" && $hyphens == "on":
		$text = trim(preg_replace("#((?![-])\pP)+#", ' ', $text));
	break; 
	default:
		$text = trim(preg_replace('#[^\p{L}\p{N}]+#u', ' ', $text));
	} 

	//$text = str_replace("-", "", $text);
	//$text = preg_replace("\w+'\w", "?", $text);
	//$text = trim(preg_replace('#[^\p{L}\p{N}]+#u', ' ', $text));
	//$text = trim(preg_replace("#((?!')\pP)+#", ' ', $text));
	return $text;
}

function consolidate($text, $consolidations) {
	if (empty($text)) {
		print("You must include some text from which to have the text removed.");
		return $text;
	}
	elseif ($consolidations == "") {
		print("Nothing to do, since there are no consolidations.");
		return $text;
	}
	else {
		$consolidationKeys = array();
		$consolidationValues = array();
		foreach(preg_split("/(\r?\n)/", $consolidations) as $line){
			$consolidationLine = explode(", ", $line);
			array_push($consolidationKeys, $consolidationLine[0]);
			array_push($consolidationValues, $consolidationLine[1]);
		}

		$removedString = str_replace($consolidationKeys, $consolidationValues, $text);

		return $removedString;
	}

}

function formatSpecial($text, $formatspecial, $specials, $common, $lowercase) {
	if (empty($text)) {
		print("You must include some text from which to have the text removed.");
		return $text;
	}
	else {
		//if ($formatspecial == "on") { // Function runs by default right now
			$allSpecials = array();
			$allSpecialKEYS = array();

			foreach(preg_split("/(\r?\n)/", $specials) as $line){
				$specialline = explode("\t", $line);
				array_push($allSpecialKEYS, $specialline[0]);
				array_push($allSpecials, $specialline[1]);
			}

			foreach($allSpecialKEYS as &$nextKEY){
				$nextKEY = "/\b" . $nextKEY . "\b/i";
			}

			$text = preg_replace($allSpecialKEYS, $allSpecials, $text);
		//}

		// De-activated for different implementation
		//if ($common == "on") {
			// HTML entities and thorn added
		//	$commonchararray = array("&ae;", "&d;", "&t;", "&e;", , "&#0541;", "&AE;", "&D;", "&T;", "&aelig;", "&eth;", "&thorn;", "&e;", "&AElig;", "&ETH;", "&THORN;", "&#0540;");
		//	$commonuniarray = array("æ", "ð", "þ", "e", "ȝ". "Æ", "Ð", "Þ", "æ", "ð", "þ", "Æ", "Ð", "Þ", "Ȝ");
		//	$text = str_replace($commonchararray, $commonuniarray, $text);
		//}

		return $text;
	}
}

function scrub_text($string, $formatting, $tags, $punctuation, $apos, $hyphens, $digits, $removeStopWords, $lemmatize, $consolidate, $formatspecial, $lowercase, $common, $stopWords = "", $lemmas = "", $consolidations = "", $specials = "", $type = 'default') {
	switch ($type) {
		case 'default':
			// Make the string variable a string with the requested elements removed.
			// Added utf-8 detection not in Scrubber
			if (!preg_match('!!u', $string)) {
				utf8_encode($string);
			}
			
			// Replace accented characters -- may not be in the right order
	$search = explode(",","ç,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø");
	$replace = explode(",","c,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o");	
	$text = str_replace($search, $replace, $string);
			
			$string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
			print("<br /> Before lowercase <br />" . substr($string, 0, 1000) . "<br />");
			if($lowercase == "on") {
				$string = strtolower($string);
				$caparray = array("Æ", "Ð", "Þ", "&#540;"); // yogh added
				$lowarray = array("æ", "ð", "þ", "&#541;");
				$string = str_replace($caparray, $lowarray, $string);
			}
			print("<br /> After lowercase, before special characters <br />" . substr($string, 0, 1000) . "<br />");
			if ($formatspecial == "on" or $common == "on") {
				$string = formatSpecial($string, $formatspecial, $specials, $common, $lowercase);
			}
			print("<br /> After special characters, before strip tags <br />" . substr($string, 0, 1000) . "<br />");
			if ($formatting == "on") {
				if($tags=="keep"){
					$string = strip_tags($string);
				}
				else {
					$string = preg_replace ( "'<(.*?)>(.*?)</(.*?)>'U", "", $string);
				}
			}
			print("<br /> After strip tags, before remove punctuation <br />" . substr($string, 0, 1000) . "<br />");
			if ($punctuation == "on") {
				$string = removePunctuation($string, $apos, $hyphens);
			} 
			print("<br /> After remove punctuation, before remove digits <br />" . substr($string, 0, 1000) . "<br />");
			if ($digits == "on") {
				$string = str_replace(range(0, 9), '', $string);
			} 
			print("<br /> After remove digits, before remove stopwords <br />" . substr($string, 0, 1000) . "<br />");
			if (isset($_SESSION['stopwordlist'])) {
				$string = remove_stopWords($string, $stopwordlist);
			}
			print("<br /> After remove stopwords, before lemmatize <br />" . substr($string, 0, 1000) . "<br />");
			if ($lemmatize == "on") {
				$string = lemmatize($string, $lemmas);
			}
			print("<br /> After lemmatize, before consolidation <br />" . substr($string, 0, 1000) . "<br />");
			if ($consolidate == "on") {
				$string = consolidate($string, $consolidations);
			}
			print("<br /> After consolidation <br />" . substr($string, 0, 1000) . "<br />");

			// Clean extra spaces
			$string = preg_replace("/\s\s+/", " ", $string);
			return $string;

			break;
		case 'xml':
			// Make the string variable a string with the requested elements removed.
			$string = remove_stopWords($string, $stopWords);
			strip_tags($string);
			break;
		case 'sgml':
			// Make the string variable a string with the requested elements removed.
			$string = remove_stopWords($string, $stopWords);
			strip_tags($string);
			break;
	}
	return $string;
}

$formatting = "";
$punctuation = "";
$apos = "";
$hyphens = "";
$digits = "";
$removeStopWords = "";
$lemmatize = "";
$consolidate = "";
$lowercase = "";
$formatspecial = "";
$common = "";

if(isset($_SESSION["formattingbox"]))
	$formatting = $_SESSION["formattingbox"];
	$tags = $_SESSION["tags"];
if(isset($_SESSION["punctuationbox"])) {
	$punctuation = $_SESSION["punctuationbox"];
	if(isset($_SESSION["aposbox"])) {
		$apos = $_SESSION["aposbox"];
	}
	if(isset($_SESSION["hyphensbox"])) {
		$hyphens = $_SESSION["hyphensbox"];
	}
}
if(isset($_SESSION["digitsbox"]))
	$digits = $_SESSION["digitsbox"];
if(isset($_SESSION["stopwordlist"]))
	$removeStopWords = $_SESSION["stopwordlist"];
if(isset($_SESSION["lemmabox"]))
	$lemmatize = $_SESSION["lemmalist"];
if(isset($_SESSION["consolidationslist"]))
	$consolidate = $_SESSION["consolidationslist"];
if(isset($_SESSION["lowercasebox"]))
	$lowercase = $_SESSION["lowercasebox"];
if(isset($_SESSION["specialbox"]))
	$formatspecial = $_SESSION["specialcharsbox"];
//Disabled pending development
//if(isset($_SESSION["commonbox"]))
//	$common = $_SESSION["commonbox"];

$stopwords = $_SESSION["stopwordlist"];
$lemmas = $_SESSION["lemmalist"];
$consolidations = $_SESSION["consolidationslist"];
$specials = $_SESSION["specialcharslist"];

$text = scrub_text($text, $formatting, $tags, $punctuation, $apos, $hyphens, $digits, $removeStopWords, $lemmatize, $consolidate, $formatspecial, $lowercase, $common, $stopwords, $lemmas, $consolidations, $specials);
?>