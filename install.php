<?php
/**
 * Installation of Lilina
 *
 * Installation functions including
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/** */
define('LILINA_PATH', dirname(__FILE__));
define('LILINA_INCPATH', LILINA_PATH . '/inc');
define('LILINA_CONTENT_DIR', LILINA_PATH . '/content');
header('Content-Type: text/html; charset=UTF-8');

require_once(LILINA_INCPATH . '/core/Lilina.php');
require_once(LILINA_INCPATH . '/core/misc-functions.php');
require_once(LILINA_INCPATH . '/core/install-functions.php');
require_once(LILINA_INCPATH . '/core/file-functions.php');
require_once(LILINA_INCPATH . '/core/version.php');
Lilina::level_playing_field();

if(version_compare('5.2', phpversion(), '>'))
	Lilina::nice_die('<p>你的服务器PHP版本为' . phpversion() . '但是Lilina需要PHP 5.2 或更高</p>');

//Make sure Lilina's not installed
if (Lilina::is_installed()) {
	if (!Lilina::settings_current()) {
		if(isset($_GET['action']) && $_GET['action'] == 'upgrade') {
			upgrade();
		}
		else {
			Lilina::nice_die('<p>您的Lilina安装已过期。 请<a href="install.php?action=upgrade">重新设置</a></p>');
		}
	}
	else {
		Lilina::nice_die('<p>Lilina已经安装. <a href="index.php">返回首页</a></p>');
	}
}

global $installer;
$installer = new Installer();

/**#@+
 * Dummy function, for use before Lilina is installed.
 */
if (!function_exists('apply_filters')) {
	function apply_filters($name, $value) {
		return $value;
	}
}
/**#@-*/

/**
 * upgrade() - Run upgrade processes on supplied data
 *
 * {{@internal Missing Long Description}}}
 */
function upgrade() {
	global $lilina;
	require_once(LILINA_INCPATH . '/core/feed-functions.php');
	require_once(LILINA_INCPATH . '/core/version.php');
	require_once(LILINA_INCPATH . '/core/misc-functions.php');

	/** Rename possible old files */
	if(@file_exists(LILINA_PATH . '/.myfeeds.data'))
		rename(LILINA_PATH . '/.myfeeds.data', LILINA_PATH . '/content/system/config/feeds.data');
	elseif(@file_exists(LILINA_PATH . '/conf/.myfeeds.data'))
		rename(LILINA_PATH . '/conf/.myfeeds.data', LILINA_PATH . '/content/system/config/feeds.data');
	elseif(@file_exists(LILINA_PATH . '/conf/.feeds.data'))
		rename(LILINA_PATH . '/conf/.feeds.data', LILINA_PATH . '/content/system/config/feeds.data');
	elseif(@file_exists(LILINA_PATH . '/conf/feeds.data'))
		rename(LILINA_PATH . '/conf/feeds.data', LILINA_PATH . '/content/system/config/feeds.data');

	if(@file_exists(LILINA_PATH . '/conf/settings.php'))
		rename(LILINA_PATH . '/conf/settings.php', LILINA_PATH . '/content/system/config/settings.php');

	require_once(LILINA_PATH . '/inc/core/conf.php');

	/*
	if(@file_exists(LILINA_PATH . '/content/system/config/feeds.data')) {
		$feeds = file_get_contents(LILINA_PATH . '/content/system/config/feeds.data');
		$feeds = unserialize( base64_decode($feeds) );

		/** Are we pre-versioned? * /
		if(!isset($feeds['version'])){

			/** Is this 0.7? * /
			if(!is_array($feeds['feeds'][0])) {
				/** 1 dimensional array, each value is a feed URL string * /
				foreach($feeds['feeds'] as $new_feed) {
					Feeds::get_instance()->add($new_feed);
				}
			}

			/** We must be in between 0.7 and r147, when we started versioning * /
			elseif(!isset($feeds['feeds'][0]['url'])) {
				foreach($feeds['feeds'] as $new_feed) {
					Feeds::get_instance()->add($new_feed['feed'], $new_feed['name']);
				}
			}

			/** The feeds are up to date, but we don't have a version * /
			else {
			}

		}
		elseif($feeds['version'] != $lilina['feed-storage']['version']) {
			/** Note the lack of breaks here, this means the cases cascade * /
			switch(true) {
				case $feeds['version'] < 147:
					/** We had a b0rked upgrader, so we need to make sure everything is okay * /
					foreach($feeds['feeds'] as $this_feed) {
						
					}
				case $feeds['version'] < 237:
					/** We moved stuff around this version, but we've handled that above. * /
			}
		}
		else {
		}
		global $data;
		$data = $feeds;
		$data['version'] = $lilina['feed-storage']['version'];
		save_feeds();
	} //end file_exists()
	*/


	/** Just in case... */
	unset($BASEURL);
	require(LILINA_PATH . '/content/system/config/settings.php');

	if(isset($BASEURL) && !empty($BASEURL)) {
		// 0.7 or below
		$raw_php		= "<?php
// What you want to call your Lilina installation
\$settings['sitename'] = '$SITETITLE';\n
// The URL to your server
\$settings['baseurl'] = '$BASEURL';\n
// Username and password to log into the administration panel\n// 'pass' is MD5ed
\$settings['auth'] = array(
							'user' => '$USERNAME',
							'pass' => '" . md5($PASSWORD) . "'
							);\n
// Version of these settings; don't change this
\$settings['settings_version'] = " . $lilina['settings-storage']['version'] . ";\n?>";

		if(!($settings_file = @fopen(LILINA_PATH . '/content/system/config/settings.php', 'w+')) || !is_resource($settings_file)) {
			Lilina::nice_die('<p>配置失败: 保存 content/system/config/settings.php 失败，请检查是否有读写权限</p>', '安装失败');
		}
		fputs($settings_file, $raw_php);
		fclose($settings_file);
	}
	elseif(!isset($settings['settings_version'])) {
		// Between 0.7 and r147
		// Fine to just use existing settings
		$raw_php		= file_get_contents(LILINA_PATH . '/content/system/config/settings.php');
		$raw_php		= str_replace('?>', "// Version of these settings; don't change this\n" .
							"\$settings['settings_version'] = " . $lilina['settings-storage']['version'] . ";\n?>", $raw_php);

		if(!($settings_file = @fopen(LILINA_PATH . '/conf/settings.php', 'w+')) || !is_resource($settings_file)) {
			Lilina::nice_die('<p>配置失败: 保存 content/system/config/settings.php 失败，请检查是否有读写权限</p>', '安装失败');
		}
		fputs($settings_file, $raw_php);
		fclose($settings_file);
	}
	elseif($settings['settings_version'] != $lilina['settings-storage']['version']) {
		/** Note the lack of breaks here, this means the cases cascade */
		switch(true) {
			case $settings['settings_version'] < 237:
				/** We moved stuff around this version, but we've handled that above. */
			case $settings['settings_version'] < 297:
				new_options_297();
			case $settings['settings_version'] < 302:
				new_options_302();
			case $settings['settings_version'] < 339:
				new_options_339();
			case $settings['settings_version'] < 368:
				new_options_368();
			case $settings['settings_version'] < 480:
				new_options_368();
		}

		$raw_php		= file_get_contents(LILINA_PATH . '/content/system/config/settings.php');
		$raw_php		= str_replace(
			"\$settings['settings_version'] = " . $settings['settings_version'] . ";",
			"\$settings['settings_version'] = " . $lilina['settings-storage']['version'] . ";",
			$raw_php);

		if(!($settings_file = @fopen(LILINA_PATH . '/content/system/config/settings.php', 'w+')) || !is_resource($settings_file)) {
			Lilina::nice_die('<p>配置失败: 保存 content/system/config/settings.php 失败，请检查是否有读写权限</p>', '安装失败');
		}
		fputs($settings_file, $raw_php);
		fclose($settings_file);

		if(!save_options()) {
			Lilina::nice_die('<p>配置失败: 保存 content/system/config/options.data 失败，请检查是否有读写权限</p>', '安装失败');
		}
	}

	$string = '';
	if(count(MessageHandler::get()) === 0) {
		Lilina::nice_die('<p>已成功安装, <a href="index.php">返回首页！</a></p>', '安装成功');
		return;
	}
	else
		$string .= '<p>安装 <strong>失败</strong>！ 错误原因是:</p><ul><li>';

	Lilina::nice_die($string . implode('</li><li>', MessageHandler::get()) . '</li></ul>', '安装失败');
}

function default_options() {
	Options::lazy_update('offset', 0);
	Options::lazy_update('encoding', 'utf-8');
	Options::lazy_update('template', 'razor');
	Options::lazy_update('locale', 'en');
	Options::lazy_update('timezone', 'UTC');
	Options::lazy_update('sitename', 'Lilina');
	Options::lazy_update('feeds_version', LILINA_FEEDSTORAGE_VERSION);
}
function new_options_297() {
	Options::lazy_update('offset', 0);
	Options::lazy_update('encoding', 'utf-8');
	if (!Options::get('template', false))
		Options::lazy_update('template', 'razor');
	if (!Options::get('locale', false))
		Options::lazy_update('locale', 'en');
}
function new_options_302() {
	Options::lazy_update('timezone', 'UTC');
}
/**
 * It appears we missed this at some point
 */
function new_options_339() {
	if (!Options::get('encoding', false))
		Options::lazy_update('encoding', 'utf-8');
}
function new_options_368() {
	global $settings;
	if (!Options::get('sitename', false)) {
		if(!empty($settings['sitename']))
			Options::lazy_update('sitename', $settings['sitename']);
		else
			Options::lazy_update('sitename', 'Lilina');
	}
}

function create_settings_file() {
	
}

//Initialize variables
if(!empty($_POST['page'])) {
	$page				= htmlspecialchars($_POST['page']);
}
elseif(!empty($_GET['page'])) {
	$page				= htmlspecialchars($_GET['page']);
}
else {
	$page				= false;
}
$from					= (isset($_POST['from'])) ? htmlspecialchars($_POST['from']) : false;
$sitename				= isset($_POST['sitename']) ? $_POST['sitename'] : false;
$username				= isset($_POST['username']) ? $_POST['username'] : false;
$password				= isset($_POST['password']) ? $_POST['password'] : false;
$error					= ((!$sitename || !$username || !$password) && $page && $page != 1) ? true : false;

if($page === "1" && !isset($_REQUEST['skip']))
	$installer->compatibility_test();

switch($page) {
	case 1:
		Installer::header();
?>
<h1 id="title">安装及配置</h1>
<p>安装时需要配置一些基本信息。</p>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
	<fieldset id="general">
		<h2>基本设置</h2>
		<div class="row">
			<label for="sitename">站点名称</label>
			<input type="text" value="<?php echo (!$sitename) ? 'Lilina' : $sitename;?>" name="sitename" id="sitename" class="input" size="40" />
			<p class="sidenote">网站的名称如“我的订阅器”，后期可修改。</p>
		</div>
	</fieldset>
	<fieldset id="security">
		<h2>安全设置</h2>
		<div class="row">
			<label for="username">管理员用户名</label>
			<input type="text" value="<?php echo (!$username) ? 'admin' : $username;?>" name="username" id="username" class="input" size="40" />
			<p class="sidenote">“admin”这个名字最好别选，懒得输入那就没办法了~</p>
		</div>
		<div class="row">
			<label for="password">管理员密码</label>
			<input type="text" value="<?php echo (!$password) ? generate_password() : $password;?>" name="password" id="password" class="input" size="40" />
			<p class="sidenote">这个很重要，忘记密码可能要重装了（后期修改密码很复杂很复杂）</p>
		</div>
	</fieldset>
	<input type="hidden" value="2" name="page" id="page" />
	<input type="submit" value="下一步" class="submit" />
</form>
<?php
		Installer::footer();
		break;
	case 2:
		$installer->install($sitename, $username, $password);
		break;
	default:
		Installer::header();
?>
<h1 id="title">安装</h1>
<p>欢迎来到Lilina的安装界面. 在准备安装之前，请确认目录<code>content/system/</code>有<a href="readme.html#permissions">读写</a>权限。</p>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
<input type="hidden" name="page" value="1" />
<input type="submit" value="安装" class="submit" />
</form>
<?php
		Installer::footer();
		break;
}