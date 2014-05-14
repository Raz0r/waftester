<?php
	class Mutator {
	
		public function getMutations($payload,$typeNames) {
			global $summary,$currentTypeName;
			$returnArray = array();
			$mutations = $this->loadMutations($typeNames);
			$summary[$currentTypeName]['count_mutations'] = count($mutations);
			$returnArray = $this->generateCombination($payload,$mutations);
			return $returnArray;
		}
		
		private function loadMutations($typeNames) {
			$simpleMutations = $this->loadSimpleMutations($typeNames);
			$complexMutations = $this->loadComplexMutations($typeNames);
			return array_merge($simpleMutations,$complexMutations);
		}
		
		private function loadSimpleMutations($typeNames) {
			global $database;
			
			$returnArray = array();
			
			foreach($typeNames as $typeName) {
				$result = $database->query("
					SELECT 
						sm.id,
						sm.`name`,
						sm.`from`,
						sm.`to`,
						(SELECT st.bin FROM subTypes AS st WHERE st.id = sm.subTypeID) AS bin, 
						(SELECT st.name FROM subTypes AS st WHERE st.id = sm.subTypeID) AS stName
					FROM simpleMutations AS sm 
					WHERE stName = '$typeName' AND sm.active = 1
				");
				while($row = $result->fetchArray()) {
					$returnArray[] = new SimpleMutation($row['bin'],$row['name'],$row['from'],$row['to'],$row['id']);
				}
			}
						
			return $returnArray;
		}
		
		private function loadComplexMutations($typeNames) {
			global $database;
			$returnArray = array();
			
			foreach($typeNames as $typeName) {
				$result = $database->query("
					SELECT 
						cm.id,
						cm.name,
						cm.filename,
						(SELECT st.bin FROM subTypes AS st WHERE st.id = cm.subTypeID) AS bin,
						(SELECT st.name FROM subTypes AS st WHERE st.id = cm.subTypeID) AS stName
					FROM complexMutations AS cm 
					WHERE stName = '$typeName' AND cm.active = 1
				");
				while($row = $result->fetchArray()) {
					require_once(COMPLEX_MUTATION_DIR.$row['filename']);
					$className = basename($row['filename'], ".php");
					$returnArray[] = new $className($row['bin'],$row['name'],$row['id']);
				}
			}
						
			return $returnArray;
		}

		function generateCombination($payload,$mutationObjects) {
			$returnArray = array(0 => array('mutation' => $payload, 'mutationTypeIDs' => array(), 'mutationIDs' => array()/*, 'mutationNames' => ''*/));
			$onlyMutations = array($payload);
			$types = array();
			$mutations = array();
			for($i = 0; $i < count($mutationObjects); $i++) {
				$type = $mutationObjects[$i]->getType();
				if(!in_array($type,$types))
					$types[] = $type;
				if(!isset($mutations[$type])) {
					$mutations[$type]['current'] = 0;
					$mutations[$type]['indexes'][] = $i;
					$mutations[$type]['active'] = FALSE;
					$mutations[$type]['max'] = 1;
				} else {
					$mutations[$type]['indexes'][] = $i;
					$mutations[$type]['max']++;
				}
			}
			$combinations = Combination::all($types);
			foreach($combinations as $combination) {
				$tmpMutations = $mutations;
				foreach($combination as $type) {
					$tmpMutations[$type]['active'] = true;
				}
				do {
					$newMutation = $payload;
					$appliedMutationTypes = array();
					$appliedMutationIDs = array();
					//$appliedMutationString = '';
					foreach($combination as $sm) {
						$currentMutation = $mutationObjects[$tmpMutations[$sm]['indexes'][$tmpMutations[$sm]['current']]];
						$prevMutation = $newMutation;
						$newMutation = $currentMutation->doMutation($newMutation);
						if($newMutation == $prevMutation) {
							$tmpMutations[$sm]['active'] = false;
							continue;
						}
						$appliedMutationTypes[] = $currentMutation->getType();
						$appliedMutationIDs[] = $currentMutation->getName();
						//$appliedMutationString = $appliedMutationString.$currentMutation->getName().', ';
					}
					if(!in_array($newMutation, $onlyMutations)) {
						$onlyMutations[] = $newMutation;
						$returnArray[] = array('mutation' => $newMutation, 'mutationTypeIDs' => $appliedMutationTypes, 'mutationIDs' => $appliedMutationIDs/*, 'mutationNames' => $appliedMutationString*/);
					}
					reset($tmpMutations);
				} while($this->incCurrent($tmpMutations,each($tmpMutations)));
			}
			return $returnArray;
		}
		
		function incCurrent(&$mut,$current) {
			if($current['value']['active']) {
				$current['value']['current']++;
				if($current['value']['current'] == $current['value']['max']) {
					$next = each($mut);
					if(!$next)
						return FALSE;
					$current['value']['current'] = 0;
					$mut[$current['key']] = $current['value'];
					if(!$this->incCurrent($mut,$next))
						return FALSE;
				}
				$mut[$current['key']] = $current['value'];
			} else {
				$next = each($mut);
				if(!$next) {
					return FALSE;
				}
				if(!$this->incCurrent($mut,$next))
					return FALSE;
			}
			return TRUE;	
		}
		
		
	}
?>