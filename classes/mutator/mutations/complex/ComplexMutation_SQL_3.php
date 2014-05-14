<?php

	class ComplexMutation_SQL_3 implements Mutation {
	
		private $name;
		private $type;
		private $id;
		
		public function __construct($type, $name, $id) {
			$this->type = $type;
			$this->name = $name;
			$this->id = $id;
		}
		
		public function doMutation($payload) {
			if(preg_match('/^\d[\'"]?[\s]/',$payload)) {
				$payload = preg_replace('/^(\d)([\'"]?)(\s)/','-1${2}${3}',$payload);
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