<?php

define('THIS_PATH', dirname(__FILE__));
include THIS_PATH . '/../../src/Codon/SuperObj.php';

/**
 * PHPUnit tests for SuperObj
 */
class SuperObjTest  extends PHPUnit_Framework_TestCase {

	public function testInit() {
		return new \Codon\SuperObj(json_decode(file_get_contents(
			THIS_PATH . '/../test.json'
		)));
	}

	/**
	 * @depends testInit
	 */
	public function testGet($obj) {

		$this->assertEquals(
			$obj->get('global', 'env'),
			'dev'
		);

		$testObj = (object) [
			'ini_set' => [
				'display_errors' => 'On'
			]
		];

		$this->assertEquals(
			$obj->get('php'),
			$testObj
		);
	}

	/**
	 * @depends testInit
	 */
	public function testPath($obj) {
		$this->assertEquals(
			$obj->getPath('testApplication.configuration.database.host'),
			'localhost'
		);
	}

}
