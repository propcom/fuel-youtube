<?php

namespace Youtube;

class Feeds
{
	public static function get_videos(array $params=array(), $user=false)
	{
		if ( ! $user) {
			$user = \Config::get('youtube.user');

			if ( ! $user) {
				\Log::error('Requires user, either config or param', __METHOD__);
				return array();
			}
		}

		$default_params = array(
			'max-results' => 5,
		);

		$params = array_merge($default_params, $params);

		$url = 'http://gdata.youtube.com/feeds/api/users/'.$user.'/uploads?';
		$url .= http_build_query($params);

		$videos = array();

		try {
			$xml = simplexml_load_file($url);

			foreach ($xml->entry as $entry) {
				$media = $entry->children('media', true);

				$videos[] = array(
					'title'       => (string) $media->group->title,
					'description' => (string) $media->group->description,
					'url'         => (string) $media->group->player->attributes()->url,
					'author'      => (string) $entry->author->name,
					'author_url'  => (string) $entry->author->uri,
					'thumbnails'  => array(
						(string) $media->group->thumbnail[0]->attributes()->url,
						(string) $media->group->thumbnail[1]->attributes()->url,
						(string) $media->group->thumbnail[2]->attributes()->url,
						(string) $media->group->thumbnail[3]->attributes()->url,
					),
				);
			}
		} catch (\Exception $e) {
			\Log::error($e->getMessage(), __METHOD__);
		}

		return $videos;
	}
}
