<?php

namespace Codon;

/**
 * This is a class that can be used to handle configuration options across a project
 * Has numerous static
 */
class Config {

	protected $_config = null;
	protected $_cache = [];

	private static $_instance = null;

	/**
	 * Singleton...
	 * @param string $data
	 */
	private function __construct($data = '') {
		$this->_config = new \ArrayObject($data,
			\ArrayObject::STD_PROP_LIST | \ArrayObject::ARRAY_AS_PROPS
		);

		$this->_config->setIteratorClass('RecursiveArrayIterator');
	}


	public function __call($name, $args) {
	   
        if(method_exists($this, $name)) {
            return call_user_func_array([$this, $name], $args);
        }
        
        throw new \BadMethodCallException('Method "' . __CLASS__ . '::' . $name . '" does not exist');
	}


	/**
	 * So we can use this class as \Codon\Config::get
	 * @static
	 * @param $name
	 * @param $args
	 * @return mixed
	 */
	public static function __callStatic($name, $args) {
	   
        if(is_callable([self::$_instance, $name])) {
            return call_user_func_array([self::$_instance, $name], $args);
        }
        
        throw new \BadMethodCallException('Static call to method "' . __CLASS__ . '::' . $name . '" does not exist');
	}
    
 
    /**
     * Initialize a singleton instance
     * @param mixed $data 
     * @see getInstance
     */
	public static function getInstance($data = '') {
		if(self::$_instance === null) {
			self::$_instance = new Config($data);
		}

		return self::$_instance;
	}


    /**
     * Initialize a singleton instance
     * @param mixed $data 
     * @see getInstance
     */
	public static function i($data = '') {
		return self::getInstance($data);
	}
    

    /**
     * Initialize a singleton instance
     * @param mixed $data 
     * @see getInstance
     */
	public static function init($data = '') {
		return self::getInstance($data);
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
    protected function get() {

		$map = func_get_args();
        $depth = func_num_args();
        
        # Store the name reversed, it'll be faster to find among like-keys
        $cached_name = strrev(implode('', $map));
        if(isset($this->_cache[$cached_name])) {
            return $this->_cache[$cached_name];
        }
                
        $value = $this->findChild($this->_config->getIterator(), $map);
        
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
    
    
    public function replaceTokens(array $tokens = [], $string) {
        
        
    }
}
