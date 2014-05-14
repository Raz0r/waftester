<?php

	class SimpleMutation implements Mutation {
	
		private $from;
		private $to;
		private $name;
		private $type;
		private $id;
		
		public function __construct($type, $name, $from, $to, $id) {
			$this->type = $type;
			$this->name = $name;
			$this->from = urldecode($from);
			$this->to = urldecode($to);
			$this->id = $id;
		}
		
		public function doMutation($payload) {
			if(substr($this->from,0,1) !== '/')
				$this->from = '/'.preg_quote($this->from,'/').'/';
			$payload = preg_replace($this->from,$this->to,$payload);
			return $payload;
		}
		
		public function getType() {
			return $this->type;
		}
		
		public function getName() {
			return $this->name;
		}
		
		public function getFrom() {
			return $this->from;
		}
		
		public function getTo() {
			return $this->to;
		}
		
		public function getID() {
			return $this->id;
		}
		
	}
	
?>