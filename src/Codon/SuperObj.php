<?php
/**
 * Codon PHP 5.4+ Super Object
 *
 * "Super object" with XPath type mapping and self-referencing-substitutions
 * within values. "Light-caches" the maps. Also enjoys long walks on the beach
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

class SuperObj extends \ArrayObject {

	protected $_cache = [];
	protected $_pathDelim = '.';
	protected $_tokenStart = '${';
	protected $_tokenEnd = '}';

	/**
	 * Construct something!
	 * @param string $data
	 */
	public function __construct($data = '') {
		parent::__construct($data, \ArrayObject::STD_PROP_LIST | \ArrayObject::ARRAY_AS_PROPS);
		$this->setIteratorClass('RecursiveArrayIterator');
	}


    /**
	 * Return a string with a given map ("one.two.three")
	 * @param string $map The path to return
	 * @return mixed
	 */
	public function getPath($map) {
		$map = explode($this->_pathDelim, $map);
		return call_user_func_array([$this, 'get'], $map);
	}
    
    
    /**
     * Pass
     */
    public function get() {

		$map = func_get_args();

        # Store the name reversed, it'll be faster to find among like-keys
        $cached_name = strrev(implode('', $map));
        if(isset($this->_cache[$cached_name]))
            return $this->_cache[$cached_name];

		$value = $this->findChild($this->getIterator(), $map);

		# Are there any tokens in this string?
		$tokens = $this->findTokens($value);
		if($tokens !== false) {
			$token_mappings = [];
			foreach($tokens as $t) {
				$token_mappings[$t] = $this->getPath($t);
			}

			$value = $this->replaceTokens($token_mappings, $value);
		}

        $this->_cache[$cached_name] = &$value;

        return $value;
    }


	/**
	 * @param \RecursiveArrayIterator $iterator
	 * @param array $tree
	 * @return null
	 */
	protected function findChild(\RecursiveArrayIterator $iterator, &$tree = []) {
        
        $find_key = array_shift($tree);
        
        foreach($iterator as $key => &$value) {
            if($key === $find_key) {          
				# found what we want
                if(!isset($tree[0])) {
                    return $value;
                }
                # recurse in
                return $this->findChild($iterator->getChildren(), $tree);
            }
        }
        
        return null;        
    }


	/**
	 * Replace an array of tokens in a given string. Pass token
	 * as name only, none of the ${/} that
	 *
	 * @param array $tokens Key=>value pairs of tokens and values
	 * @param string $string String to replace in
	 * @return string
	 */
	protected  function replaceTokens(array $tokens = [], $string) {

		foreach($tokens as $key => $value) {
			$string = str_replace(
				$this->_tokenStart . $key . $this->_tokenEnd,
				$value,
				$string
			);
		}

		return $string;
	}


	/**
	 * Find any tokens that exist inside of a string
	 * @param string $string The string to search on
	 * @return array|bool
	 */
	protected function findTokens($string) {

		$start = strpos($string, $this->_tokenStart);
		if($start === false) {
			return false;
		}

		$matches = [];
		while(1) {
			$end = strpos($string, $this->_tokenEnd, $start);
			$matches[] = substr($string, $start + 2, ($end - $start - 2));
			$start = strpos($string, $this->_tokenStart, $end);

			if($start === false)
				break;
		}

		return $matches;
    }

}
