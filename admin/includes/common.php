<?php
/**
 * Common administration helpers
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */


function admin_header($title, $parent_file = false) {
	$self = preg_replace('|^.*/admin/|i', '', $_SERVER['PHP_SELF']);
	$self = preg_replace('|^.*/plugins/|i', '', $self);

	header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $title ?> &mdash; <?php echo get_option('sitename'); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="<?php echo get_option('baseurl'); ?>admin/resources/jquery-ui.css" media="screen"/>
<link rel="stylesheet" type="text/css" href="<?php echo get_option('baseurl'); ?>admin/resources/core.css" media="screen"/>
<link rel="stylesheet" type="text/css" href="<?php echo get_option('baseurl'); ?>admin/resources/full.css" media="screen"/>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>inc/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>inc/js/json2.js"></script>
<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>inc/js/jquery.ui.js"></script>
<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>inc/js/jquery.scrollTo.js"></script>
<script type="text/javascript" src="<?php echo get_option('baseurl'); ?>admin/admin.js"></script>
<script type="text/javascript">
<?php
	$localisations = array(
		'nofeedurl'      => _r('No feed URL supplied'),
		'nofeedid'       => _r('No feed ID supplied'),
		'failedtoparse'  => _r('Failed to parse response: '),
		'ays'            => _r('你确定?'),
		'whoops'         => _r('Whoops!'),
		'ok'             => _r('确认'),
		'cancel'         => _r('取消'),
		'delete'         => _r('删除'),
		'somethingwrong' => _r('Something Went Wrong!'),
		'error'          => _r('Error message:'),
		'weirderror'     => _r('If you think you shouldn\'t have received this error then <a href="http://code.google.com/p/lilina/issues">report a bug</a> quoting that message and how it happened.'),
		'edithint'       => _r('双击可编辑'),
		'delete'         => _r('删除'),
		'showadvanced'   => _r('高级选项'),
		'dragme'         => _r('拖动至浏览器书签栏'),
		'log'            => _r('日志'),
	);
?>
	admin.localisations = <?php echo json_encode($localisations) ?>;
</script>
</head>
<body id="admin-<?php echo basename($self, '.php'); ?>" class="admin-page">
<div id="header">
	<p id="sitetitle"><a href="<?php echo get_option('baseurl'); ?>"><?php echo get_option('sitename'); ?></a></p>
	<ul id="navigation">
<?php
	$navigation = array(
		array(_r('仪表盘'), 'index.php', ''),
		array(_r('订阅'), 'feeds.php', 'feeds'),
		array(_r('插件'), 'plugins.php', 'plugins'),
		array(_r('设置'), 'settings.php', 'settings'),
	);
	$navigation = apply_filters('navigation', $navigation);

	$subnavigation = apply_filters('subnavigation', array(
		'index.php' => array(
			array(_r('首页'), 'index.php', 'home'),
		),
		'feeds.php' => array(
			array(_r('添加/管理'), 'feeds.php', 'feeds'),
			array(_r('导入'), 'feed-import.php', 'feeds'),
		),
		'plugins.php' => array(
			array(_r('管理'), 'plugins.php', 'plugins'),
			//array(_r('Search & Install'), 'plugins-add.php', 'plugins'),
		),
		'settings.php' => array(
			array(_r('普通设置'), 'settings.php', 'settings'),
		),
	), $navigation, $self);

	foreach($navigation as $nav_item) {
		$class = 'item';
		if((strcmp($self, $nav_item[1]) == 0) || ($parent_file && ($nav_item[1] == $parent_file))) {
			$class .= ' current';
		}

		if(isset($subnavigation[$nav_item[1]]) && count($subnavigation[$nav_item[1]]) > 1)
			$class .= ' has-submenu';

		echo "<li class='$class'><a href='{$nav_item[1]}'>{$nav_item[0]}</a>";
		
		if(!isset($subnavigation[$nav_item[1]]) || count($subnavigation[$nav_item[1]]) < 2) {
			echo "</li>";
			continue;
		}
		
		echo '<ul class="submenu">';
		foreach($subnavigation[$nav_item[1]] as $subnav_item) {
			echo '<li' . ((strcmp($self, $subnav_item[1]) == 0) ? ' class="current"' : '') . "><a href='{$subnav_item[1]}'>{$subnav_item[0]}</a></li>";
		}
		echo '</ul></li>';
		
	}
?>
	</ul>
	<ul id="utilities">
		<li><a href="page_item_logout"><a href="login.php?logout" title="<?php _e('退出'); ?>"><?php _e('退出'); ?></a></a></li>
		<?php do_action('admin_utilities_items'); ?>
	</ul>
</div>
<div id="main">
<?php
	if($result = implode('</p><p>', MessageHandler::get())) {
		echo '<div id="alert" class="fade"><p>' . $result . '</p></div>';
	}
	do_action('admin_header');
	do_action("admin_header-$self");
	do_action('send_headers');
}

function admin_footer() {
?>
</div>
<p id="footer"><?php
_e('Powered by <a href="http://getlilina.org/">Lilina</a>');
do_action('admin_footer'); ?> | <a href="http://getlilina.org/docs/start"><?php _e('文档(打不开)') ?></a> | <a href="http://getlilina.org/forums/" title="<?php _e('社区') ?>"><?php _e('支持(打不开)') ?></a></p>
</body>
</html>
<?php
}
?>