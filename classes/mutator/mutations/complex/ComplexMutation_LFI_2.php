<?php

	class ComplexMutation_LFI_2 implements Mutation {
	
		private $name;
		private $type;
		private $id;
		
		public function __construct($type, $name, $id) {
			$this->type = $type;
			$this->name = $name;
			$this->id = $id;
		}
		
		public function doMutation($payload) {
			if(($pos = strpos($payload,'data:,')) !== FALSE) {
				$base64 = base64_encode(substr($payload,$pos+6));
				$base64 = preg_replace('/=+$/','',$base64);
				return 'data:;base64,'.$base64;
			} else {
				return $payload;
			}
		}
		
		public function getType() {
			return $this->type;
		}
		
		public function getName() {
			return $this->name;
		}
		
		public function getID() {
			return $this->id;
		}
	}
	
?>