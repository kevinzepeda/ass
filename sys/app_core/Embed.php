<?php 

class Embed extends Media{
	
	public $data = array(
    	'youtube' => '',
    	'vimeo' => '',
    	'dailymotion' => '',
    	'video_type' => '',
    	'thumbnail' => '',
    	'title' => '',
    	'description' => '',
    	'tags' => '',
    	'tags_array' => '',
    	'duration' => '',
    );

	public function fetchVideo($link=''){

		if (preg_match('#(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})#i', $link, $match)) {
		    $this->data['youtube'] = self::secure($match[1]);
		    $this->data['video_type'] = 'youtube';
		} 

		else if (preg_match("#https?://vimeo.com/([0-9]+)#i", $link, $match)) {
		    $this->data['vimeo'] = self::secure($match[1]);
		    $this->data['video_type'] = 'vimeo';
		} 

		else if (preg_match('#http://www.dailymotion.com/video/([A-Za-z0-9]+)#s', $link, $match)) {
		    $this->data['dailymotion'] = self::secure($match[1]);
		    $this->data['video_type'] = 'daily';
		}
		    
	    if (!empty($this->data['youtube'])) {
	    	
	    	try {
	    		$youtube = new Madcoda\Youtube(array('key' => self::$config['yt_api']));
	            $get_videos = $youtube->getVideoInfo($this->data['youtube']);

	            if (!empty($get_videos)) {
		    		if (!empty($get_videos->snippet)) {
	            		if (!empty($get_videos->snippet->thumbnails->medium->url)) {
	            			$this->data['thumbnail'] = $get_videos->snippet->thumbnails->medium->url;
	            		} 
		    			$info = $get_videos->snippet;
		    			$this->data['title'] = $info->title;
		    			if (!empty(px_covtime($get_videos->contentDetails->duration))) {
		    				$this->data['duration'] = px_covtime($get_videos->contentDetails->duration);
		    			}
		    			$this->data['description'] = $info->description;
		    			$this->data['tags']        = '';
		    		}
		    	}
	    	} 
	    	catch (Exception $e) {
	    		echo $e->getMessage();
	    	}
	    } 

	    else if (!empty($this->data['dailymotion'])) {

	    	$api_request = $this->curlConnect('https://api.dailymotion.com/video/' . $this->data['dailymotion'] . '?fields=thumbnail_large_url,title,duration,description,tags');

	    	if (!empty($api_request)) {

	    		if (!empty($api_request['title'])) {
	    			$this->data['title'] = $api_request['title'];
	    		}

	    		if (!empty($api_request['description'])) {
	    			$this->data['description'] = $api_request['description'];
	    		}

	    		if (!empty($api_request['thumbnail_large_url'])) {
	    			$this->data['thumbnail'] = $api_request['thumbnail_large_url'];
	    		}

	    		if (!empty($api_request['duration'])) {
	    			$this->data['duration'] = gmdate("i:s", $api_request['duration']);
	    		}

	    		if (is_array($api_request['tags'])) {
					$this->data['tags'] = implode(',', $api_request['tags']);
				}
	    	}
	    } 

	    else if (!empty($this->data['vimeo'])) {

	    	$api_request = $this->curlConnect('http://vimeo.com/api/v2/video/' . $this->data['vimeo'] . '.json');
	    	if (!empty($api_request)) {
	    		$api_request = end($api_request);
	    		if (!empty($api_request['title'])) {
	    			$this->data['title'] = $api_request['title'];
	    		}

	    		if (!empty($api_request['description'])) {
	    			$this->data['description'] = $api_request['description'];
	    		}

	    		if (!empty($api_request['thumbnail_large'])) {
	    			$this->data['thumbnail'] = $api_request['thumbnail_large'];
	    		}

	    		if (!empty($api_request['duration'])) {
	    			$this->data['duration'] = gmdate("i:s", $api_request['duration']);
	    		}

	    		if (!empty($api_request['tags'])) {
					$this->data['tags'] = $api_request['tags'];
				}
	    	}
	    } 
	    
	    return $this->data;
	}
	
	public function videoIdExists($type = ""){
		self::$db->where($type, $this->$data[$type]);
		return (self::$db->getValue(T_POSTS, 'count(*)') > 0);
	}
}



