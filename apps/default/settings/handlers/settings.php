<?php

if (IS_LOGGED != true) {
	header("Location: $site_url/welcome");
	exit;
}

if (!empty($_GET['user']) && User::userNameExists($_GET['user'])) {
	if ($user->isAdmin()) {
		$user->setUserByName($_GET['user']);
		$me = $user->userData($user->getUser());
		$me = o2array($me);
	}
}

require_once "$root/apps/$theme/$app/setup.php";
$smarty = new Smarty_Settings($app);
$page   = 'general';
$pages  = array(
	'delete',
	'password',
	'general',
	'profile',
	'privacy',
	'notifications',
	'blocked',
);

if (!empty($_GET['page']) && in_array($_GET['page'], $pages)) {
	$page = $_GET['page'];
}

if ($page == 'delete' && $config['delete_account'] != 'on') {
	$page = 'general';
}

$sm_ctx = array(
	'title' => lang('profile_settings'),
	'page' => $page,
	'me' => $me,
	'context_user' => $context['user']
);


if ($page == 'blocked') {
	$blocked = $user->getBlockedUsers();
	$blocked = (is_array($blocked) == true) ? $blocked : array();
	$sm_ctx['blocked_users'] = o2array($blocked);
}

$smarty->assign($sm_ctx);
$smarty->debugging  = true;
$context['content'] = $smarty->fetch('index.tpl');