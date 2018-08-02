<?php
require_once('./sys/init.php');

$root     = __DIR__;
define('ROOT', $root);
$app      = (!empty($_GET['app'])) ? $_GET['app'] : 'home';
$apph     = (!empty($_GET['apph'])) ? $_GET['apph'] : 'home';
$app_cont = "apps/$theme/$app/handlers/$apph.php";

if (file_exists($app_cont)) {
	require_once($app_cont);
}

if( ISSET( $context['user'] ) ){
	if($context['user']["active"] == 0){
		$app      = 'notactive';
		$apph     = 'notactive';
		$app_cont = "apps/$theme/notactive/handlers/notactive.php";
		require_once($app_cont);
	}
}

if (empty($context['content'])) {
	header("Location: $site_url/404");
	exit;
}

echo $context['content'];
$db->disconnect();
unset($context);