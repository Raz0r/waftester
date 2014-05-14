<?php
	require_once('common.php');
	
	echo "Start program".PHP_EOL;
	if(isset($settings['proxy']))
		echo "Proxy: ".$settings['proxy'].PHP_EOL;

	foreach($settings['vectors'] as $typeName) {
		$currentTypeName = $typeName;
		echo PHP_EOL."=======================".PHP_EOL;
		echo PHP_EOL."Type attacks: ".$typeName.PHP_EOL;
		echo "Generate basic vectors".PHP_EOL;
		$payloads = $generator->generateVectors($types->getTypeIDFromName($typeName));
		echo "Generation Finished (".getTime()." s)".PHP_EOL;
		$vectors = array();
		echo "Generate mutations".PHP_EOL;
		foreach($payloads as $scriptID => $payloadsPerScript) {
			foreach($payloadsPerScript as $basicQueryID => $payloadsPerBasicQuery) {
				foreach($payloadsPerBasicQuery as $payload) {
					$vectors[$scriptID][$basicQueryID][] = $mutator->getMutations($payload,$settings[$typeName.'Mutations']);
				}
			}
		}
		echo "Mutations Finished (".getTime()." s)".PHP_EOL;
		echo "Sending".PHP_EOL.PHP_EOL;

		$scripts = $sender->loadScripts($types->getTypeIDFromName($typeName));
		$sender->send($vectors,$scripts,$typeName,$settings['typesSend']);
		
		echo PHP_EOL."Sending Finished (".getTime()." s)".PHP_EOL;
	}
	
	echo PHP_EOL."=======================".PHP_EOL;
	echo PHP_EOL."Program Finished (".getTime()." s)".PHP_EOL;
	$database->close();
?>