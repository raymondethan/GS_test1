<?php
/**
 * @version Plain.php 07.04.2009 13:19:18
 * @author Tulika Evheniy ytu eugene.tulika@voltimum.com
 */

/** Zend_Controller_Router_Route_Abstract */
require_once 'Zend/Controller/Router/Route/Abstract.php';

class App_Controller_Router_Route_Plain extends Zend_Controller_Router_Route_Abstract
{
	protected $_urlVariable = ':';
    protected $_urlDelimiter = '&';
    protected $_regexDelimiter = '#';
    protected $_defaultRegex = null;

    /**
     * Holds names of all route's pattern variable names. Array index holds a position in URL.
     * @var array
     */
    protected $_variables = array();

    /**
     * Holds Route patterns for all URL parts. In case of a variable it stores it's regex
     * requirement or null. In case of a static part, it holds only it's direct value.
     * In case of a wildcard, it stores an asterisk (*)
     * @var array
     */
    protected $_parts = array();

    /**
     * Holds user submitted default values for route's variables. Name and value pairs.
     * @var array
     */
    protected $_defaults = array();

    /**
     * Holds user submitted regular expression patterns for route's variables' values.
     * Name and value pairs.
     * @var array
     */
    protected $_requirements = array();

    /**
     * Associative array filled on match() that holds matched path values
     * for given variable names.
     * @var array
     */
    protected $_values = array();

    /**
     * Associative array filled on match() that holds wildcard variable
     * names and values.
     * @var array
     */
    protected $_wildcardData = array();

    /**
     * Helper var that holds a count of route pattern's static parts
     * for validation
     * @var int
     */
    protected $_staticCount = 0;

    public function getVersion() {
        return 1;
    }

    /**
     * Instantiates route based on passed Zend_Config structure
     *
     * @param Zend_Config $config Configuration object
     */
    public static function getInstance(Zend_Config $config)
    {
        $reqs = ($config->reqs instanceof Zend_Config) ? $config->reqs->toArray() : array();
        $defs = ($config->defaults instanceof Zend_Config) ? $config->defaults->toArray() : array();
        return new self($config->route, $defs, $reqs);
    }

    /**
     * Prepares the route for mapping by splitting (exploding) it
     * to a corresponding atomic parts. These parts are assigned
     * a position which is later used for matching and preparing values.
     *
     * @param string $route Map used to match with later submitted URL path
     * @param array $defaults Defaults for map variables with keys as variable names
     * @param array $reqs Regular expression requirements for variables (keys as variable names)
     */
    public function __construct($route, $defaults = array(), $reqs = array())
    {

        $route = trim($route, $this->_urlDelimiter);
        if(strpos($route, '?')!==false)
        	$route = substr($route, strpos($route, '?')+1);
        $this->_defaults = (array) $defaults;
        $this->_requirements = (array) $reqs;
        if ($route != '') {

            foreach (explode($this->_urlDelimiter, $route) as $pos => $part) {
				//var_dump($this->_urlVariable);

                $part = explode('=', $part);
                if(isset($part[1])) {
                	if (substr($part[1], 0, 1) == $this->_urlVariable) {
                    	$name = substr($part[1], 1);
                    	$this->_parts[$part[0]] = (isset($reqs[$name]) ? $reqs[$name] : $this->_defaultRegex);
                    	$this->_variables[$part[0]] = $name;
                	} else {
                    	$this->_parts[$part[0]] = $part[1];
                    	$this->_staticCount++;
                	}
                } else if ($part[0] == '*')
                {
                	$this->_parts[] = $part[0];
                }
            }
        }

    }

    /**
     * Matches a user submitted path with parts defined by a map. Assigns and
     * returns an array of variables on a successful match.
     *
     * @param string $path Path used to match against this routing map
     * @return array|false An array of assigned values or a false on a mismatch
     */
    public function match($path)
    {
        $pathStaticCount = 0;
        $values = array();
        $path = trim($path, $this->_urlDelimiter);
 		if(strpos($path, '?')!==false)
        	$path = substr($path, strpos($path, '?')+1);
        if ($path != '') {

            $path = explode($this->_urlDelimiter, $path);
            foreach ($path as $pos => $pathPart) {
				$pathPart = explode('=', $pathPart);
				$pathName = $pathPart[0];
				$pathPart = $pathPart[1];
				//var_dump($this->_parts);// die();
                // Path is longer than a route, it's not a match

                if (!array_key_exists($pathName, $this->_parts) ) {
                    if(in_array('*', $this->_parts))
                    {
                    	if (!isset($this->_wildcardData[$pathName]) && !isset($this->_defaults[$pathName]) && !isset($values[$pathName])) {
                            $this->_wildcardData[$pathName] = (!is_null($pathPart)) ? urldecode($pathPart) : null;
                        	$this->_staticCount++;
                        }
                    } else
                    	return false;
                }

                // If it's a wildcard, get the rest of URL as wildcard data and stop matching
                //?????
                /*if ($this->_parts[$pos] == '*') {
                    $count = count($path);
                    for($i = $pos; $i < $count; $i+=2) {
                        $var = urldecode($path[$i]);
                        $pathVar = explode('=', $var);
                        if (!isset($this->_wildcardData[$pathVar[0]]) && !isset($this->_defaults[$pathVar[0]]) && !isset($values[$pathVar[0]])) {
                            $this->_wildcardData[$pathVar[0]] = (!is_null($pathVar[1])) ? urldecode($pathVar[1]) : null;
                        }
                    }
                    break;
                }*/

                $name = isset($this->_variables[$pathName]) ? $this->_variables[$pathName] : null;
                $pathPart = urldecode($pathPart);

                // If it's a static part, match directly
                if ($name === null && $this->_parts[$pathName] != $pathPart && $this->_wildcardData[$pathName] != $pathPart) {
                    return false;
                }

                // If it's a variable with requirement, match a regex. If not - everything matches
                if ($this->_parts[$pathName] !== null && !preg_match($this->_regexDelimiter . '^' . $this->_parts[$pathName] . '$' . $this->_regexDelimiter . 'iu', $pathPart)) {
                    return false;
                }

                // If it's a variable store it's value for later
                if ($name !== null) {
                    $values[$name] = $pathPart;
                } else {
                    $pathStaticCount++;
                }

            }

        }

        // Check if all static mappings have been matched
        if ($this->_staticCount != $pathStaticCount) {
            return false;
        }

        $return = $values + $this->_wildcardData + $this->_defaults;

        // Check if all map variables have been initialized
        foreach ($this->_variables as $var) {
            if (!array_key_exists($var, $return)) {
                return false;
            }
        }

        $this->_values = $values;

        return $return;

    }

    /**
     * Assembles user submitted parameters forming a URL path defined by this route
     *
     * @param  array $data An array of variable and value pairs used as parameters
     * @param  boolean $reset Whether or not to set route defaults with those provided in $data
     * @return string Route path with user submitted parameters
     */
    public function assemble($data = array(), $reset = false, $encode = false)
    {
        $url = array();
        $flag = false;

        foreach ($this->_parts as $key => $part) {

            $name = isset($this->_variables[$key]) ? $this->_variables[$key] : null;
            $useDefault = false;
            if (isset($name) && array_key_exists($name, $data) && $data[$name] === null) {
                $useDefault = true;
            }

            if (isset($name)) {

                if (isset($data[$name]) && !$useDefault) {
                    $url[$key] = $name."=".$data[$name];
                    unset($data[$name]);
                } elseif (!$reset && !$useDefault && isset($this->_values[$name])) {
                    $url[$key] = $name."=".$this->_values[$name];
                } elseif (!$reset && !$useDefault && isset($this->_wildcardData[$name])) {
                    $url[$key] = $name."=".$this->_wildcardData[$name];
                } elseif (isset($this->_defaults[$name])) {
                    $url[$key] = $name."=".$this->_defaults[$name];
                } else {
                    require_once 'Zend/Controller/Router/Exception.php';
                    throw new Zend_Controller_Router_Exception($name . ' is not specified');
                }


            } elseif ($part != '*') {
                $url[$key] = $part;
            } else {
                if (!$reset) $data += $this->_wildcardData;
                foreach ($data as $var => $value) {
                    if ($value !== null) {
                        $url[$key++] = $var."=".$value;
//                        $url[$key++] = $value;
                        $flag = true;
                    }
                }
            }

        }
        $return = '';

        foreach (array_reverse($url, true) as $key => $value) {
            if ($flag || !isset($this->_variables[$key]) || $value !== $this->getDefault($this->_variables[$key])) {
                if ($encode) $value = urlencode($value);
                $return = $this->_urlDelimiter . $value . $return;
                $flag = true;
            }
        }
//var_dump($return);
        return trim($return, $this->_urlDelimiter);

    }

    /**
     * Return a single parameter of route's defaults
     *
     * @param string $name Array key of the parameter
     * @return string Previously set default
     */
    public function getDefault($name) {
        if (isset($this->_defaults[$name])) {
            return $this->_defaults[$name];
        }
        return null;
    }

    /**
     * Return an array of defaults
     *
     * @return array Route defaults
     */
    public function getDefaults() {
        return $this->_defaults;
    }
}
?>
