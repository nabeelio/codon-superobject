<?php
/**
 * Codon PHP 5.4+ Super Object Wrapper Example
 *
 * This configuration class wraps the \Codon\SuperObj class into a generic
 * container for configuration values. This also allows the SuperObj functions
 * to be accessed statically.
 *
 * @author      Nabeel Shahzad <nshahzad@gmail.com>
 * @copyright   2012 Nabeel Shahzad
 * @link		http://nabeelio.com
 * @link        https://github.com/nshahzad/codon-profiler
 * @license     MIT
 * @package     Codon
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Codon;

class Config {

	protected static $_instance = null;

	private function __construct() { }

	/**
	 * Load data from a JSON string. Return an instance to self
	 * @static
	 * @param $json_string
	 * @return SuperObj
	 */
	public static function loadDataJSON($json_string) {
		return self::getInstance(json_decode($json_string));
	}

	/**
	 * @static
	 * @param $data
	 * @return SuperObj
	 */
	public static function getInstance($data) {

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
