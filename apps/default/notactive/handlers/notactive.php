<?php 

require_once "$root/apps/$theme/$app/setup.php";
$smarty = new Smarty_notactive($app);
$smarty->assign(array(
	'title' => lang('account_not_activated'),
	'page' => 'notactive'
));


$smarty->debugging  = true;
$context['content'] = $smarty->fetch('index.tpl');
