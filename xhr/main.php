<?php 

if ($action == 'follow' && IS_LOGGED) {
	if (!empty($_GET['user_id']) && is_numeric($_GET['user_id'])) {
		$follower_id  = $me['user_id'];
		$following_id = Generic::secure($_GET['user_id']);
		$notif        = new Notifications();
		$user->setUserById($follower_id);
		$status       = $user->follow($following_id);
		$data['status'] = 400;
		if ($status === 1) {
			$data['status'] = 200;
			$data['code'] = 1;

			#Notify post owner
			$notif_conf = $notif->notifSettings($following_id,'on_follow');
			if ($notif_conf) {
				$re_data = array(
					'notifier_id' => $me['user_id'],
					'recipient_id' => $following_id,
					'type' => 'followed_u',
					'url' => un2url($me['username']),
					'time' => time()
				);
				
				$notif->notify($re_data);
			}	
		}

		else if($status === -1){
			$data['status'] = 200;
			$data['code'] = 0;
		}

		goto exit_xhr;
	}
}

else if($action == 'get_notif' && IS_LOGGED){
	$notif = new Notifications();
	$data  = array();

	$notif->setUserById($me['user_id']);
	$notif->type    = 'all';
	$notif->limit   = 1000;
	$queryset       = $notif->getNotifications();

	if (!empty($queryset) && is_array($queryset)) {
		$new_notif      = o2array($queryset);

		$smarty->assign('notifications',$new_notif);
		$smarty->setDir('main/templates/header');

		$data['html']   = $smarty->fetch('notifications.tpl');
		$data['status'] = 200;

		foreach ($new_notif as $key => $value) {
			# code...
		}
	}

	else{
		$data['status']  = 304;
		$data['message'] = lang('u_dont_have_notif');
	}
}

elseif ($action == 'update-data' && IS_LOGGED) {
	$data  = array();
	$notif = new Notifications();

	$notif->setUserById($me['user_id']);
	$notif->type    = 'new';
	$new_notif      = $notif->getNotifications();
	$data['notif']  = (is_numeric($new_notif)) ? $new_notif : 0;

	if (!empty($_GET['new_messages'])) {
		$messages     = new Messages();
		$messages->setUserById($me['user_id']);
		$new_messages = $messages->countNewMessages();
		$data['new_messages'] = $new_messages;
	}
}

elseif ($action == 'explore-people' && IS_LOGGED) {
	if (!empty($_GET['offset']) && is_numeric($_GET['offset'])) {
		$user->limit = 100;
		$offset      = $_GET['offset'];
		$users       = $user->explorePeople($offset);
		$data        = array('status' => 404);

		if (!empty($users)) {
			$users = o2array($users);
			$html  = "";
			$smarty->setDir('explore/templates/explore');

			foreach ($users as $udata) {
				$smarty->assign('udata',$udata);
				$html .= $smarty->fetch('includes/row.tpl');
			}

			$data = array(
				'status' => 200,
				'html' => $html
			);
		}
	}
}

elseif ($action == 'report-profile' && IS_LOGGED && !empty($_POST['id'])){
	if (is_numeric($_POST['id']) && !empty($_POST['t'])) {
		$user_id = $_POST['id'];
		$type    = $_POST['t'];
		$data    = array('status' => 304);
		if (in_array($type, range(1, 8)) || $type == -1) {
			$code = $user->reportUser($user_id,$type);
			$code = ($code == -1) ? 0 : 1;
			$data = array(
				'status' => 200,
				'code' => $code,
			);

			if ($code == 0) {
				$data['message'] = lang('report_canceled');
			}

			else if($code == 1){
				$data['message'] = lang('report_sent');
			}
		}
	}
}

elseif ($action == 'block-user' && IS_LOGGED && !empty($_POST['id'])){
	if (is_numeric($_POST['id'])) {
		$user_id = $_POST['id'];
		$data    = array('status' => 304);
		$notif   = new Notifications();
		$code    = $user->blockUser($user_id);
		$code    = ($code == -1) ? 0 : 1;

		if (in_array($code, array(0,1))) {
			$data    = array(
				'status' => 200,
				'code' => $code,
			);

			if ($code == 0) {
				$data['message'] = lang('user_unblocked');
			}

			else if($code == 1){
				$data['message']    = lang('user_blocked');
				$notif->notifier_id = $user_id; 
				$notif->setUserById($me['user_id'])->clearNotifications();
			}
		}
	}
}

elseif ($action == 'search-users' && !empty($_POST['kw'])){
	if (len($_POST['kw']) >= 0) {
		$kword    = $_POST['kw'];
		$data     = array('status' => 304);
		$queryset = $user->seachUsers($kword);
		$html     = "";

		if(!empty($queryset)){
			$queryset = o2array($queryset);
			$smarty->setDir('main/templates/header');

			foreach ($queryset as $udata) {
				$smarty->assign('udata',$udata);
				$html .= $smarty->fetch('search-usrls.tpl');
			}

			$data['status'] = 200;
			$data['html']   = $html;
		}
	}
}

elseif ($action == 'search-posts' && !empty($_POST['kw'])){
	if (len($_POST['kw']) >= 0) {
		$posts    = new Posts();
		$kword    = $_POST['kw'];
		$data     = array('status' => 304);
		$queryset = $posts->searchPosts($kword);
		$html     = "";

		if(!empty($queryset)){
			$queryset = o2array($queryset);
			$smarty->setDir('main/templates/header');

			foreach ($queryset as $htag) {
				$htag['url'] = sprintf('%s/explore/tags/%s',$site_url,$htag['tag']);
				$smarty->assign('htag',$htag);
				$html .= $smarty->fetch('search-posts.tpl');
			}

			$data['status'] = 200;
			$data['html']   = $html;
		}
	}
}

exit_xhr: