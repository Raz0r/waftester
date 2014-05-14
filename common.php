<?php
	define('DS',DIRECTORY_SEPARATOR);
	
	define('MAIN_DIR',getcwd().DS);
	define('CLASSES_DIR',MAIN_DIR.'classes'.DS);
	define('LIBS_DIR',MAIN_DIR.'libs'.DS);
	define('RESULTS_DIR',MAIN_DIR.'results'.DS);
	define('DATABASE_DIR',MAIN_DIR.'database'.DS);
	define('COMMON_RESULTS_DIR',RESULTS_DIR.'common'.DS);
	
	define('MUTATOR_DIR',CLASSES_DIR.'mutator'.DS);
	define('GENERATOR_DIR',CLASSES_DIR.'generator'.DS);
	define('DETECTOR_DIR',CLASSES_DIR.'detector'.DS);
	define('SENDER_DIR',CLASSES_DIR.'sender'.DS);
	define('TYPES_DIR',CLASSES_DIR.'types'.DS);
	
	define('MUTATION_DIR',MUTATOR_DIR.'mutations'.DS);
	define('COMPLEX_MUTATION_DIR',MUTATION_DIR.'complex'.DS);
	
	$settings = parse_ini_file(MAIN_DIR.'config.ini');

	require_once(MUTATOR_DIR.'Mutator.php');
	require_once(MUTATION_DIR.'Mutation.php');
	require_once(MUTATION_DIR.'SimpleMutation.php');
	require_once(GENERATOR_DIR.'Generator.php');
	require_once(SENDER_DIR.'Sender.php');
	require_once(DETECTOR_DIR.'Detector.php');
	require_once(TYPES_DIR.'Types.php');
	
	require_once(LIBS_DIR.'http_request'.DS.'http_with_file.php');
	require_once(LIBS_DIR.'combination'.DS.'Combination.php');
	$database = new SQLite3(DATABASE_DIR.'waf.sqlite');
	
	date_default_timezone_set("Europe/Moscow");
	
	$types = new Types();
	$generator = new Generator_();
	$mutator = new Mutator();
	$sender = new Sender();
	$detector = new Detector();
	
	$startTime = microtime(TRUE);
	
	foreach($settings['vectors'] as $typeName) {
		$summary[$typeName] = array(
			'total_queries' => 0,
			'total_passed' => 0,
			'basic_queries' => 0,
			'count_mutations' => 0,
			'per_result' => array()
		);
	}
	
	$currentTypeName = '';
	
	function getTime() {
		global $startTime;
		return ceil(microtime(TRUE) - $startTime);
	}
?>