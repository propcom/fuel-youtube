<?php

namespace Youtube;

class Playlist extends Youtube
{

	/**
	 * @param array $params
	 * @return array Videos
	 */
	public function get_videos(array $params = [])
	{
		try {
			$response = \Cache::get('youtube_playlist_'.$this->id.'_videos');
		}
		catch (\CacheNotFoundException $e) {
			try {
				$response = static::service()->playlistItems->listPlaylistItems(
					'snippet',
					[
						'playlistId' => $this->id,
						'maxResults' => 50,
					]
				);
				\Cache::set(
					'youtube_playlist_'.$this->id.'_videos',
					$response,
					\Config::get('youtube.cache_expiration', 300)
				);
			}
			catch (\Google_Service_Exception $e) {
				\Log::error('Google error: '.$e, __METHOD__);
				return [];
			}
		}

		$videos = [];
		$max_results = \Arr::get($params, 'max_results', \Config::get('youtube.feed.length', 10));
		foreach (\Arr::get($response, 'items', []) as $item) {
			$video = Video::forge($item);
			$videos[$video->get_id()] = $video;
			if (count($videos) >= $max_results) {
				break;
			}
		}

		return $videos;
	}

}
