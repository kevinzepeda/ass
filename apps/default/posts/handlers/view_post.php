<?php

if (empty($_GET['pid']) || !is_numeric($_GET['pid'])) {
	header("Location: $site_url/404");
	exit;
}

require_once "$root/apps/$theme/$app/setup.php";

$smarty            = new Smarty_Posts($app);
$post_id           = $_GET['pid'];
$posts             = new Posts();
$post_data         = null;
$fetched_data      = $posts->setPostId($post_id)->postData();
$is_owner          = false;
$is_following      = false;
$follow   = $user->followSuggestions();

if (!empty($fetched_data)) {
	$post_data = o2array($fetched_data);
}
else{
	header("Location: $site_url/404");
	exit;
}


if (IS_LOGGED && ($me['user_id'] == $post_data['user_id'])) {
	$is_owner = true;
}

if (IS_LOGGED) {
	$is_following = $user->isFollowing($post_data['user_id']);
}

$smarty->assign(array(
	'title' => lang('posts'),
	'post_data' => $post_data,
	'is_owner' => $is_owner,
	'follow' => o2array($follow),
	'is_following' => $is_following,
	'exjs' => true,
));

$smarty->debugging  = true;
$context['content'] = $smarty->fetch('view-post.tpl');
