#!/usr/bin/php
<?php
ini_set('date.timezone', 'America/New_York');

define('THIS_PATH', dirname(__FILE__));

include THIS_PATH . '/../../codon-profiler/src/Codon/Profiler.php';
include THIS_PATH . '/../src/Codon/SuperObj.php';
include THIS_PATH . '/../src/Codon/Config.php';

$instance = \Codon\Config::loadDataJSON(file_get_contents(THIS_PATH . '/../tests/test.json'));
#$value = $instance->get('testApplication', 'configuration', 'database', 'host');
#var_dump($value);

$value = $instance->get('paths', 'temp_path');
var_dump($value);

$value = \Codon\Config::getPath('testApplication.configuration.database.host');
var_dump($value);