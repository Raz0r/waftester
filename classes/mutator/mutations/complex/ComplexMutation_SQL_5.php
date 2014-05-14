<?php

	class ComplexMutation_SQL_5 implements Mutation {
	
		private $name;
		private $type;
		private $id;
		
		public function __construct($type, $name, $id) {
			$this->type = $type;
			$this->name = $name;
			$this->id = $id;
		}
		
		private function strToHex($string) {
			$hex='';
			for ($i=0; $i < strlen($string); $i++)
			{
				$hex .= dechex(ord($string[$i]));
			}
			return $hex;
		}
		
		public function doMutation($payload) {
			if(preg_match('/[\'"].*?[\'"]/is',$payload)) {
				$payload = preg_replace('/[\'"](.*?)[\'"]/ise','\'X\\\'\'.\$this->strToHex(\'$1\').\'\\\'\'',$payload);
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