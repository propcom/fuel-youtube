<?php

namespace Youtube;

abstract class Youtube
{

	protected $id;

	protected static $service;

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
		if ( ! static::$service) {
			if ( ! \Config::get('youtube.api_key')) {
				throw new \Exception('API key not provided.');
			}

			$client = new \Google_Client();
			$client->setDeveloperKey(\Config::get('youtube.api_key'));
			static::$service = new \Google_Service_YouTube($client);
		}
		return static::$service;
	}

	/**
	 * @param void
	 * @return string
	 */
	public function get_id()
	{
		return $this->id;
	}

}
