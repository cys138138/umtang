<?php
if(preg_match('#^/tenant#', $_SERVER['REQUEST_URI'])){
	require '../apps/tenant/web/index.php';
}elseif(preg_match('#^/api#', $_SERVER['REQUEST_URI'])){
	require '../apps/api/web/index.php';
}else{
	$aOldUrlParam = [
		'#^/weixin/#',
		'#^/experience-lesson/#',
		'#^/activity-vote/#',
		'#^/inside-use/#',
	];
	foreach($aOldUrlParam as $oldUrl){
		if(preg_match($oldUrl, $_SERVER['REQUEST_URI'])){
			require '/data/http/umantang/umantang/apps/login/web/index.php';
			exit;
		}
	}
	require '../apps/login/web/index.php';
}
