<?php
if (IS_LOGGED !== true) {
	header("Location: $site_url/welcome");
	exit;
}

require_once "$root/apps/$theme/$app/setup.php";

$smarty            = new Smarty_Explore($app);
$context['posts']  = array();
$posts             = new Posts();
$posts->orderBy('post_id','DESC');
$posts->limit      = 40;
$query_posts       = $posts->explorePosts();
$follow            = array();

if (IS_LOGGED) {
	$follow = $user->followSuggestions();
}


if (!empty($query_posts)) {
	$context['posts'] = o2array($query_posts);
}

$follow = (!empty($follow) && is_array($follow)) ? o2array($follow) : array();

$smarty->assign(array(
	'title' => lang('explore_posts'),
	'posts' => $context['posts'],
	'follow' => $follow,
	'page' => 'explore',
	'exjs' => true,
));


$smarty->debugging  = true;
$context['content'] = $smarty->fetch('index.tpl');
