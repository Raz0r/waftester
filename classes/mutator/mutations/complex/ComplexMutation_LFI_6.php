<?php

	class ComplexMutation_LFI_6 implements Mutation {
	
		private $name;
		private $type;
		private $id;
		
		public function __construct($type, $name, $id) {
			$this->type = $type;
			$this->name = $name;
			$this->id = $id;
		}
		
		public function doMutation($payload) {
			if(($pos = strpos($payload,'/')) !== FALSE) {
				$payload = preg_replace('/([^\/])\/([^\/])/','${1}///${2}',$payload);
				return $payload;
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