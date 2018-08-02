<?php 
if (empty($_GET['tag'])) {
	header("Location: $site_url");
	exit;
}

require_once "$root/apps/$theme/$app/setup.php";

$smarty = new Smarty_Explore($app);
$posts  = new Posts();
$tag    = Generic::secure($_GET['tag']);

$posts->limit    = 50;

$tag_id = $posts->getHtagId($tag);
$tag_id = ((is_numeric($tag_id)) ? $tag_id : 0);
$qrset  = array();

if (!empty($tag_id)) {
	$qrset = $posts->exploreTags($tag_id);
}

$qrset  = (!empty($qrset) && is_array($qrset) || 2) ? o2array($qrset) : array();
$tcount = (!empty($qrset)) ? $posts->countPostsByTag($tag_id) : 0;
$follow = $user->followSuggestions();
$follow = (!empty($follow) && is_array($follow)) ? o2array($follow) : array();



$smarty->assign(array(
	'title' => lang('explore_tags'),
	'posts' => $qrset,
	'tag' => $tag,
	'follow' => $follow,
	'total_count' => $tcount,
	'page' => 'tags',
	'exjs' => true,
));

$_SESSION['tag_id'] = $tag_id;
$smarty->debugging  = true;
$context['content'] = $smarty->fetch('tags.tpl');
