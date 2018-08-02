<?php
if (IS_LOGGED) {
	header("Location: $site_url");
	exit;
}

require_once "$root/apps/$theme/$app/setup.php";


$config['header'] = false;
$config['footer'] = false;

$smarty = new Smarty_Welcome($app);
$smarty->clearAllCache();
$smarty->assign(array(
	'title' => lang('reset'),
));



$smarty->debugging  = false;
$context['content'] = $smarty->fetch('forgot.tpl');