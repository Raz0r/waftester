<?php
	
	class Detector {
	
		public function __construct() {
			$this->prepareDetector();
		}

		public function summary() {
			global $summary,$currentTypeName,$settings,$types;
			
			$toFile  = "Type attack: $currentTypeName".PHP_EOL;
			$toFile .= "Total attack-queries: ".$summary[$currentTypeName]['total_queries'].PHP_EOL;
			$toFile .= "Basic queries: ".$summary[$currentTypeName]['basic_queries'].PHP_EOL;
			$toFile .= "Total mutations: ".$summary[$currentTypeName]['count_mutations'].PHP_EOL;
			$toFile .= "Total bypass: ".$summary[$currentTypeName]['total_passed'].PHP_EOL;
			
			foreach($summary[$currentTypeName]['per_result'] as $result => $data) {
				$toFile .= PHP_EOL.PHP_EOL."$result".PHP_EOL;
				foreach($data as $type_send => $count) {
					$toFile .= "$type_send: $count".PHP_EOL;
				}
			}
			
			$tmpArray = array();
			foreach($summary['bypassMutationTypeIDs'][$currentTypeName] as $key1 => $value1) {
				foreach($value1 as $key2 => $value2) {
					if(isset($value2['bypass'])) {
						$tmpArray[$key1][$key2] = ($value2['bypass'] / $value2['used'])." (".$value2['bypass']." / ".$value2['used'].")";
					} else {
						$tmpArray[$key1][$key2] = "0 (0 / ".$value2['used'].")";
					}
				}
			}
			foreach($tmpArray as $typeSend => $tmpArray2) {
				if(count($tmpArray2) !== 0) {
					arsort($tmpArray2);
					$toFile .= PHP_EOL."$typeSend mutation type statistics:".PHP_EOL;
					foreach($tmpArray2 as $key => $count) {
						$toFile .= $types->getNameFromSubTypeID($key)." ".$count.PHP_EOL;
					}
				}
			}
			
			$tmpArray = array();
			foreach($summary['bypassMutationIDs'][$currentTypeName] as $key1 => $value1) {
				foreach($value1 as $key2 => $value2) {
					if(isset($value2['bypass'])) {
						$tmpArray[$key1][$key2] = ($value2['bypass'] / $value2['used'])." (".$value2['bypass']." / ".$value2['used'].")";
					} else {
						$tmpArray[$key1][$key2] = "0 (0 / ".$value2['used'].")";
					}
				}
			}
			foreach($tmpArray as $typeSend => $tmpArray2) {
				if(count($tmpArray2) !== 0) {
					arsort($tmpArray2);
					$toFile .= PHP_EOL."$typeSend mutation id statistics:".PHP_EOL;
					foreach($tmpArray2 as $key => $count) {
						$toFile .= $key." ".$count.PHP_EOL;
					}
				}
			}
			
			$tmpArray = array();
			foreach($summary['bypassBasicQueriesIDs'][$currentTypeName] as $key1 => $value1) {
				foreach($value1 as $key2 => $value2) {
					if(isset($value2['bypass'])) {
						$tmpArray[$key1][$key2] = ($value2['bypass'] / $value2['used'])." (".$value2['bypass']." / ".$value2['used'].")";
					} else {
						$tmpArray[$key1][$key2] = "0 (0 / ".$value2['used'].")";
					}
				}
			}
			foreach($tmpArray as $typeSend => $tmpArray2) {
				if(count($tmpArray2) !== 0) {
					arsort($tmpArray2);
					$toFile .= PHP_EOL."$typeSend basic query id statistics:".PHP_EOL;
					foreach($tmpArray2 as $key => $count) {
						$toFile .= $key." ".$count.PHP_EOL;
					}
				}
			}

			$toFile .= PHP_EOL."=========================".PHP_EOL.PHP_EOL;
			file_put_contents(RESULTS_DIR."summary_".$settings['testName'].".txt",$toFile,FILE_APPEND);
		}
				  
		private function prepareDetector() {
			global $settings;
			foreach($settings['dirs'] as $detectorResultDir) {
				@mkdir($detectorResultDir,0777,TRUE);
			}
		}

		public function saveResult($vector,$scriptID,$basicQueryID,$results) {
			global $settings,$summary,$currentTypeName;
		
			@mkdir(RESULTS_DIR.$settings['testName'].DS."script_".$scriptID,0777,TRUE);
			@mkdir(RESULTS_DIR.$settings['testName'].DS."script_".$scriptID.DS."basic_".$basicQueryID,0777,TRUE);
			
			$fullResultToFile = '';
			foreach($results as $key => $file) {
				$unknow = true;
				
				foreach($vector['mutationTypeIDs'] as $tid) {
					if(isset($summary['bypassMutationTypeIDs'][$currentTypeName][$key][$tid]['used']))
						$summary['bypassMutationTypeIDs'][$currentTypeName][$key][$tid]['used']++;
					else
						$summary['bypassMutationTypeIDs'][$currentTypeName][$key][$tid]['used'] = 1;
				}
				
				foreach($vector['mutationIDs'] as $id) {
					if(isset($summary['bypassMutationIDs'][$currentTypeName][$key][$id]['used']))
						$summary['bypassMutationIDs'][$currentTypeName][$key][$id]['used']++;
					else
						$summary['bypassMutationIDs'][$currentTypeName][$key][$id]['used'] = 1;
				}
				
				if(isset($summary['bypassBasicQueriesIDs'][$currentTypeName][$key][$basicQueryID]['used'])) {
					$summary['bypassBasicQueriesIDs'][$currentTypeName][$key][$basicQueryID]['used']++;
				} else {
					$summary['bypassBasicQueriesIDs'][$currentTypeName][$key][$basicQueryID]['used'] = 1;
				}
				
				foreach($settings['keywords'] as $result => $keyword) {
					if(strpos($file,$keyword) !== FALSE) {
						file_put_contents(
							$settings['dirs'][$result].$settings['testName'].".txt",
							"Time: ".date("D M j G:i:s T Y").PHP_EOL.
								"Vector: ".urlencode($vector['mutation']).PHP_EOL.
								"Type: $key".PHP_EOL.
								"File: ".$settings['testName'].DS."script_$scriptID".DS."basic_$basicQueryID".DS.md5($vector['mutation']).'.txt'.PHP_EOL.
								$settings['resultStrings'][$result].PHP_EOL.PHP_EOL,
							FILE_APPEND
						);
						$fullResultToFile = $fullResultToFile . "Time: ".date("D M j G:i:s T Y").PHP_EOL.
							"Vector: ".urlencode($vector['mutation']).PHP_EOL.
							"Type: $key".PHP_EOL.
							"Result: ".$settings['resultStrings'][$result].PHP_EOL.
							"Answer:".PHP_EOL.PHP_EOL.
							$file.PHP_EOL.PHP_EOL."======".PHP_EOL.PHP_EOL;
						if(!isset($summary[$currentTypeName]['per_result'][$result][$key]))
							$summary[$currentTypeName]['per_result'][$result][$key] = 1;
						else 
							$summary[$currentTypeName]['per_result'][$result][$key]++;
						if($result == $settings['bypassResultID']) {
							$summary[$currentTypeName]['total_passed']++;
							foreach($vector['mutationTypeIDs'] as $tid) {
								if(isset($summary['bypassMutationTypeIDs'][$currentTypeName][$key][$tid]['bypass']))
									$summary['bypassMutationTypeIDs'][$currentTypeName][$key][$tid]['bypass']++;
								else
									$summary['bypassMutationTypeIDs'][$currentTypeName][$key][$tid]['bypass'] = 1;
							}
							foreach($vector['mutationIDs'] as $id) {
								if(isset($summary['bypassMutationIDs'][$currentTypeName][$key][$id]['bypass']))
									$summary['bypassMutationIDs'][$currentTypeName][$key][$id]['bypass']++;
								else
									$summary['bypassMutationIDs'][$currentTypeName][$key][$id]['bypass'] = 1;
							}
							if(isset($summary['bypassBasicQueriesIDs'][$currentTypeName][$key][$basicQueryID]['bypass'])) {
								$summary['bypassBasicQueriesIDs'][$currentTypeName][$key][$basicQueryID]['bypass']++;
							} else {
								$summary['bypassBasicQueriesIDs'][$currentTypeName][$key][$basicQueryID]['bypass'] = 1;
							}
						}
						$unknow = false;
						break;
					}	
				}
				if($unknow) {
					file_put_contents(
						$settings['dirs']['unknow'].$settings['testName'].".txt",
						"Time: ".date("D M j G:i:s T Y").PHP_EOL.
							"Vector: ".urlencode($vector['mutation']).PHP_EOL.
							"Type: $key".PHP_EOL.
							"File: ".$settings['testName'].DS."script_$scriptID".DS."basic_$basicQueryID".DS.md5($vector['mutation']).'.txt'.PHP_EOL.
							$settings['resultStrings']['unknow'].PHP_EOL.PHP_EOL,
						FILE_APPEND
					);
					$fullResultToFile = $fullResultToFile . "Time: ".date("D M j G:i:s T Y").PHP_EOL.
						"Vector: ".urlencode($vector['mutation']).PHP_EOL.
						"Type: $key".PHP_EOL.
						"Result: ".$settings['resultStrings']['unknow'].PHP_EOL.
						"Answer:".PHP_EOL.PHP_EOL.
						$file.PHP_EOL.PHP_EOL."======".PHP_EOL.PHP_EOL;
					if(!isset($summary[$currentTypeName]['per_result']['unknow'][$key]))
						$summary[$currentTypeName]['per_result']['unknow'][$key] = 1;
					else 
						$summary[$currentTypeName]['per_result']['unknow'][$key]++;
				}
			}
			
			file_put_contents(
				RESULTS_DIR.$settings['testName'].DS."script_$scriptID".DS."basic_$basicQueryID".DS.md5($vector['mutation']).".txt",
				$fullResultToFile
			);
		}	
	
	}
?>