<?php

namespace Codon;

/**
 * This is a class that can be used to handle configuration options across a project
 * Has numerous static
 */
class SuperObj {

	protected $_data = null;
	protected $_cache = [];

	/**
	 * Construct something!
	 * @param string $data
	 */
	public function __construct($data = '') {
		
		$this->_data = new \ArrayObject($data,
			\ArrayObject::STD_PROP_LIST | \ArrayObject::ARRAY_AS_PROPS
		);

		$this->_data->setIteratorClass('RecursiveArrayIterator');
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
        if(isset($this->_cache[$cached_name])) {
            return $this->_cache[$cached_name];
        }
                
        $value = $this->findChild($this->_data->getIterator(), $map);
        
        // @TODO: Find any tokens...
        
        $this->_cache[$cached_name] = &$value;
        
        return $value;
    }

    
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
