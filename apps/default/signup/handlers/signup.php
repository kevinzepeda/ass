<?php

if (IS_LOGGED || $config['signup_system'] != 'on') {
	header("Location: $site_url");
	exit;
}

require_once "$root/apps/$theme/$app/setup.php";


$config['header'] = false;
$config['footer'] = false;

$smarty = new Smarty_Signup($app);
$smarty->clearAllCache();
$smarty->assign(array(
	'title' => lang('signup'),
));



$smarty->debugging  = true;
$context['content'] = $smarty->fetch('index.tpl');