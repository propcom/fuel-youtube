<?php

namespace Youtube;

class Playlist extends Youtube
{

	public function from_source($source) {}


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
			\Log::debug('fetch videos', __METHOD__.':'.__LINE__);
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
