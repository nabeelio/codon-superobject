<?php

define('THIS_PATH', dirname(__FILE__));
include THIS_PATH . '/../../src/Codon/SuperObj.php';

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('date.timezone', 'America/New_York');

/**
 * PHPUnit tests for SuperObj
 * @outputBuffering false
 */
class SuperObjTest  extends PHPUnit_Framework_TestCase {

	public function init() {
		return new \Codon\SuperObj(json_decode(file_get_contents(
			THIS_PATH . '/../test.json'
		)));
	}

	/**
	 * Return just a string
	 */
	public function testGetString() {
		$obj = $this->init();
		$this->assertEquals(
			$obj->get('global', 'env'),
			'dev'
		);
	}

	/**
	 * Return an object
	 */
	public function testGetObj() {
		$obj = $this->init();
		$this->assertEquals(
			$obj->get('php'),
			(object) [
				'ini_set' => (object) [
					'display_errors' => 'On',
					"date.timezone" => "America/New_York"
				]
			]
		);
	}

	/**
	 * Get a string by path
	 */
	public function testGetByPath() {
		$obj = $this->init();
		$this->assertEquals(
			$obj->get('testApplication.configuration.database.host'),
			'localhost'
		);
	}

	/**
	 * Return an array by path
	 */
	public function testGetArrayByPath() {
		$obj = $this->init();
		$this->assertEquals(
			$obj->get('testApplication.configuration.database'),
			(object) [
				"host" => "localhost",
				"user" => "aUsername",
				"pass" => "aPassword",
				"name" => "someDatabase"
			]
		);
	}

	/**
	 * Get a string that has a token
	 */
	public function testStringTokens() {
		$obj = $this->init();
		$this->assertEquals(
			$obj->get('testApplication.id'),
			'testapp-dev'
		);
	}

	/**
	 * Get an object with tokens
	 */
	public function testObjectTokens() {
		$obj = $this->init();
		$this->assertEquals(
			$obj->get('paths'),
			(object) [
				"root_path" => "/var/www/webapp",
				"upload_path" => "/var/www/webapp/upload",
				"temp_path" => "/var/www/webapp/temp"
			]
		);
	}

	/**
	 * Get an array with tokens
	 */
	public function testNestedArrayTokens() {
		$obj = $this->init();
		$this->assertEquals(
			$obj->get('testApplication.cdn'),
			[
				"cdn-001.testapp-dev-cdn.cdn.com",
				"cdn-002.testapp-dev-cdn.cdn.com"
			]
		);
	}

	/**
	 * Get an array with nested objects with tokens
	 */
	public function testNestedArrayObjectsTokens() {
		$obj = $this->init();
		$this->assertEquals(
			$obj->get('testApplication.systems'),
			[
				(object) [
					"id" => '${testApplication.cdn_Id}',
					"env" => "dev",
					"type" =>  "server"
				],
				(object) [
					"id" => "testapp-dev-cdn",
					"env" => "dev",
					"type" =>  "server"
				]
			]
		);
	}

	/**
	 * A basic set
	 */
	public function testSetString() {
		$obj = $this->init();
		$obj->set('test.value', 'hello');

		$this->assertEquals(
			$obj->get('test.value'),
			'hello'
		);

		return $obj;
	}

	/**
	 * @depends testSetString
	 */
	public function testAppendToExisting($obj) {

		$obj->set('test.array', ['oranges', 'bananas']);
		$this->assertEquals(
			$obj->get('test'),
			[
				'value' => 'hello',
				'array' => [
					'oranges',
					'bananas'
				]
			]
		);

		return $obj;
	}

	/**
	 * See we didn't trample over any existing values
	 * @depends testAppendToExisting
	 */
	public function testCheckExistingNodes($obj) {
		$this->assertEquals(
			$obj->get('testApplication.id'),
			'testapp-dev'
		);
	}
}
