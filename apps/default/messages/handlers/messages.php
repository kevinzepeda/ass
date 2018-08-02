<?php 
if (IS_LOGGED !== true) {
	header("Location: $site_url/welcome");
	exit;
}

require_once "$root/apps/$theme/$app/models.php";
require_once "$root/apps/$theme/$app/setup.php";

$smarty = new Smarty_Messages($app);
$chat  = array();
$to_id = false;
$chats_history = array();
$messages  = new Messages();
$user_data = array();
$conversation = array();
$c_privacy    = false;

if (!empty($_GET['uname']) && strcmp($_GET['uname'], $me['username']) != 0) {
	$uname = $_GET['uname'];

	try {
		$user->setUserByName($uname);
		$user_data = $user->getUser();
	} 

	catch (Exception $e) {}

	if (!empty($user_data)) {
		$c_privacy   = $user->chatPrivacy($user_data->user_id);
		$is_blocked  = $user->isBlocked($user_data->user_id);
		$ami_blocked = $user->isBlocked($user_data->user_id,true);

		if ($ami_blocked || $is_blocked) {
			header("Location: $site_url");
			exit;
		}

		$messages->setUserById($me['user_id']);
		$messages->limit = 100;
		$to_id     = $user_data->user_id;
		$conv_data = $messages->getMessages($to_id);
		$_SESSION['to_id'] = $user_data->user_id;

		if (!empty($conv_data)) {
			$conversation = o2array($conv_data);
		}
	}
}

$chats = array();
$messages->setUserById($me['user_id']);
$chats_history = $messages->getChats();

if (!empty($chats_history)) {
	$chats = o2array($chats_history);
}

$smarty->assign(array(
	'title' => lang('messages'),
	'user_data' => o2array($user_data),
	'chats_history' => $chats,
	'conversation' => $conversation,
	'c_privacy' => $c_privacy,
));

$smarty->debugging  = true;
$context['content'] = $smarty->fetch('index.tpl');
