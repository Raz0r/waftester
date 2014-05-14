<?php 
	interface Mutation {
		
		public function getType();
		public function getName();
		public function getID();
		public function doMutation($payload);
		
	}
?>