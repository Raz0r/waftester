<?php

	class ComplexMutation_LFI_1 implements Mutation {
	
		private $name;
		private $type;
		private $id;
		
		public function __construct($type, $name, $id) {
			$this->type = $type;
			$this->name = $name;
			$this->id = $id;
		}
		
		public function doMutation($payload) {
			if(($pos = strpos($payload,'data:')) !== FALSE) {
				$base64 = base64_encode(substr($payload,strpos($payload,',')+1));
				return preg_replace('/(data:)(.*?)(,)(.*)/','${1}${2};base64${3}'.$base64,$payload);
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