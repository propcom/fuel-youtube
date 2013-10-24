<?php

namespace Youtube;

use Oil\Exception;

class Video
{
    protected $id;

    protected function __construct($id)
    {
        $this->id = $id;
    }

    public static function forge($id)
    {
        return new static($id);
    }
    public function data(){
        return array(
            'details'=>$this->details(),
            'related'=>$this->get('related'),
            'comments'=>$this->get('comments'),
            'responses'=>$this->get('responses'),
            'embed'=>$this->embed()
        );
    }
    public function details(){
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
                'mobile' => $d['link'][3]['attributes']['href'],
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
                'mobile' => $r['link'][4]['attributes']['href'],
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
                'mobile' => $r['link'][3]['attributes']['href'],
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

    public function embed($params = array()){
        if(isset($params['height'])){
            $height = $params['height'];
            unset($params['height']);
        }else{
            $height = 315;
        }
        if(isset($params['width'])){
            $width = $params['width'];
            unset($params['width']);
        }else{
            $width = 560;
        }
        if(!isset($params['wmode'])){
            $params['wmode'] = 'transparent';
        }
        $string = "";
        foreach($params as $k => $p){
            if($string == ""){
                $string .= "?";
            }else{
                $string .= "&";
            }
            $string .= $k .'='. $p;
        }
        $string = $this->id.$string;
        return "<iframe width=\"$width\" height=\"$height\" src=\"http://www.youtube.com/embed/$string\" frameborder=\"0\" allowfullscreen></iframe>";
    }
}