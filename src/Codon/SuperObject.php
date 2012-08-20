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

class SuperObject extends \ArrayObject {

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
		parent::__construct($data, \ArrayObject::STD_PROP_LIST | \ArrayObject::ARRAY_AS_PROPS);
		$this->setIteratorClass('RecursiveArrayIterator');
	}


	/**
	 * Save values/set them to an arbitrary $path
	 * @param string $path Path to save (x.y.z)
	 * @param mixed $value Value for the above path
	 */
	public function set($path, $value) {

		$path_list = explode('.', $path);

		# See if this is a node already exists
		list($node, $node_value) = $this->findNode($this->getIterator(), $path_list);
		if($node !== null) {
			$node->offsetSet($node->key(), $value);
			$this->saveCache($path, $value);
			return;
		}

		# See if the first index exists; if not, create it and assign it
		# Then set a parent tree to that index. Then just crawl up the nodes
		# Creating each level
		$p = array_shift($path_list);
		if(!isset($this[$p])) {
			$this[$p] = [];
		}

		$parent_key = &$this[$p];
		foreach($path_list as $p) {
			# create new node, if it doesn't exist
			if(!isset($parent_key[$p])) {
				$parent_key[$p] = [];
			}
			$parent_key = &$parent_key[$p];
		}

		# Finally set the value because we're at the end
		$parent_key = $value;
		$this->saveCache($path, $value);
	}


    /**
	 * Return a string with a given map ("one.two.three")
	 * @param string $map The path to return
	 * @return mixed
	 */
	public function getByPath($map) {
		return $this->get($map);
	}


    /**
     * Pass in the path, or a list with the path of the key you want
	 * Returns the value from that path
	 * @return mixed
     */
    public function get() {

		$tree = func_get_args();
		$count = func_num_args();
		if($count === 1) {
			if(is_array($tree[0])) { # They passed in an array, so assume this is our list
				$tree = $tree[0];
			} elseif(is_string($tree[0])) { # Passed in a string, assume this is a path
				# explode based on the type of delimiter (custom, . or /)
				if(strpos($tree[0], $this->_pathDelim) !== false) {
					$tree = explode($this->_pathDelim, $tree[0]);
				} elseif(strpos($tree[0], '.') !== false) {
					$tree = explode('.', $tree[0]);
				} elseif(strpos($tree[0], '/') !== false) {
					$tree = explode('/', $tree[0]);
				}
			}
		}

		$tree_key = implode('.', $tree);

		# Check to see if we have a copy of this key cached already
        $value = $this->getCached($tree_key);
		if($value !== null)
			return $value;

		$value = $this->findValue($this->getIterator(), $tree);

		# Save it to the cache
		$this->saveCache($tree_key, $value);

        return $value;
    }


	/**
	 * Return a cached copy of a given key string. Stores the key name
	 * as a reversed string, for a faster lookup time (esp for nested-sets)
	 * @param string $key_name
	 * @return mixed
	 */
	public function getCached($key_name) {

		$key_name = strrev($key_name);

		if(isset($this->_cache[$key_name])) {
			return $this->_cache[$key_name];
		}

		return null;
	}


	/**
	 * Save a copy of the key to the cache
	 * @param $key_name
	 * @param $value
	 */
	public function saveCache($key_name, $value) {
		$key_name = strrev($key_name);
		$this->_cache[$key_name] = $value;
	}


	/**
	 * Find and return the bottom node in the given tree
	 * @param \RecursiveArrayIterator $iterator
	 * @param array $tree
	 * @return mixed Array with 0: iterator, 1: value (reference)
	 */
	protected function findNode(\RecursiveArrayIterator $iterator, $tree = []) {

		$find_key = array_shift($tree);

		foreach($iterator as $key => &$value) {

			if($key !== $find_key) { continue; }

			# $tree isn't null yet, meaning we still have to travel down
			# nodes in order to get to the last one... inception
			if(isset($tree[0])) {
				return $this->findNode($iterator->getChildren(), $tree);
			}

			# Return a reference to this current node - it's needed later. More details
			# are in the findValue() function. Yeah, it's kinda hackey
			return [$iterator, &$value];
		}

		return null;
	}


	/**
	 * Returns an iterator of the current position
	 * @param \RecursiveArrayIterator $node
	 * @param array $tree
	 * @return mixed|null
	 */
	protected function findValue(\RecursiveArrayIterator $node, $tree = []) {

		# Find the node we are looking for, and return the iterator and value
		list($node, $node_value) = $this->findNode($node, $tree);

		if($node_value === null) { return null; }

		# This node has no children, it's just a string value, replace and return
		if($node->hasChildren() === false) {
			$node->offsetSet($node->key(), $this->parseTokens($node->current()));
			return $node->current();
		}

		# The node we want has children, parse through the
		# leafs and parse any tokens that are present in them
		$rec_ii = new \RecursiveIteratorIterator (
			$node->getChildren(),
			\RecursiveIteratorIterator::LEAVES_ONLY
		);

		iterator_apply($rec_ii, function(\Iterator &$iter, &$node_value){

			$curr_key = $iter->key();
			$curr_val = $this->parseTokens($iter->current());

			$iter->offsetSet($curr_key, $curr_val);

			# This is a bug in PHP or something - if $nodeValue is an array, then the
			# offsetSet() doesn't "stick" the new values, since they're not passed
			# into the iterator by reference. So this is a work-around
			if(is_array($node_value) && isset($node_value[$curr_key])) {
				$node_value[$curr_key] = $curr_val;
			}

			return true;

		}, [&$rec_ii, &$node_value]);

		return $node_value;
    }


	/**
	 * Find and replace all tokens in a string
	 * @param mixed $input Pass in the string or array to parse for variables
	 * @return mixed Returns same type that was passed in
	 */
	public function parseTokens($input) {

		$tokens = $this->findTokens($input);
		if($tokens === false) { return $input; }

		# Blank, to only replace tokens that are
		foreach($tokens as $t) {

			# Look for it in the mapping list/cache, if not found, then retrieve it
			if(isset($this->_tokenMappings[$t])) { continue; }

			$token_val = $this->getCached($t);
			if($token_val === null) {
				$token_val = $this->findValue($this->getIterator(), explode('.', $t));
				$this->saveCache($t, $token_val);
			}

			# If we found a value for it, then add it to the replacement table
			if($token_val !== null) {
				$this->_tokenMappings[$t] = $token_val;
			}
		}

		return $this->replaceTokens($this->_tokenMappings, $input);
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

			if($start === false) { break; }
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
