<?php
if (IS_LOGGED) {
	header("Location: $site_url");
	exit;
}

if (empty($_GET['code'])) {
	header("Location: $site_url/404");
	exit;
}

$user_id = User::validateCode($_GET['code']);

if (!$user_id) {
	header("Location: $site_url/404");
	exit;
}



require_once "$root/apps/$theme/$app/setup.php";


$config['header'] = false;
$config['footer'] = false;

$smarty = new Smarty_Welcome($app);
$smarty->clearAllCache();
$smarty->assign(array(
	'title' => lang('reset'),
	'code' => Generic::secure($_GET['code'])
));



$smarty->debugging  = false;
$context['content'] = $smarty->fetch('reset.tpl');