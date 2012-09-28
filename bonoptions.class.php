<?php
/**
 * BonOptions
 * Helper class to handle get/set options
 *
 * @licence GPL
 * @version 1.00
 * @created on 2012 Sep 27 - 1391 Shahrivar 6
 * @author Amir Hossein Hodjati Pour <me@Amir-Hossein.com>
 */
class BonOptions
{
    /**
     * @access protected
     * @var array Options
     */
    protected $_options = array();
    /**
     * @access protected
     * @var bool Hydration
     */
    protected $_hydration = false;
    /**
     * @access protected
     * @var string Options Key
     */
    protected $_key;
    /**
     * @access protected
     * @var callable Reader Validator. bool function(string $key[, additional arguments]);
     */
    protected $_readerValidator;
    /**
     * @access protected
     * @var array Additional arguments to append to Reader Validator arguments.
     */
    protected $_readerValidatorAdditionalArguments = array();
    /**
     * @access protected
     * @var callable Writer Validator. bool function(string $key, mixed $value[, additional arguments]);
     */
    protected $_writerValidator;
    /**
     * @access protected
     * @var array Additional arguments to append to Writer Validator arguments.
     */
    protected $_writerValidatorAdditionalArguments = array();

    /**
     * Construct
     *
     * @param string|null $key Optional Key for options
     * @return \BonOptions
     */
    public function __construct($key = null)
    {
        $this->key($key);
        $this->setWriterValidator(function($key, $value){ return true; });
        $this->setReaderValidator(function($key){ return true; });
    }

    /**
     * Hydration Getter/Setter
     *
     * @param bool|null $isHydration
     * @return bool
     */
    public function hydration($isHydration = null)
    {
        if (isset($isHydration)) {
            $this->_hydration = (bool)$isHydration;
        }
        return $this->_hydration;
    }

    /**
     * Setter
     *
     * @param string|array $keys
     *      string: Single keys and using $value as value
     *      array: Associative array
     * @param mixed|void $value
     *      mixed: while $key is string
     *      void: while $key is array
     * @return void
     */
    public function set($keys, $value = null)
    {
        if (is_string($keys)) {
            $keys = array($keys => $value);
        }
        if (is_array($keys)) {
            foreach ($keys as $key => $value) {
                if ($this->_writerValidator($key, $value) === true) {
                    $this->_options[$key] = $value;
                }
            }
        }
    }

    /**
     * Getter
     *
     * @param string|array|void
     *      string: One or more keys to retrieve
     *      array: List of keys as array to get
     *      void
     * @return mixed Single mixed value while single string key, else associtive array of values
     * @example
     *      $bontions->get(); // Get options as array
     *      $bontions->get('key1'); // Get value assigned to "key1" as mixed
     *      $bontions->get('key1', 'key2'); // array('key1'=>val1, 'key2'=>val2)
     *      $bontions->get( array('key1','key2') ); // array('key1'=>val1, 'key2'=>val2)
     */
    public function get()
    {
        switch (func_num_args()) {
            case 0:
                $options = array();
                foreach ($this->_options as $key => $value) {
                    if ($this->has($key) === true) {
                        $options[$key] = $value;
                    }
                }
                return $options;
                break;
            case 1:
                $key = func_get_arg(0);
                if (is_string($key) || is_numeric($key)) {
                    if ($this->has($key) === true) {
                        return $this->_options[$key];
                    }
                } elseif (is_array($key)) {
                    $values = array();
                    foreach ($key as $k) {
                        if ($this->has($k) === true) {
                            $values[$k] = $this->_options[$k];
                        }
                    }
                    return $values;
                }
                break;
            default:
                $keys = array();
                foreach (func_get_args() as $arg) {
                    if (is_string($arg)) {
                        $keys[] = $arg;
                    } elseif (is_array($arg)) {
                        $keys = array_merge($keys, $arg);
                    }
                }
                $values = array();
                foreach ($keys as $k) {
                    if ($this->has($k) === true) {
                        $values[$k] = $this->_options[$k];
                    }
                }
                return $values;
                break;
        }
        return null;
    }

    /**
     * Checker
     *
     * @param string $key Key to check
     * @return bool
     */
    public function has($key)
    {
        return (array_key_exists($key, $this->_options) && $this->_readerValidator($key) === true);
    }

    /**
     * Find and filter values
     *
     * @param array|string $key String key or Array Keys
     * @param callable $filterFunction Function to call for each value
     * @param array $filterArguments Optional Arguments for filterFunction
     * @return array|mixed|null
     */
    public function filter($key, $filterFunction, array $filterArguments = array())
    {
        if (is_string($key)) {
            $values = $this->_filter(array($key), $filterFunction, $filterArguments);
            return empty($values) ? null : current($values);
        }
        if (is_array($key)) {
            $values = $this->_filter($key, $filterFunction, $filterArguments);
            return $values;
        }
        return null;
    }

    /**
     * Find and filter values
     *
     * @access protected
     * @param array $keys Array Keys
     * @param callable $filterFunction Function to call for each value
     * @param array $filterArguments Arguments for filterFunction
     * @return array
     */
    protected function _filter(array $keys, $filterFunction, array $filterArguments)
    {
        $values = call_user_func_array(array($this, 'get'), array($keys));
        foreach ($values as $key => $value) {
            $values[$key] = call_user_func_array($filterFunction, array_merge(array($value), $filterArguments));
        }
        return $values;
    }

    /**
     * Reset storage
     *
     * @return void
     */
    public function reset()
    {
        $this->_options = array();
    }

    /**
     * Setter validator
     *
     * @param string $key Option key
     * @param mixed $value
     * @return bool
     */
    protected function _writerValidator($key, $value)
    {
        $arguments = array_merge(array($key, $value), $this->_writerValidatorAdditionalArguments);
        return call_user_func_array($this->_writerValidator, $arguments);
    }

    /**
     * Getter validator
     *
     * @access protected
     * @param string $key Optioin key
     * @return bool
     */
    protected function _readerValidator($key)
    {
        $arguments = array_merge(array($key), $this->_readerValidatorAdditionalArguments);
        return call_user_func_array($this->_readerValidator, $arguments);
    }

    /**
     * Set/Get Key
     *
     * @param string|void|NULL String to set key, Void/NULL to get Key
     * @return string Key
     */
    public function key($key = null)
    {
        if (isset($key)) {
            $this->_key = $key;
        }
        return $this->_key;
    }

    /**
     * Set Reader Validator callback
     *
     * @param callable $callable Reader Validator callback
     * @param array $additionalArguments
     * @return void
     */
    public function setReaderValidator($callable, $additionalArguments = array())
    {
        $this->_readerValidator = $callable;
        $this->_readerValidatorAdditionalArguments = $additionalArguments;
    }

    /**
     * Set Writer Validator callback
     *
     * @param callable $callable Writer Validator callback
     * @param array $additionalArguments
     * @return void
     */
    public function setWriterValidator($callable, $additionalArguments = array())
    {
        $this->_writerValidator = $callable;
        $this->_writerValidatorAdditionalArguments = $additionalArguments;
    }

    /**
     * Overloads
     */
    public function __get($var)
    {
        if ($this->_hydration === true) {
            return $this->get($var);
        }
        return null;
    }

    public function __isset($var)
    {
        if ($this->_hydration === true) {
            return $this->has($var);
        }
        return false;
    }

    public function __set($var, $value)
    {
        if ($this->_hydration === true) {
            $this->set($var, $value);
        }
    }

    public function __toString()
    {
        return get_class($this) . ($this->_key ? ': ' . $this->_key : 'Instance');
    }
}