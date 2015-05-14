<?php

namespace Youtube;

class User
{

	protected $user;


	protected function __construct($un)
	{
	\Config::load('youtube',true);
		if($un){
		$this->user = $un;
		}else{
			$user =\Config::get('youtube.user');
		}
	}


	public static function forge($un)
	{
		return new static($un);
	}


	/**
	 * Get user's uploads
	 *
	 * @param array $params
	 * @return array
	 */
	public function videos(array $params = [])
	{
		// get channel associated with Youtube username
		try {
			$response = \Cache::get('youtube_user_'.$this->user.'_channels');
		}
		catch (\CacheNotFoundException $e) {
			try {
				$response = \Youtube\Youtube::service()->channels->listChannels(
					'snippet, contentDetails',
					[
						'forUsername' => $this->user,
					]
				);
				\Cache::set(
					'youtube_user_'.$this->user.'_channels',
					$response,
					\Config::get('youtube.cache_expiration', 300)
				);
			}
			catch (\Google_Service_Exception $e) {
				\Log::error('Google error: '.$e, __METHOD__);
				return [];
			}
		}

		if ( ! $response['items']) {
			\Log::error("Unable to find channels associated with user '{$this->user}'", __METHOD__);
		}

		foreach ($response['items'] as $item) {
			if ($uploads = \Arr::get($item,'contentDetails.relatedPlaylists.uploads')) {
				$playlist = \Youtube\Playlist::forge($uploads);
				return $playlist->get_videos($params);
			}
		}

		return [];
	}

}
