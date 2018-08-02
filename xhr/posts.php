<?php 

if ($action == 'new-image' && IS_LOGGED && ($config['upload_images'] == 'on')) {
	$smarty->setDir('home/templates/home');
	$data['status'] = 200;
	$data['html']   = $smarty->fetch('includes/upload-image.tpl');
}

else if ($action == 'new-video' && IS_LOGGED && ($config['upload_videos'] == 'on')) {
	$smarty->setDir('home/templates/home');
	if ($config['ffmpeg_sys'] == 'on') {
		$data['html']   = $smarty->fetch('includes/ffmpeg-upload-video.tpl');
		$data['status'] = 200;
	}
	else{
		$data['html']   = $smarty->fetch('includes/upload-video.tpl');
		$data['status'] = 200;
	}
}

else if ($action == 'new-embed' && IS_LOGGED && ($config['import_videos'] == 'on')) {
	$smarty->setDir('home/templates/home');
	$data['status'] = 200;
	$data['html'] = $smarty->fetch('includes/embed-video.tpl');
}

else if ($action == 'new-gif' && IS_LOGGED && ($config['import_images'] == 'on')) {
	$smarty->setDir('home/templates/home');
	$data['status'] = 200;
	$data['html'] = $smarty->fetch('includes/import-gifs.tpl');
}

else if ($action == 'new-story' && IS_LOGGED && ($config['story_system'] == 'on')) {
	$smarty->setDir('home/templates/home');
	$story          = new Story();
	$data['status'] = 400;
	$can_addstory   = $story->setUserById($me['user_id'])->canAddStory();
	
	if ($can_addstory) {
		$data['status']  = 200;
		$data['html']    = $smarty->fetch('includes/create-story.tpl');
	}
	else{
		$data['message'] = lang('story_system_limit');
	}
}

else if ($action == 'upload-post-images' && IS_LOGGED && ($config['upload_images'] == 'on')) {
	if (!empty($_FILES) && !empty($_POST['attach']) && is_array($_POST['attach'])) {
		$images  = px_multiple_files($_FILES['images']);
		$media   = new Media();
		$posts   = new Posts();
		$notif   = new Notifications();
		$uploads = array();
		$attach  = $_POST['attach'];
		$up_size = 0;
		$mx_size = $config['max_upload'];

		foreach ($images as $image){
			$up_size += $image['size'];
		}

		if ($up_size <= $mx_size) {
			foreach ($images as $key => $image) {

				if (in_array($key, $attach) && $media->isImage($image['tmp_name'])) {
					$file_info = array(
						'file' => $image['tmp_name'],
						'name' => $image['name'],
						'size' => $image['size'],
						'type' => $image['type'],
						'allowed' => 'jpeg,jpg,png,webp,gif',
					);
					
					if (count($images) > 1) {
						$file_info['crop'] = array(
							'width' => 600,
							'height' => 600
						);
					}

					$media->setFile($file_info);
					$upload = $media->uploadFile();

					
					if (!empty($upload['filename'])) {
						$uploads[] = $upload['filename']; 
					}
				}
			}

			if (!empty($uploads)) {
				$re_data = array(
					'user_id' => $me['user_id'],
					'time' => time(),
					'type' => 'image',
				);

				if (!empty($_POST['caption'])) {
					$text = Generic::cropText($_POST['caption'],$config['caption_len']);
					$re_data['description'] = $text;
				}

				$post_id = $posts->insertPost($re_data);
				if (is_numeric($post_id)) {
					foreach ($uploads as $key => $file) {
						$re_data = array(
							'post_id' => $post_id,
							'file' => $file,
						);
						$posts->insertMedia($re_data);
					}


					$posts->setPostId($post_id);
					$post_data = o2array($posts->postData());

					$smarty->setDir('home/templates/home');
					$smarty->assign('post_data',$post_data);
					$data['html']    = $smarty->fetch('includes/post-image.tpl');
					$data['status']  = 200;
					$data['message'] = lang('post_published');

					#Notify mentioned users
					$notif->notifyMentionedUsers($_POST['caption'],pid2url($post_id));
				}
			}
			else{
				$data['status']  = 400;
				$data['message'] = lang('unknown_error');
			}
		}
		else{
			$mx_size         = $mx_size;
			$data['status']  = 400;
			$data['message'] = str_replace('{{size}}', $mx_size, lang('max_upload_limit'));
		}
	}
}

else if($action == 'ffmpeg-video-upload' && IS_LOGGED && ($config['upload_videos'] == 'on')) {
	if ($config['ffmpeg_sys'] && !empty($_FILES['video']) && file_exists($_FILES['video']['tmp_name'])){
		$ffmpeg  = new FFmpeg($config['ffmpeg_binary']);
		$media   = new Media();
		$posts   = new Posts();
		$notif   = new Notifications();
		$up_size = (!empty($_FILES['video']['size'])) ? $_FILES['video']['size'] : 0;
		$mx_size = $config['max_upload'];

		if ($up_size <= $mx_size) {
			$media->setFile(array(
				'file' => $_FILES['video']['tmp_name'],
				'name' => $_FILES['video']['name'],
				'size' => $_FILES['video']['size'],
				'type' => $_FILES['video']['type'],
				'allowed' => 'mp4,mov,3gp,webm',
			));

			$upload = $media->uploadFile(0, false);
			if (!empty($upload)) {
				try{
					$filepath = explode('.', $upload['filename'])[0];
					$filext   = explode('.', $upload['filename'])[1];
					$ffmpeg->input($upload['filename']);
					$ffmpeg->set('-ss','0');
					$ffmpeg->set('-t', $config['max_video_duration']);
					$ffmpeg->set('-vcodec','h264');
					$ffmpeg->set('-c:v','libx264');
					$ffmpeg->set('-preset','ultrafast');
					$ffmpeg->set('-acodec','mp3');
					$ffmpeg->set('-hide_banner');
					$ffmpeg->forceFormat('mp4');

					$time  = time();
					$video = $ffmpeg->output("$filepath.final.mp4")->ready();
					$video = "$filepath.final.mp4";

					$media->initDir('photos');

	                $dir      = "media/upload/photos/" . date('Y') . '/' . date('m');
	                $hash     = sha1(time() + time() - rand(9999,9999));

	                $thumb    = "$dir/$hash.video_thumb.jpeg";
	                $full_dir = $root;

	                $input_path = $upload['filename'];
	                $output_path = $thumb;

	                #Generete thumb

	                $ffmpeg = new FFmpeg($config['ffmpeg_binary']);
	                $ffmpeg->input($upload['filename']);
					$ffmpeg->set('-ss','2');
					$ffmpeg->set('-vframes','1');
					$ffmpeg->set('-f','mjpeg');
	                $output_thumb = $ffmpeg->output("$output_path")->ready();

	                $re_data = array(
	                	'user_id' => $me['user_id'],
	                	'time' => time(),
	                	'type' => 'video',
	                );

	                if (!empty($_POST['caption'])) {
						$text = Generic::cropText($_POST['caption'],$config['caption_len']);
						$re_data['description'] = $text;
					}

					$post_id = $posts->insertPost($re_data);

					if (is_numeric($post_id)) {
						$re_data = array(
							'post_id' => $post_id,
							'file' => $video,
							'extra' => $thumb,
						);

						$media = new Media;
						if ($config['ftp_upload'] == 1) {
							$media->uploadToFtp($video);
							$media->uploadToFtp($thumb);
						} else if ($config['amazone_s3'] == 1) {
							$media->uploadToS3($video);
							$media->uploadToS3($thumb);
						}

						$posts->setPostId($post_id);
						$posts->insertMedia($re_data);

						
						$post_data = o2array($posts->postData());

						$smarty->setDir('home/templates/home');
						$smarty->assign('post_data',$post_data);
						$data['html']    = $smarty->fetch('includes/post-video.tpl');	
						$data['status']  = 200;
						$data['message'] = lang('post_published');

						#Notify mentioned users
						$notif->notifyMentionedUsers($_POST['caption'],pid2url($post_id));

						@unlink($upload['filename']);

					}
					else{
						$data['status']  = 500;
						$data['message'] = lang('unknown_error');
					}

				}
				catch(Exception $error){
					$data['status']  =  400;
					$data['message'] = lang('unknown_error');
				}
			}
			else{
				$data['status']  = 400;
				$data['message'] = lang('unknown_error');
			}
		}
		else{
			$mx_size         = $mx_size;
			$data['status']  = 400;
			$data['message'] = str_replace('{{size}}', $mx_size, lang('max_upload_limit'));
		}
	}
}

else if($action == 'upload-post-video' && IS_LOGGED){
	if (!empty($_FILES['video']) && !empty($_FILES['thumb'])){
		$media  = new Media();
		$posts  = new Posts();

		$media->setFile(array(
			'file' => $_FILES['video']['tmp_name'],
			'name' => $_FILES['video']['name'],
			'size' => $_FILES['video']['size'],
			'type' => $_FILES['video']['type'],
			'allowed' => 'mp4,mov,3gp,webm',
		));

		$video = $media->uploadFile();

		$media->setFile(array(
			'file' => $_FILES['thumb']['tmp_name'],
			'name' => $_FILES['thumb']['name'],
			'size' => $_FILES['thumb']['size'],
			'type' => $_FILES['thumb']['type'],
			'allowed' => 'jpeg,jpg,png',
			'crop' => array(
				'width' => '600',
				'height' => '400',
			)
		));

		$image = $media->uploadFile();
		if (!empty($video['filename']) && !empty($image['filename'])) {
            
            $re_data = array(
            	'user_id' => $me['user_id'],
            	'time' => time(),
            	'type' => 'video',
            );

            if (!empty($_POST['caption'])) {
				$text = Generic::cropText($_POST['caption'],500);
				$re_data['description'] = $text;
			}

			$post_id = $posts->insertPost($re_data);

			if (is_numeric($post_id)) {
				$re_data = array(
					'post_id' => $post_id,
					'file' => $video['filename'],
					'extra' => $image['filename'],
				);

				$posts->setPostId($post_id);
				$posts->insertMedia($re_data);
	
				$post_data = o2array($posts->postData());

				$smarty->setDir('home/templates/home');
				$smarty->assign('post_data',$post_data);
				$data['html']    = $smarty->fetch('includes/post-video.tpl');	
				$data['status']  = 200;
				$data['message'] = lang('post_published');

			}
			else{
				$data['status']  = 500;
				$data['message'] = lang('unknown_error');
			}
		}
		else{
			$data['status']  = 400;
			$data['message'] = lang('unknown_error');
		}
	}
}

else if($action == 'embed-post-video' && IS_LOGGED && ($config['import_videos'] == 'on')) {

	if (!empty($_POST['embed']) && !empty($_POST['video_id']) && !empty($_POST['url'])) {
		$posts    = new Posts();
		$embed    = new Embed();
		$notif    = new Notifications();
		$emsrc    = (in_array($_POST['embed'], array('youtube','vimeo','dailymotion')) === true);
		$id_val   = false;
 
		if ($_POST['embed'] == 'youtube' && preg_match('/^([a-zA-Z0-9_]{4,15})$/', $_POST['video_id'])) {
			$id_val = true;
		}

		else if($_POST['embed'] == 'vimeo' && preg_match('/^([0-9]+)$/', $_POST['video_id'])){
			$id_val = true;
		}

		else if($_POST['embed'] == 'dailymotion' && preg_match('/^([a-zA-Z0-9_]{4,15})$/', $_POST['video_id'])){
			$id_val = true;
		}

		if ($emsrc && $id_val) {
            $url     = ((Generic::isUrl($_POST['url'])) ? $_POST['url'] : '');
            $emsrc   = $_POST['embed'];

            $re_data = array(
            	"user_id" => $me['user_id'],
            	"link" => $url,
            	"time" => time(),
            	"type" => $_POST['embed'],
            	"$emsrc" => $_POST['video_id'],
            );

            if (!empty($_POST['caption'])) {
				$text = Generic::cropText($_POST['caption'],$config['caption_len']);
				$re_data['description'] = $text;
			}

			$post_id = $posts->insertPost($re_data);
			
			if (is_numeric($post_id)) {
				$em_data = array();
				$re_data = array(
					'post_id' => $post_id,
				);

				try {
	            	$em_data = $embed->fetchVideo($url);
	            } 
	            catch (Exception $e) {
	            	$data['status']  = 500;
					$data['message'] = lang('unknown_error');
					goto xhr_exit;
	            }

            	if (!empty($em_data['thumbnail']) && Generic::isUrl($em_data['thumbnail'])) {
            		$re_data['file'] = $em_data['thumbnail'];
	            	$posts->insertMedia($re_data);
            	}
	            	

				$posts->setPostId($post_id);
				$post_data = o2array($posts->postData());

				$smarty->setDir('home/templates/home');
				$smarty->assign('post_data',$post_data);
				$data['html']    = $smarty->fetch("includes/post-$emsrc.tpl");	
				$data['status']  = 200;
				$data['message'] = lang('post_published');

				#Notify mentioned users
				$notif->notifyMentionedUsers($_POST['caption'],pid2url($post_id));
			}
			else{
				$data['status']  = 500;
				$data['message'] = lang('unknown_error');
			}
		}

		else{
			$data['status']  = 400;
		}
	}
}

else if($action == 'import-post-gifs' && IS_LOGGED && ($config['import_images'] == 'on')) {

	if (!empty($_POST['gif_url'])){
		$posts = new Posts();
		$notif = new Notifications();

		if (Generic::isUrl($_POST['gif_url'])) {
            $gif_url = urlencode($_POST['gif_url']);

            $re_data = array(
            	"user_id" => $me['user_id'],
            	"time" => time(),
            	"type" => 'gif',
            );

            if (!empty($_POST['caption'])) {
				$text = Generic::cropText($_POST['caption'],$config['caption_len']);
				$re_data['description'] = $text;
			}

			$post_id = $posts->insertPost($re_data);

			if (is_numeric($post_id)) {
				$re_data = array(
					'post_id' => $post_id,
					'file' => $gif_url
				);

				$posts->setPostId($post_id);
				$posts->insertMedia($re_data);

				
				$post_data = o2array($posts->postData());

				$smarty->setDir('home/templates/home');
				$smarty->assign('post_data',$post_data);
				$data['html']    = $smarty->fetch('includes/post-image.tpl');	
				$data['status']  = 200;
				$data['message'] = lang('post_published');

				#Notify mentioned users
				$notif->notifyMentionedUsers($_POST['caption'],pid2url($post_id));

			}
			else{
				$data['status']  = 500;
				$data['message'] = lang('unknown_error');
			}
		}

		else{
			$data['status']  = 400;
			$data['message'] = lang('unknown_error');
		}
	}
}

else if($action == 'delete-post' && IS_LOGGED) {	
	if (!empty($_POST['post_id']) && is_numeric($_POST['post_id'])) {
		$posts   = new Posts();
		$post_id = $_POST['post_id'];
		$data['status']  = 304;
		$data['message'] = lang('unknown_error');

		$posts->setPostId($post_id);
		$posts->setUserById($me['user_id']);

		if ($posts->isPostOwner() || IS_ADMIN) {
			$del = $posts->deletePost();
			if ($del) {
				$data['status']  = 200;
				$data['message'] = 200;
			}
		}
	}
}

else if($action == 'add-comment' && IS_LOGGED) {
	
	if (!empty($_POST['post_id']) && is_numeric($_POST['post_id']) && !empty($_POST['text'])) {
		$posts   = new Posts();
		$notif   = new Notifications();
		$post_id = $_POST['post_id'];
		$text    = Generic::cropText($_POST['text'],$config['comment_len']);
		$text    = Generic::secure($text);
		$data['status'] = 304;

		$posts->setPostId($post_id);
		$posts->setUserById($me['user_id']);

		$re_data = array(
			'text' => $text,
			'time' => time(),
		);


		$insert = $posts->addPostComment($re_data);


		if (!empty($insert)) {
			$comment = $posts->postCommentData($insert);
			if (!empty($comment)) {
				$smarty->setDir('home/templates/home');
				$smarty->assign(array(
					'comment' => o2array($comment),
				));

				$data['html'] = $smarty->fetch('includes/comments.tpl');
				$data['status'] = 200;

				#Notify post owner
				if (!$posts->isPostOwner()) {
					try {
						$posts->setPostId($post_id);
						$post_owner = $posts->getPostOwnerData();

						if (!empty($post_owner)) {
							$notif_conf = $notif->notifSettings($post_owner->user_id,'on_comment');

							if ($notif_conf) {
								$re_data = array(
									'notifier_id' => $me['user_id'],
									'recipient_id' => $post_owner->user_id,
									'type' => 'commented_ur_post',
									'url' => pid2url($post_id),
									'time' => time()
								);

								$notif->notify($re_data);
							}
						}
					} 
					catch (Exception $e) {
						
					}
				}

				#Notify mentioned users
				$notif->notifyMentionedUsers($_POST['text'],pid2url($post_id));
			}
		}
	}
}

else if($action == 'delete-comment' && IS_LOGGED) {
	
	if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
		$posts   = new Posts();
		$id      = $_POST['id'];
		$data['status'] = 304;
		$posts->setUserById($me['user_id']);
		if ($posts->isCommentOwner($id)) {
			$delete = $posts->deletePostComment($id);
			$data['status'] = 200;
		}
	}
}

else if($action == 'explore-posts' && IS_LOGGED) {
	if (!empty($_GET['offset']) && is_numeric($_GET['offset'])) {
		$last_id      = Generic::secure($_GET['offset']);
		$posts        = new Posts();
		$latest_posts = $posts->explorePosts($last_id);
		$context['posts'] = array();
		$smarty->setDir('explore/templates/explore');
		$data['status'] = 404;
		$data['html']   = "";

		if (!empty($latest_posts)) {
			$context['posts'] = o2array($latest_posts);

			foreach ($context['posts'] as $key => $post_data) {
				$smarty->assign('post_data',$post_data);
				$smarty->assign('page','explore');
				$data['html'] .= $smarty->fetch('includes/list.tpl');
			}

			$data['status'] = 200;
		}
	}
}

else if($action == 'explore-tags') {
	if (!empty($_GET['offset']) && is_numeric($_GET['offset']) && !empty($_SESSION['tag_id'])) {
		$last_id = Generic::secure($_GET['offset']);
		$htag    = Generic::secure($_SESSION['tag_id']);
		$posts   = new Posts();


		$latest_posts     = $posts->exploreTags($htag,$last_id);
		$context['posts'] = array();	
		$data['status']   = 404;
		$html             = "";

		if (!empty($latest_posts)) {
			$context['posts'] = o2array($latest_posts);
			$smarty->setDir('explore/templates/explore');
			$smarty->assign('page','tags');

			foreach ($context['posts'] as $key => $post_data) {
				$smarty->assign('post_data',$post_data);
				$html .= $smarty->fetch('includes/list.tpl');
			}

			$data['status'] = 200;
			$data['html']   = $html;
		}
	}
}

else if($action == 'load-user-posts') {
	$vl1 = (!empty($_GET['user_id']) && is_numeric($_GET['user_id']));
	$vl2 = (!empty($_GET['offset']) && is_numeric($_GET['offset']));

	if ($vl1 && $vl2) {
		$last_id = Generic::secure($_GET['offset']);
		$user_id = Generic::secure($_GET['user_id']);
		$posts   = new Posts();

		try {

			$ami_blocked = $user->isBlocked($user_id,true);

			if ($user->profilePrivacy($user_id) && empty($ami_blocked)) {
				$posts->setUserById($user_id);
				$user_posts = $posts->getUserPosts($last_id);
			}
			elseif (empty(IS_LOGGED)) {
				$posts->setUserById($user_id);
				$user_posts = $posts->getUserPosts($last_id);
			}
		} 

		catch (Exception $e) {
			goto xhr_exit;
		}

		$context['posts'] = array();

		$smarty->setDir('profile/templates/profile');
		$data['status'] = 404;
		$data['html']   = "";

		if (!empty($user_posts)) {
			$context['posts'] = o2array($user_posts);
			$smarty->assign('page','posts');
			foreach ($context['posts'] as $key => $post_data) {
				$smarty->assign('post_data',$post_data);
				$data['html'] .= $smarty->fetch('includes/list.tpl');
			}

			$data['status'] = 200;
		}
	}
}

else if($action == 'load-saved-posts'  && IS_LOGGED) {
	$request = (!empty($_GET['offset']) && is_numeric($_GET['offset']));

	if ($request == true) {
		$last_id = Generic::secure($_GET['offset']);
		$posts   = new Posts();
		
		$data['status']   = 404;
		$user_posts       = $posts->getSavedPosts($last_id);
		$html             = "";

		if (!empty($user_posts)) {

			$context['posts'] = o2array($user_posts);
			$smarty->setDir('profile/templates/profile');
			$smarty->assign('page','favourites');
			foreach ($context['posts'] as $key => $post_data) {
				$smarty->assign('post_data',$post_data);
				$html .= $smarty->fetch('includes/list.tpl');
			}

			$data['status'] = 200;
			$data['html']   = $html;
		}
	}
}

else if($action == 'lightbox') {
	if ((!empty($_GET['post_id']) && is_numeric($_GET['post_id']))) {

		$post_id = $_GET['post_id'];
		$page    = (!empty($_GET['page'])) ? $_GET['page'] : false;
		$posts   = new Posts();
		$posts->setPostId($post_id);

		$post_data = $posts->postData();
		$smarty->setDir('main/templates');
		$data['status'] = 404;
		$data['html']   = "";

		

		if (!empty($post_data) && !empty($page)) {
			$posts->setPostId($post_id);
			if ($page == 'tags' && !empty($_SESSION['tag_id'])) {
				$posts->tag_id = $_SESSION['tag_id'];
			}

			$post_data      = o2array($post_data);
			$thumb          = "";
			$is_following   = false;
			$has_next       = $posts->hasNext($page);
			$has_prev       = $posts->hasPrev($page);
			
			if (IS_LOGGED) {
				$is_following = $user->isFollowing($post_data['user_id']);
			}

			if (in_array($post_data['type'], array('youtube','dailymotion','vimeo'))) {
				$mfile = array_shift($post_data['media_set']);
				if (!empty($mfile['file'])) {
					$thumb = $mfile['file'];
				}
			}

			$smarty->assign('thumb',$thumb);
			$smarty->assign('post_data',$post_data);
			$smarty->assign('is_following',$is_following);
			$smarty->assign('prev',$has_prev);
			$smarty->assign('next',$has_next);
			$smarty->assign('page',$page);

			$data['html']   = $smarty->fetch('includes/lightbox.tpl');
			$data['status'] = 200;
		}
	}
}

else if($action == 'like' && IS_LOGGED) {
	if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
		$post_id = $_POST['id'];
		$posts   = new Posts();
		$data    = array('status' => 304);

		$posts->setPostId($post_id);
		$code    = $posts->likePost();

		if ($code == 1 || $code == -1) {
			$data['code'] = $code;
			$data['status'] = 200;
			
			if ($posts->isPostOwner(false) == false && $code == 1) {
				$post_owner = $posts->getPostOwnerData();
				if (!empty($post_owner)) {
					$notif      = new Notifications();
					$notif_conf = $notif->notifSettings($post_owner->user_id,'on_like');
					if ($notif_conf) {
						$re_data = array(
							'notifier_id' => $me['user_id'],
							'recipient_id' => $post_owner->user_id,
							'type' => 'liked_ur_post',
							'url' => pid2url($post_id),
							'time' => time()
						);

						$notif->notify($re_data);
					}
				}
			}
		}
	}
}

else if($action == 'save' && IS_LOGGED) {
	if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
		$post_id = $_POST['id'];
		$posts   = new Posts();
		$data    = array('status' => 304);

		$posts->setPostId($post_id);
		
		$code            = $posts->savePost();
		$code            = ($code == -1) ? 0 : 1;
		$data['code']    = $code;
		$data['status']  = 200;
		$data['message'] = lang('post_added2fav');
		if ($code == 0) {
			$data['message'] = lang('post_rem_from_fav');
		}
	}
}

elseif ($action == 'update' && IS_LOGGED) {
	$vl1 = (!empty($_POST['id']) && is_numeric($_POST['id']));
	$vl2 = (isset($_POST['text']));

	if ($vl1 && $vl2) {
		$post_id  = $_POST['id'];
		$text     = Generic::secure($_POST['text']);
		$posts    = new Posts();
		$notif    = new Notifications();
		$is_owner = $posts->setPostId($post_id)->isPostOwner();
		$data     = array('status' => 304,'message' => lang('unknown_error'));

		if ($is_owner === true) {
			$update = $posts->updatePost(array('description' => $text));
			$data['status']  = 200;
			$data['message'] = lang('changes_saved');

			#Notify mentioned users
			$notif->notifyMentionedUsers($text,pid2url($post_id));
		}
	}
}

elseif ($action == 'report' && IS_LOGGED) {
	if (!empty($_POST['id']) && is_numeric($_POST['id'])) {
		$post_id  = $_POST['id'];
		$posts    = new Posts();
		$report   = $posts->setPostId($post_id)->reportPost();
		$data     = array('status' => 304);

		if ($report == 1) {
			$data['status']  = 200;
			$data['code']    = 1;
			$data['message'] = lang('report_sent');
		}
		else if($report == -1){
			$data['status']  = 200;
			$data['code']    = 0;
			$data['message'] = lang('report_canceled');
		}
	}
}

elseif ($action == 'load-tl-posts' && IS_LOGGED) {
	if (!empty($_GET['offset']) && is_numeric($_GET['offset'])) {
		$last_id  = $_GET['offset'];
		$posts    = new Posts();
		$data     = array('status' => 404);
		$qset     = $posts->getTimelinePosts($last_id);
		$qset     = (!empty($qset)) ? o2array($qset) : 0;
		$html     = "";

		if (len($qset) > 0) {
			$smarty->setDir('home/templates/home');
			foreach ($qset as $post_data) {

				$smarty->assign('post_data',$post_data);

				if ($post_data['type'] == 'image' || $post_data['type'] == 'gif') {
					$html .= $smarty->fetch('includes/post-image.tpl');
				}

				elseif ($post_data['type'] == 'video') {
					$html .= $smarty->fetch('includes/post-video.tpl');
				}

				elseif ($post_data['type'] == 'youtube') {
					$html .= $smarty->fetch('includes/post-youtube.tpl');
				}
				
				elseif ($post_data['type'] == 'vimeo') {
					$html .= $smarty->fetch('includes/post-vimeo.tpl');
				}	
							
				elseif ($post_data['type'] == 'dailymotion') {
					$html .= $smarty->fetch('includes/post-dailymotion.tpl');
				}
			}

			$data['status'] = 200;
			$data['html']   = $html;
		}
	}
}

elseif ($action == 'view-likes' && !empty($_GET['post_id'])) {
	if (is_numeric($_GET['post_id'])) {
		$posts   = new Posts();
		$post_id = $posts::secure($_GET['post_id']);
		$query   = $posts->setPostId($post_id)->getLikedUsers();
		$query   = (!empty($query)) ? o2array($query) : array();
		$data    = array('status' => 404);
		if (!empty($query)) {
			$smarty->setDir('main/templates/modals');
			$smarty->assign('users',$query);

			$data['status']  = 200;
			$data['html']    = $smarty->fetch('view-post-likes.tpl');

		}
		else{
			$data['message'] = lang('unknown_error');
		}
	}
}

else if($action == 'load-tlp-comments' && IS_LOGGED && !empty($_POST['post_id'])) {
	
	if (is_numeric($_POST['post_id']) && !empty($_POST['offset']) && is_numeric($_POST['offset'])) {
		$data    = array(
			'status' => 404
		);

		$posts   = new Posts();
		$posts->comm_limit = 30;
		$post_id = $_POST['post_id'];
		$offset  = $_POST['offset'];
		$query   = $posts->setPostId($post_id)->getPostComments($offset);
		$html    = '';


		if (!empty($query)) {
			$comments = o2array($query);
			$smarty->setDir('home/templates/home');

			foreach ($comments as $comment) {
				$smarty->assign('comment',$comment);
				$html .= $smarty->fetch('includes/comments.tpl');	
			}

			$data['status']  = 200;
			$data['html']    = $html;
		}
		else{
			$data['message'] = lang('no_more_comments');
		}
	}
}
xhr_exit: