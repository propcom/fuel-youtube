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
		return $videos;
	}

}
