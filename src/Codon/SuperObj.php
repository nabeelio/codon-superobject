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
	public function map($map) {
		$map = explode('.', $map);
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

                # We are at the base of what we want
                if(!isset($tree[0])) {
                    return $value;
                }
                
                # Go 
                return $this->findChild($iterator->getChildren(), $tree);
            }
        }
        
        return null;        
    }
    
    
    protected function findTokens($string) {
        
    }
    
    
    protected function replaceTokens(array $tokens = [], $string) {
        
        
    }
}
