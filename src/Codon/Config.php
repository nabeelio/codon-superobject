<?php

namespace Codon;

/**
 * This configuration class wraps the \Codon\SuperObj class into a generic
 * container for configuration values. This also allows the SuperObj functions
 * to be accessed statically as:
 *
 * \Codon\Config::get('mapped.array.path.here');
 * \Codon\Config::set('maped.array.path', 'value');
 */

class Config {

	protected static $_instance = null;

	private function __construct() { }

	public static function loadDataJSON($json_string) {
		self::init(json_decode($json_string));
	}

	/**
	 * @static
	 * @param $data
	 * @return SuperObj
	 */
	public static function init($data) {

		if(self::$_instance === null) {
			self::$_instance = new \Codon\SuperObj($data);
		}

		return self::$_instance;
	}


	/**
	 * Allow this class to be used statically - SuperObj::get(), set(), etc
	 * @static
	 * @param $name
	 * @param $args
	 * @return mixed
	 * @throws \BadMethodCallException
	 */
	public static function __callStatic($name, $args) {

		if(is_callable([self::$_instance, $name])) {
			return call_user_func_array([self::$_instance, $name], $args);
		}

		throw new \BadMethodCallException('Static call to method "' . __CLASS__ . '::' . $name . '" does not exist');
	}

}
