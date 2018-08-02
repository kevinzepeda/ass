<?php 
if (empty($_GET['uname'])) {
	header("Location: $site_url");
	exit;
}

require_once "$root/apps/$theme/$app/models.php";
require_once "$root/apps/$theme/$app/setup.php";



$smarty = new Smarty_Profile($app);
$page  = (!empty($_GET['page'])) ? $_GET['page'] : 'posts';

try {
	$user->setUserByName($_GET['uname']);
	$user_data = $user->userData($user->getUser());
	$user_data = o2array($user_data);
} 

catch (Exception $e) {
	header("Location: $site_url");
	exit;
}


$context['posts']  = array();
$posts             = new Posts();
$is_owner          = false;
$is_following      = false;
$is_reported       = false;
$is_blocked        = false;
$user_id           = $user_data['user_id'];

$posts->setUserById($user_id);
$total_posts       = $posts->countPosts();
$user_followers    = $user->countFollowers();
$user_following    = $user->countFollowing();
$profile_privacy   = $user->profilePrivacy($user_id);
$chat_privacy      = $user->chatPrivacy($user_id);


if (IS_LOGGED && ($me['user_id'] == $user_id)) {
	$is_owner = true;
}

if (IS_LOGGED) {
	$is_following = $user->isFollowing($user_id);
	$is_reported  = $user->isUserRepoted($user_id);
	$is_blocked   = $user->isBlocked($user_id);
	$ami_blocked  = $user->isBlocked($user_id,true);

	if ($ami_blocked) {
		header("Location: $site_url");
		exit;
	}
}

$navbar = ($profile_privacy && empty($is_blocked));
$template_context  = array(
	'title' => $user_data['name'],
	'user_data' => $user_data,
	'posts' => $context['posts'],	
	'is_owner' => $is_owner,
	'total_posts' => $total_posts,
	'is_following' => $is_following,
	'user_followers' => $user_followers,
	'user_following' => $user_following,
	'content_page' => 'posts.tpl',
	'page' => $page,
	'p_privacy' => $profile_privacy,
	'chat_privacy' => $chat_privacy,
	'is_reported' => $is_reported,
	'is_blocked' => $is_blocked,
	'navbar' => $navbar,
	'exjs' => true,
);


if ($page == 'following' && $navbar) {
	$user->setUserById($user_id);
	$following_ls = $user->getFollowing(false,50);
	$template_context['content_page'] = "following.tpl";
	$template_context['following_ls'] =	o2array($following_ls);
}

elseif ($page == 'followers' && $navbar) {
	$user->setUserById($user_id);
	$followers_ls = $user->getFollowers(false,50);
	$template_context['content_page'] = "followers.tpl";	
	$template_context['followers_ls'] =	o2array($followers_ls);
}

elseif ($page == 'favourites' && $is_owner) {
	$user->setUserById($me['user_id']);
	$template_context['content_page']   = "favourites.tpl";
	$template_context['favorite_posts'] = array();
	$favorite_posts = $posts->getSavedPosts();

	if (!empty($favorite_posts)) {
		$template_context['favorite_posts'] = o2array($favorite_posts);
	}
}

else{
	if ($navbar || empty(IS_LOGGED)) {
		$posts->setUserById($user_id);
		$posts->limit = 40;
		$user_posts   = $posts->getUserPosts();
		$template_context['posts'] = o2array($user_posts);
	}
	
	$template_context['page'] = 'posts';
}

if ($is_owner === true) {
	$favourites = $posts->countSavedPosts();
	$template_context['favourites'] = $favourites;
}

$smarty->assign($template_context);


$smarty->debugging  = true;
$context['content'] = $smarty->fetch('index.tpl');
