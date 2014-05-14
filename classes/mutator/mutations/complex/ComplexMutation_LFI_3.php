<?php

	class ComplexMutation_LFI_3 implements Mutation {
	
		private $name;
		private $type;
		private $id;
		private $blacklist = array('compress.zlib','zlib','data');
		
		public function __construct($type, $name, $id) {
			$this->type = $type;
			$this->name = $name;
			$this->id = $id;
		}
		
		public function doMutation($payload) {
			if(($pos = strpos($payload,':')) !== FALSE) {
				$wrapper = substr($payload,0,$pos);
				if(in_array($wrapper,$this->blacklist)) {
					return $payload;
				}
				$newWrapper = '';
				for($i = 0; $i < strlen($wrapper); $i++) {
					if(($i % 2) == 0) {
						$newWrapper = $newWrapper.strtoupper($wrapper[$i]);
					} else {
						$newWrapper = $newWrapper.strtolower($wrapper[$i]);
					}
				}
				$payload = str_replace($wrapper.':',$newWrapper.':',$payload);
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