<?php 
if (IS_LOGGED !== true) {
	header("Location: $site_url/welcome");
	exit;
}

require_once "$root/apps/$theme/$app/setup.php";

$smarty = new Smarty_Explore($app);
$user->limit = 100;
$follow = $user->explorePeople();
$users  = (!empty($follow) && is_array($follow)) ? o2array($follow) : array();

$smarty->assign(array(
	'title' => lang('explore_people'),
	'users' => $users,
	'page' => 'explore-people',
	'exjs' => true,
));


$smarty->debugging  = true;
$context['content'] = $smarty->fetch('people.tpl');
