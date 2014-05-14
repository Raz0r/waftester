<?php
	class Types {
	
		private $types;
		private $subTypes;
		
		function __construct() {
			$this->loadTypes();
			$this->loadSubTypes();
		}
		
		function loadTypes() {
			global $database;
			$result = $database->query('SELECT id,name FROM types');
			while($row = $result->fetchArray()) {
				$this->types[$row['id']] = $row['name'];
			}
		}
		
		function loadSubTypes() {
			global $database;
			
			$result = $database->query('SELECT name,bin FROM subTypes');
			while($row = $result->fetchArray()) {
				$this->subTypes[$row['bin']] = $row['name'];
			}
		}
		
		function getNameFromSubTypeID($subTypeID) {
			return $this->subTypes[$subTypeID];
		}
		
		function getSubTypeIDFromName($name) {
			foreach($this->subTypes as $id => $subType) {
				if($name == $subType) {
					return $id;
				}
			}
		}
		
		function getNameFromTypeID($typeID) {
			return $this->types[$typeID];
		}
		
		function getTypeIDFromName($name) {
			foreach($this->types as $id => $type) {
				if($name == $type) {
					return $id;
				}
			}
		}
	}
?>