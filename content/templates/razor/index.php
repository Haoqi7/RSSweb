<?php
/**
 * The Razor template for Lilina
 *
 * A 3-column layout, designed to look like a desktop application
 * @author Ryan McCue <http://ryanmccue.info/>
 */
header('Content-Type: text/html; charset=utf-8');

$user = new User();
$authenticated = !!$user->identify();
?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo get_option('sitename') ?></title>
	<link rel="stylesheet" type="text/css" href="<?php template_directory() ?>/style.css" />
	<link rel="stylesheet" type="text/css" href="<?php template_directory() ?>/resources/fancybox/fancybox.css" />
	<?php
	template_header();
	?>
</head>
<body>
	<div id="header">
		<h1 id="title"><a  href="<?php echo get_option('baseurl') ?>"><?php echo get_option('sitename') ?></a></h1>
		<p id="messagearea"></p>
		<ul id="menu">
			<li id="update"><a href="?method=update" title="更新订阅">更新</a></li>
			<li id="help"><a href="#help" title="如何使用">帮助</a></li>
<?php
if($authenticated) {
?>
			<li id="settings"><a href="<?php echo get_option('baseurl') ?>admin/settings.php" title="配置设置">设置</a></li>
			<li id="logout"><a href="<?php echo get_option('baseurl') ?>admin/login.php?logout=logout&return=index.php" title="退出登录">退出</a></li>
<?php
}
else {
?>
			<li id="login"><a href="<?php echo get_option('baseurl') ?>admin/login.php?return=index.php" title="登陆后可添加删除订阅操作">登录</a></li>
<?php
}
?>
		</ul>
	</div>

	<div id="sidebar">
		<div class="item-list">
			<h2>分类库</h2>
			<ul id="library">
				<li id="library-everything" class="selected"><a href="#library">全部订阅</a></li>
			</ul>
			<h2>我的订阅</h2>
			<ul id="feeds-list">
				<li><a href="#">正在加载订阅....</a></li>
			</ul>
		</div>
		<div class="footer">

			<ul>
<?php
if($authenticated) {
?>
				<li><a id="footer-add" href="<?php echo get_option('baseurl') ?>admin/feeds.php#add">添加</a></li>
				<li><a href="<?php echo get_option('baseurl') ?>admin/feeds.php">管理</a></li>
<?php
}
?>
				<li><span class="resize-handle">||</span></li>
			</ul>
		</div>
	</div>
	
	<div id="switcher" class="footer">
		<ul>
			<li><a href="#" id="switcher-sidebar">边栏</a></li>
			<li><a href="#" id="switcher-items">内容列表</a></li>
		</ul>
	</div>

	<div id="items-list-container">
		<ol id="items-list">
			<li><a href="#">内容加载中...</a></li>
		</ol>
		<div class="footer">
			<ul>
				<li><a id="items-reload" href="<?php echo get_option('baseurl') ?>">刷新</a></li>
				<li><span class="resize-handle">||</span></li>
			</ul>
		</div>
	</div>

	<div id="item-view">
		<div id="item">
			<div id="heading">
				<h2 class="item-title">欢迎来到夜枫的RSS定读器</h2>
				<p class="item-meta"><span class="item-source">来自 <a href="<?php echo get_option('baseurl') ?>#external" class="external"  target="_blank" ><?php echo get_option('sitename') ?></a></span>. <span class="item-date">创建于 <abbr class="relative" title="<?php echo(date("Y-m-d H:i")) ?>"><?php echo(date("Y-m-d H:i")) ?></abbr></p>
			</div>
			<div id="item-content">
				<p>点击左侧内容列表，进行查看。</p>
				<p>也可以点击<a href="?method=update" id="contentboxupdate" title="更新订阅列表">更新</a>来刷新所有的订阅</p>
<?php
if($authenticated) {
?>
			<p>你已经登录，你可以<a href="<?php echo get_option('baseurl') ?>admin/settings.php" title="变更设置">设置</a>相关配置信息。</p>
<?php
}
else {
?>
<p>你还没有登录，<a href="<?php echo get_option('baseurl') ?>admin/login.php?return=index.php" title="登录">登录</a>后可以相关配置信息。</p>
<?php
}
?>
			</div>
		</div>
		<div class="footer">
			<ul>
			</ul>
		</div>
	</div>

	<!--<div id="context-menu"></div>-->

	<?php template_footer(); ?>

	<script type="text/javascript" src="<?php echo get_option('baseurl') ?>inc/js/jquery.js"></script>
	<script type="text/javascript" src="<?php echo get_option('baseurl') ?>inc/js/api.js"></script>
	<script type="text/javascript" src="<?php template_directory() ?>/core.js"></script>
	<script>
		Razor.baseURL = <?php echo json_encode(get_option('baseurl')) ?>;
		Razor.scriptURL = "<?php template_directory() ?>";
	</script>
</body>
</html>