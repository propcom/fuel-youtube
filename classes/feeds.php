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

		$url = 'http://gdata.youtube.com/feeds/api/users/'.$user.'/uploads';
		// The parameters will generate a feed from YouTube's search index, effectively
		// restricting the result set to indexed, public videos, rather than returning
		// a complete list of the user's uploaded videos.
		// https://developers.google.com/youtube/2.0/developers_guide_protocol_video_feeds#User_Uploaded_Videos
		if (count($params) > 1) {
			$url .= '?' . http_build_query($params);
		}

		$videos = array();

		try {
			$xml = simplexml_load_file($url);

			foreach ($xml->entry as $entry) {
				$media = $entry->children('media', true);

				$videos[] = array(
					'title'       => (string) $media->group->title,
					'description' => (string) $media->group->description,
					'url'         => (string) \Uri::create($media->group->player->attributes()->url, array(), array(), true),
					'author'      => (string) $entry->author->name,
					'author_url'  => (string) \Uri::create($entry->author->uri, array(), array(), true),
					'thumbnails'  => array(
						(string) \Uri::create($media->group->thumbnail[0]->attributes()->url, array(), array(), true),
						(string) \Uri::create($media->group->thumbnail[1]->attributes()->url, array(), array(), true),
						(string) \Uri::create($media->group->thumbnail[2]->attributes()->url, array(), array(), true),
						(string) \Uri::create($media->group->thumbnail[3]->attributes()->url, array(), array(), true),
					),
					'published' => (string) $entry->published,
				);
				if (count($videos) >= $params['max-results']) {
					break;
				}
			}
		} catch (\Exception $e) {
			\Log::error($e->getMessage(), __METHOD__);
		}

		return $videos;
	}

	/**
	 * @param void
	 * @return object
	 */
	protected static function _get_service()
	{
		$client = new \Google_Client();
		$client->setDeveloperKey(\Config::get('youtube.api_key'));
		return new \Google_Service_YouTube($client);
	}

}
