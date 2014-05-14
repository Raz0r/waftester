<?php

	class Sender {

		public function loadScripts($type) {
			global $database;
			$returnArray = array();
			
			$result = $database->query('SELECT id,text FROM scripts WHERE typeID = '.$type);
			while($row = $result->fetchArray()) {
				$returnArray[$row['id']] =  $row['text'];
			}
			
			return $returnArray;
		}

		public function send($vectors, $scripts, $type, $typeSends) {
			global $summary,$currentTypeName,$detector;
			$totalCount = 0;
			$this->countVectors($vectors,$totalCount);
			$totalCount = $totalCount * count($typeSends);
			$counter = 0;
			$counterBasicQuery = 0;
			echo "Total count attack-requests: $totalCount".PHP_EOL;
			$summary[$currentTypeName]['total_queries'] = $totalCount;
			foreach($vectors as $scriptID => $vectorsPerScript) {
				echo "Script ".$scripts[$scriptID].PHP_EOL;
				$parsedScript = parse_url($scripts[$scriptID]);
				parse_str($parsedScript['query'],$parsedQuery);
				foreach($parsedQuery as $key => $val) {
					if($val == '${p}') {
						$payloadID = $key;
						break;
					}
				}
				unset($parsedQuery[$payloadID]);

				$host = $parsedScript['host'];
				$path = $parsedScript['path'];
				if(isset($parsedScript['port'])) {
					$port = $parsedScript['port'];
				} else {
					if((isset($parsedScript['scheme'])) && ($parsedScript['scheme'] == 'https')) {
						$port = 443;
					} else {
						$port = 80;
					}
				}
				
				foreach($vectorsPerScript as $basicQueryID => $vectorsPerBasicQuery) {
					$summary[$currentTypeName]['basic_queries'] += count($vectorsPerBasicQuery);
					
					$bvector = urlencode($vectorsPerBasicQuery[0][0]['mutation']);
					if(strlen($bvector) > 50)
						$bvector = substr($bvector,0,50).'...';
					echo "Basic query $basicQueryID started: ".$bvector.PHP_EOL;
					
					foreach($vectorsPerBasicQuery as $vectorsPerBasicQueryMutations) {
			
						foreach($vectorsPerBasicQueryMutations as $vector) {
							$this->sendVector($vector,$scriptID,$basicQueryID,$host,$port,$path,$parsedQuery,$payloadID,$typeSends);
							$counter += count($typeSends);
						}
					
					}
					
					echo "$counter request sended (".getTime()." s)".PHP_EOL;
				}
			}
			$detector->summary();
		}
		
		private function sendVector($vector,$scriptID,$basicQueryID,$host,$port,$path,$get,$payloadID,$typeSends) {
			global $detector,$settings;
		
			$v = $vector['mutation'];
			if(isset($settings['addHeaders']) and count($settings['addHeaders']) !== 0)
				$header = $settings['addHeaders'];
			else
				$header = array();
			$results = array();
			$post[$payloadID] = urlencode($v);
			$postIndex[$payloadID.'['.$v.']'] = 1;
			if(!isset($settings['proxy']))
				$settings['proxy'] = $host;
			foreach($typeSends as $typeSend) {
				switch($typeSend) {
					case "GET":
						$get[$payloadID] = urlencode($v);
						$results[$typeSend] = 			http_request('GET',$settings['proxy'],$host,$port,$path,$get,array(),array(),array(),$header);
						unset($get[$payloadID]);
						break;
					case "GET_INDEX":
						if(strpos($v,"\0") === FALSE) {
							$get[$payloadID.'['.$v.']'] = '1';
							$results[$typeSend] =		http_request('GET',$settings['proxy'],$host,$port,$path,$get,array(),array(),array(),$header);
							unset($get[$payloadID.'['.$v.']']);
						}
						break;
					case "POST":
						$results[$typeSend] =			http_request('POST',$settings['proxy'],$host,$port,$path,$get,$post,array(),array(),$header);
						break;
					case "POST_INDEX":
						if(strpos($v,"\0") === FALSE) {
							$results[$typeSend] =		http_request('POST',$settings['proxy'],$host,$port,$path,$get,$postIndex,array(),array(),$header);
						}
						break;
					case "COOKIE":
						$results[$typeSend] = 			http_request('GET',$settings['proxy'],$host,$port,$path,$get,array(),$post,array(),$header);
						break;
					case "COOKIE_INDEX":
						if(strpos($v,"\0") === FALSE) {
							$results[$typeSend] = 		http_request('GET',$settings['proxy'],$host,$port,$path,$get,array(),$postIndex,array(),$header);
						}
						break;
					case "HEADER":
						$header[$payloadID] = $v;
						$results[$typeSend] = 			http_request('GET',$settings['proxy'],$host,$port,$path,$get,array(),array(),array(),$header);
						unset($header[$payloadID]);
						break;
				}
			}
			
			$detector->saveResult($vector,$scriptID,$basicQueryID,$results);
		}
		
		private function countVectors($vectors,&$count) {
			if(!is_array($vectors) || isset($vectors['mutation'])) {
				$count++;
			} else {
				foreach($vectors as $v) {
					$this->countVectors($v,$count);
				}
			}
		}
	
	}

?>