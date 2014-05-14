<?php

	class ComplexMutation_COMMON_1 implements Mutation {
	
		private $name;
		private $type;
		private $id;
		
		public function __construct($type, $name, $id) {
			$this->type = $type;
			$this->name = $name;
			$this->id = $id;
		}
		
		public function doMutation($payload) {
			for($i = 0; $i < strlen($payload); $i++) {
				$payload[$i] = (($i % 2) == 0) ? strtoupper($payload[$i]) : strtolower($payload[$i]);
			}
			return $payload;
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