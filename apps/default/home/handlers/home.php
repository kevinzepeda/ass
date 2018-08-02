<?php 
if (IS_LOGGED !== true) {
	header("Location: $site_url/welcome");
	exit;
}

require_once "$root/apps/$theme/$app/models.php";
require_once "$root/apps/$theme/$app/setup.php";

$smarty            = new Smarty_Home($app);
$context['posts']  = array();
$posts             = new Posts();
$story             = new Story();
$posts->limit      = 3;
$posts->comm_limit = 4;
$tlposts           = $posts->setUserById($me['user_id'])->getTimelinePosts();

$follow   = $user->followSuggestions();
$stories  = $story->setUserById($me['user_id'])->getStories();
$stories  = o2array($stories);
$trending = $posts->getFeaturedPosts();
$post_sys = array(
	($config['upload_images'] == 'on'),
	($config['upload_videos'] == 'on'),
	($config['import_videos'] == 'on'),
	($config['import_images'] == 'on'),
	($config['story_system'] == 'on'),
);



if (!empty($tlposts)) {
	$context['posts'] = o2array($tlposts);
}

$trending = (!empty($trending)) ? o2array($trending) : array();

$smarty->assign(array(
	'title' => lang('home_page'),
	'posts' => $context['posts'],
	'follow' => o2array($follow),
	'stories' => $stories,
	'trending' => $trending,
	'post_sys' => (in_array(true, $post_sys)),
	'exjs' => true,
));


$smarty->debugging  = true;
$context['content'] = $smarty->fetch('index.tpl');
