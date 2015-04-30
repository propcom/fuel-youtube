<?php

namespace Youtube;

use Oil\Exception;

class Video extends Youtube
{
	protected $channel_id;
	protected $channel_title;
	protected $title;
	protected $description;
	protected $published;
	protected $thumbnails = [
		'Default' => null,
		'High' => null,
		'Maxres' => null,
		'Medium' => null,
	];


	/**
	 * @param object $source Google_Service_YouTube_PlaylistItem
	 * @param object
	 */
	public function from_source($source)
	{
		$this->id = $source['snippet']['resourceId']['videoId'];
		$this->channel_id = $source['snippet']['channelId'];
		$this->channel_title = $source['snippet']['channelTitle'];
		$this->title = $source['snippet']['title'];
		$this->description = $source['snippet']['description'];
		$this->published = $source['snippet']['publishedAt'];
		foreach ($this->thumbnails as $size => $value) {
			if ($thumbnail = $source['snippet']->getThumbnails()->{"get$size"}()) {
				$this->thumbnails[$size] = $thumbnail->getUrl();
			}
		}
		return $this;
	}


	public function get_author()
	{
		return $this->channel_title;
	}


	public function get_author_url()
	{
		return 'https://www.youtube.com/channel/' . $this->channel_id;
	}


	public function get_thumbnail($size = 'Default')
	{
		return $this->thumbnails[$size];
	}


	public function get_title()
	{
		return $this->title;
	}


	/**
	 * @param void
	 * @return string
	 */
	public function get_url()
	{
		return 'https://www.youtube.com/watch?v='.$this->id;
	}


	/**
	 * @param array $params
	 * @return string
	 */
	public function embed(array $params = array())
	{
		$height = \Arr::get($params, 'height', \Config::get('youtube.embed.height'));
		$width = \Arr::get($params, 'width', \Config::get('youtube.embed.width'));
		return "<iframe width=\"$width\" height=\"$height\" src=\"http://www.youtube.com/embed/$this->id\" frameborder=\"0\" allowfullscreen></iframe>";
	}


	public function data()
	{
        return array(
            'details'=>$this->details(),
            'related'=>$this->get('related'),
            'comments'=>$this->get('comments'),
            'responses'=>$this->get('responses'),
            'embed'=>$this->embed()
        );
    }


	public function details()
	{

		// -------------------------------------------------

		if ( ! \Config::get('youtube.api_key')) {
			throw new \Exception('API key not provided.');
		}
		$client = new \Google_Client();
		$client->setDeveloperKey(\Config::get('youtube.api_key'));
		$youtube = new \Google_Service_YouTube($client);

		$playlistItems = $youtube->videos->listVideos(
			'snippet',
			[
				'id' => $this->id,
			]
		);

		foreach ($playlistItems as $item) {
			$video = [
				'id' => $this->id,
				'published' => $item['snippet']['publishedAt'],
				'updated' => null, //$d['updated'],
				'category' => null, //$d['category'][1]['attributes']['label'],
				'title' => $item['snippet']['title'],
				'content' => $item['snippet']['description'],
				'link' => [
					'mobile' => null, //(isset($d['link'][3]['attributes']['href'])?$d['link'][3]['attributes']['href']:''),
					'desktop' => null, //$d['link'][0]['attributes']['href']
				],
				'author' => [
					'name' => null, //$d['author']['name'],
					'link' => null, //$d['author']['uri']
				],
			];
			return $video;
		}

		// -------------------------------------------------




        $url = "http://gdata.youtube.com/feeds/api/videos/".$this->id;
        $d = Utils::request($url);
        $data = array(
            'id' => $this->id,
            'published' => $d['published'],
            'updated' => $d['updated'],
            'category' => $d['category'][1]['attributes']['label'],
            'title' => $d['title'],
            'content' => $d['content'],
            'link' => array(
                'mobile' => (isset($d['link'][3]['attributes']['href'])?$d['link'][3]['attributes']['href']:''),
                'desktop' => $d['link'][0]['attributes']['href']
            ),
            'author' => array(
                'name' => $d['author']['name'],
                'link' => $d['author']['uri']
            ),
        );

        return Utils::array2Object($data);
    }
    public function get($what = "comments",$sort = "DESC"){
        $url = "http://gdata.youtube.com/feeds/api/videos/" . $this->id . '/' . $what;
        $d = Utils::request($url);
        $data = array();
        if(isset($d['entry'])){
            if(isset($d['entry'][0])){
                foreach($d['entry'] as $e){
                    $data[] = self::process_entry($what,$e);
                }
            }else{
                $data[] = self::process_entry($what,$d['entry']);
            }
        }
        if($sort == "DESC"){
            rsort($data);
        }
        return Utils::array2Object($data);
    }

    protected static function process_entry($what,$e){
        switch($what){
            case "comments":
                $return = self::process_comment($e);
                break;
            case "responses":
                $return = self::process_response($e);
                break;
            case "related":
                $return = self::process_related($e);
                break;
            default:
                return false;
                break;
        }
        return $return;
    }

    protected static function process_comment($c){
        $comment = array(
            'published' => $c['published'],
            'updated' => $c['updated'],
            'title' => $c['title'],
            'content' => (is_string($c['content'])?$c['content']:""),
            'author' => array(
                'name' => $c['author']['name'],
                'link' => $c['author']['uri']
            )
        );
        return $comment;
    }

    protected static function process_response($r){
        $start = strpos($r['link'][1]['attributes']['href'],'v=')+2;
        $len = strpos($r['link'][1]['attributes']['href'],'&')-$start;
        $id = substr($r['link'][1]['attributes']['href'],$start,$len);
        $res = array(
            'published' => $r['published'],
            'updated' => $r['updated'],
            'title' => $r['title'],
            'content' => (is_string($r['content'])?$r['content']:""),
            'link' => array(
                'mobile' => (isset($r['link'][4]['attributes']['href'])?$r['link'][4]['attributes']['href']:''),
                'desktop' => $r['link'][1]['attributes']['href']
            ),
            'author' => array(
                'name' => $r['author']['name'],
                'link' => $r['author']['uri']
            ),
            'id' => $id
        );
        return $res;
    }

    protected static function process_related($r){
        $start = strpos($r['link'][0]['attributes']['href'],'v=')+2;
        $len = strpos($r['link'][0]['attributes']['href'],'&')-$start;
        $id = substr($r['link'][0]['attributes']['href'],$start,$len);
        $rel = array(
            'published' => $r['published'],
            'updated' => $r['updated'],
            'title' => $r['title'],
            'content' => (is_string($r['content'])?$r['content']:""),
            'category' => $r['category'][1]['attributes']['label'],
            'link' => array(
                'mobile' => (isset($r['link'][3]['attributes']['href'])?$r['link'][3]['attributes']['href']:''),
                'desktop' => $r['link'][0]['attributes']['href']
            ),
            'author' => array(
                'name' => $r['author']['name'],
                'link' => $r['author']['uri']
            ),
            'id' => $id
        );
        return $rel;
    }

}
