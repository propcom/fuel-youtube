<?php

namespace Youtube;

abstract class Youtube implements \ArrayAccess, \Iterator
{

	protected $id;

	protected static $_service;

	protected function __construct($id)
	{
		if (is_object($id)) {
			$this->from_source($id);
		} else {
			$this->id = $id;
		}
	}

	/**
	 * @param mixed $id
	 * @return object
	 */
	public static function forge($id)
	{
		return new static($id);
	}

	/**
	 * Populate object using API source data
	 * @param object $source
	 */
	public abstract function from_source($source);

	public static function service()
	{
		if ( ! static::$_service) {
			if ( ! \Config::get('youtube.api_key')) {
				throw new \Exception('API key not provided.');
			}

			$client = new \Google_Client();
			$client->setDeveloperKey(\Config::get('youtube.api_key'));
			static::$_service = new \Google_Service_YouTube($client);
		}
		return static::$_service;
	}


	/**
	 * @param void
	 * @return string
	 */
	public function get_id()
	{
		return $this->id;
	}


	// Implementation of ArrayAccess -----------------------

	public function offsetExists($offset)
	{
		return $this->__isset($offset);
	}


	public function offsetGet($offset)
	{
		try {
			return $this->__get($offset);
		}
		catch (\Exception $e) {
			return false;
		}
	}


	public function offsetSet($offset, $value)
	{
		try {
			$this->$offset = $value;
		}
		catch (\Exception $e) {
			return false;
		}
	}


	public function offsetUnset($offset)
	{
		return false;
	}


	// Implementation of Iterator --------------------------


	protected $_iterable = [];


	public function current()
	{
		return current($this->_iterable);
	}


	public function next()
	{
		return next($this->_iterable);
	}


	public function key()
	{
		return key($this->_iterable);
	}


	public function rewind()
	{
		$this->_iterable = [];
		foreach (get_object_vars($this) as $key => $value) {
			if (0 !== strpos($key, '_')) {
				$this->_iterable[$key] = $value;
			}
		}
		reset($this->_iterable);
	}


	public function valid()
	{
		return key($this->_iterable) !== null;
	}

}
