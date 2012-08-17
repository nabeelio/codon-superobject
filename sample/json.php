#!/usr/bin/php
<?php
ini_set('date.timezone', 'America/New_York');

define('THIS_PATH', dirname(__FILE__));

include THIS_PATH . '/../../codon-profiler/src/Codon/Profiler.php';
include THIS_PATH . '/../src/Codon/SuperObj.php';
include THIS_PATH . '/../src/Codon/Config.php';

$instance = \Codon\Config::loadDataJSON(file_get_contents(THIS_PATH . '/../tests/test.json'));

echo "==== set values=======\n";
echo "=========================\n";

# Set new
echo 'BEFORE SET: '; var_dump($instance->get('test.value'));
$instance->set('test.value', 'hello');
echo 'AFTER SET: '; var_dump($instance->get('test.value'));

$instance->set('test.array', ['oranges', 'bananas']);
echo 'SET ARRAY: '; var_dump($instance->get('test.array'));

echo 'GET TEST NODE: '; var_dump($instance->get('test'));

#$instance->set('test.Application.cdn', array_push($cdn, $cdn));

echo "==== array values =======\n";
echo "=========================\n";

$value = $instance->get(['testApplication', 'cdn']);
echo 'RETURNED VALUES: '; var_dump($value);
