<?php

namespace Youtube;

//use Oil\Exception;

class User
{
    protected $user;

    protected function __construct($un)
    {
        $this->user = $un;
    }

    public static function forge($un)
    {
        return new static($un);
    }
    public function videos($params = array()){
        $default_params = array(
            'max-results' => 5,
        );
        $params = array_merge($default_params, $params);

        $url = 'http://gdata.youtube.com/feeds/api/users/'.$this->user.'/uploads?';
        $url .= http_build_query($params);
		$xml = simplexml_load_file($url);
		foreach ($xml->entry as $entry) {
			$media = $entry->children('media', true);
			$url = $media->group->player->attributes()->url;
			$id = substr($url,strpos($url,'v=')+2);
			$id = substr($id,0,strpos($id,'&'));
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
				'id'=> $id
			);
		}
		return $videos;
    }
}