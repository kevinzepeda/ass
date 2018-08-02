<?php 

require_once "$root/apps/$theme/$app/setup.php";
$smarty = new Smarty_404($app);
$smarty->assign(array(
	'title' => lang('page_not_found'),
	'page' => '404',
));


$smarty->debugging  = true;
$context['content'] = $smarty->fetch('index.tpl');
