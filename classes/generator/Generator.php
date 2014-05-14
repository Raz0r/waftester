<?php
	class Generator_ {
	
		public function generateVectors($type) {
			global $database,$generatorOut;
			$returnArray = array();
			
			$result = $database->query('SELECT q.id,q.text,q.scriptID,(SELECT s.typeID FROM scripts AS s WHERE q.scriptID = s.id) AS typeID FROM queries AS q WHERE q.active = 1 AND typeID = '.$type);
			while($row = $result->fetchArray()) {
				$generatorOut = array();
				$texts = $this->textGenerator($row['text']);
				if(isset($returnArray[$row['scriptID']][$row['id']]))
					$returnArray[$row['scriptID']][$row['id']] = array_merge($returnArray[$row['scriptID']][$row['id']],$texts);
				else	
					$returnArray[$row['scriptID']][$row['id']] = $texts;
				$texts = null;
			}
			return $returnArray;
		}
		
		private function textGenerator($text)
		{
			global $generatorOut;
			if (preg_match("/^(.*)\{([^\{\}]+)\}(.*)$/isU", $text, $matches)) {
				$p = explode('|', $matches[2]);
				foreach ($p as $comb) {
					$this->textGenerator($matches[1].$comb.$matches[3]);
				}
			} else {
				$generatorOut[] = $text;
				return array_values(array_unique($generatorOut));
			}
			return array_values(array_unique($generatorOut));
		}
	}
?>