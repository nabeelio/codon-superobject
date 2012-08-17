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

/**
 * @TODO
 *
 * Parse testApplications.systems
 */
class SuperObj extends \ArrayObject {

	protected $_cache = [];
	protected $_pathDelim = '.';

	protected $_tokenStart = '${';
	protected $_tokenEnd = '}';
	protected $_tokenMappings = [];

	/**
	 * Construct something!
	 * @param string $data
	 */
	public function __construct($data = '') {
		parent::__construct($data, \ArrayObject::STD_PROP_LIST);
		$this->setIteratorClass('RecursiveArrayIterator');
	}


	/**
	 * @param $path
	 * @param $value
	 */
	public function set($path, $value) {

	}


    /**
	 * Return a string with a given map ("one.two.three")
	 * @param string $map The path to return
	 * @return mixed
	 */
	public function getPath($map) {
		return $this->get($map);
	}


    /**
     * Pass in the path
	 * @return mixed
     */
    public function get() {

		$tree = func_get_args();
		$count = func_num_args();
		if($count === 1) {
			if(is_array($tree[0]))  # They passed in an array
				$tree = $tree[0];
			elseif(is_string($tree[0])) # Assume they passed in a path
				$tree = explode($this->_pathDelim, $tree[0]);
		}

        # Store the name reversed, it'll be faster to find among like-keys
		# This is mostly useful for doing a ton of token-replacements which
		# are found as single keys, but also for repeated-access
        $cached_name = strrev(implode('', $tree));
        if(isset($this->_cache[$cached_name]))
            return $this->_cache[$cached_name];

		$value = $this->findValue($this->getIterator(), $tree);

		$this->_cache[$cached_name] = $value;

        return $value;
    }


	/**
	 * Returns an iterator of the current position
	 * @param \RecursiveArrayIterator $iterator
	 * @param array $tree
	 * @return \RecursiveArrayIterator|null
	 */
	protected function findValue(\RecursiveArrayIterator &$iterator, $tree = []) {

        $find_key = array_shift($tree);

        foreach($iterator as $key => &$value) {
            if($key === $find_key) {

				# We've gotten down to the node that we want ($tree is now null)
                if(!isset($tree[0])) {

					# The node we want has children, parse through the leafs
					# and parse any tokens that are present in them
                    if($iterator->hasChildren()) {

						$rec_ii = new \RecursiveIteratorIterator (
							$iterator->getChildren(),
							\RecursiveIteratorIterator::LEAVES_ONLY
						);

						iterator_apply($rec_ii, function(\Iterator &$iterator, &$val){

							$curr_key = $iterator->key();
							$curr_val = $this->parseTokens($iterator->current());

							$iterator->offsetSet($curr_key, $curr_val);

							# This is a bug in PHP or something - if $val is an array of strings, then the
							# offsetSet() doesn't "stick" the new values, so this is a work-around
							if(isset($val[$curr_key])) {
								$val[$curr_key] = $curr_val;
							}

							return true;
						}, [&$rec_ii, &$value]);

					} else {
						$value = $this->parseTokens($value);
					}

					return $value;
                }

                return $this->findValue($iterator->getChildren(), $tree);
            }
        }

        return null;
    }

	/**
	 * Find and replace all tokens in a string
	 * @param mixed $input Pass in the string or array to parse for variables
	 * @return mixed Returns same type that was passed in
	 */
	public function parseTokens($input) {

		$tokens = $this->findTokens($input);
		if($tokens !== false) {
			$tokens_mappings = [];
			foreach($tokens as $t) {

				# If a token hasns't been found, then leave it intact, don't replace it
				$val = $this->get($t);
				if($val !== null) {
					$tokens_mappings[$t] = $val;
				}
			}

			$input = $this->replaceTokens($tokens_mappings, $input);
		}

		return $input;
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


	/**
	 * Replace an array of tokens in a given string. Pass token
	 * as name only, none of the ${/} that
	 *
	 * @param array $tokens Key=>value pairs of tokens and values
	 * @param string $string String to replace in
	 * @return string
	 */
	protected function replaceTokens(array $tokens = [], $string) {

		foreach($tokens as $key => $value) {
			$string = str_replace(
				$this->_tokenStart . $key . $this->_tokenEnd,
				$value,
				$string
			);
		}

		return $string;
	}
}
