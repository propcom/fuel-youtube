<?php

namespace Youtube;

class Playlist extends Youtube
{

	public function from_source($source) {}

	/**
	 * @param void
	 * @return array Videos
	 */
	public function get_videos()
	{
		try {
			$videos = \Cache::get('youtube_playlist_'.$this->id.'_videos');
		}
		catch (\CacheNotFoundException $e) {
			\Log::debug(__METHOD__);
			$videos = [];
			try {
				$playlistItems = static::service()->playlistItems->listPlaylistItems(
					'snippet',
					[
						'playlistId' => $this->id,
						'maxResults' => \Config::get('youtube.feed.length', 10),
					]
				);
				foreach ($playlistItems['items'] as $item) {
					$video = Video::forge($item);
					$videos[$video->get_id()] = $video;
				}
			}
			catch (\Exception $e) {
				\Log::error($e->getMessage(), __METHOD__);
				\Log::error($e->getLine(), $e->getFile());
			}

			\Cache::set(
				'youtube_playlist_'.$this->id.'_videos',
				$videos,
				\Config::get('youtube.cache_expiration', 300)
			);
		}

		return $videos;
	}

}
