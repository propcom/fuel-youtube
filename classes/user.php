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
		$response = \Youtube\Youtube::service()->channels->listChannels(
			'snippet, contentDetails',
			[
				'forUsername' => $this->user,
			]
		);

		foreach ($response['items'] as $item) {
			$uploads = $item['contentDetails']['relatedPlaylists']['uploads'];
			$playlist = \Youtube\Playlist::forge($uploads);
			return $playlist->get_videos($params);
		}

		return [];
	}

}
