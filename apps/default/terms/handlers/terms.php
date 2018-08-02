<?php 
$tpage = (!empty($_GET['page'])) ? $_GET['page'] : 'about';
$pname = 'about_us';
if ($tpage == 'terms-of-use') {
	$pname = 'terms_of_use';
}

elseif ($tpage == 'privacy-and-policy') {
	$pname = 'privacy_and_policy';
}

$pagecont = $user->getPage($pname);
$ctext    = array(
	'tpage' => $tpage,
	'title' => lang($pname),
	'pagecont' => $pagecont,
	'app_name' => 'terms'
);

$smarty->assign($ctext);
$smarty->setDir('terms/templates/terms');
$smarty->debugging  = true;
$context['content'] = $smarty->fetch('index.tpl');
