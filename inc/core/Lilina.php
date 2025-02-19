<?php

class Lilina {
	/**
	 * Turn register globals off.
	 *
	 * @access private
	 * @since 2.1.0
	 * @return null Will return null if register_globals PHP directive was disabled
	 */
	public static function level_playing_field() {
		Lilina::fix_request_uri();
		if (ini_get('register_globals')) {
			if ( isset($_REQUEST['GLOBALS']) )
				die('GLOBALS overwrite attempt detected');

			// Variables that shouldn't be unset
			$keep = array('GLOBALS', '_GET', '_POST', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');

			$input = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_ENV, $_FILES, isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array());
			foreach ( $input as $k => $v ) {
				if ( !in_array($k, $keep) && isset($GLOBALS[$k]) ) {
					$GLOBALS[$k] = NULL;
					unset($GLOBALS[$k]);
				}
			}
		}

		if (get_magic_quotes_gpc()) {
			list($_GET, $_POST, $_COOKIE, $_REQUEST) = stripslashes_deep(array($_GET, $_POST, $_COOKIE, $_REQUEST));
		}
	}
	/**
	 * Fix the $_SERVER['REQUEST_URI'] variable on IIS.
	 *
	 * IIS does not set the $_SERVER['REQUEST_URI'] variable, so we need to generate it for it
	 * @author WordPress
	 */
	public static function fix_request_uri() {
		// Fix for IIS, which doesn't set REQUEST_URI
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {

			// IIS Mod-Rewrite
			if (isset($_SERVER['HTTP_X_ORIGINAL_URL'])) {
				$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
			}
			// IIS Isapi_Rewrite
			else if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
				$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
			}
			else {
				// If root then simulate that no script-name was specified
				if (empty($_SERVER['PATH_INFO']))
					$_SERVER['REQUEST_URI'] = substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/')) . '/';
				elseif ( $_SERVER['PATH_INFO'] == $_SERVER['SCRIPT_NAME'] )
					// Some IIS + PHP configurations puts the script-name in the path-info (No need to append it twice)
					$_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO'];
				else
					$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'];

				// Append the query string if it exists and isn't null
				if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
					$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
				}
			}
		}
	}

	/**
	 * Check if Lilina is installed and current
	 *
	 * Checks the PHP version and whether Lilina is installed and up-to-date
	 */
	public static function check_installed() {
		require_once(LILINA_PATH . '/inc/core/version.php');
		if(version_compare('5.2', phpversion(), '>'))
			Lilina::nice_die('<p>你的服务器PHP版本低 ' . phpversion() . ' Lilina 最低需要PHP 5.2或更高.</p>');

		if(!Lilina::is_installed()) {
			Lilina::nice_die("<p>你还没有安装配置Lilina，现在 <a href='" . Lilina::guess_baseurl() . "install.php'>开始安装</a>.</p>", '没有配置或安装');
		}
		if(!Lilina::settings_current()) {
			Lilina::nice_die('<p>您的Lilina安装已过期。 请<a href="install.php?action=upgrade">重新设置</a></p>', 'Out of Date');
		}
	}

	/**
	 * Detect whether Lilina is installed
	 *
	 * @return bool Whether Lilina is installed or not
	 */
	public static function is_installed() {
		if (file_exists(LILINA_PATH . '/content/system/config/settings.php')) {
			return true;
		}
		elseif (file_exists(LILINA_PATH . '/conf/settings.php')) {
			// Special case, for an old friend ;-)
			return true;
		}
		return false;
	}

	/**
	 * Detects whether Lilina's settings need to be updated
	 *
	 * {{@internal Missing Long Description}}}
	 * @global Get the current settings version
	 */
	public static function settings_current() {
		// Need to check this again, due to the above function
		if(!file_exists(LILINA_PATH . '/content/system/config/settings.php') && file_exists(LILINA_PATH . '/conf/settings.php')) {
			return false;
		}

		require_once(LILINA_PATH . '/inc/core/conf.php');

		global $settings;
		if( isset($settings['settings_version'])
		  && $settings['settings_version'] == LILINA_SETTINGS_VERSION ) {
			return true;
		}
		return false;
	}

	public static function nice_die($message, $title = 'Whoops!', $class = false) {
		if($title == 'Whoops!' && function_exists('_r')) {
			$title = _r('Whoops!');
		}
		$guessurl = Lilina::guess_baseurl();
	?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
		<title><?php echo $title; ?> &mdash; Lilina</title>
		<link rel="stylesheet" type="text/css" href="<?php echo $guessurl ?>admin/resources/reset.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo $guessurl ?>install.css" />
	</head>
	<body<?php if($class !== false) echo ' class="' . $class . '"'; ?>>
		<div id="content">
			<h1 id="title"><?php echo $title; ?></h1>
			<?php echo $message; ?>
		</div>
		<div id="footer">
			<p>Powered by <a href="http://getlilina.org/">Lilina</a> <span class="version"><?php echo LILINA_CORE_VERSION; ?></span>. Read the <a href="http://codex.getlilina.org/">documentation</a> or get help on the <a href="http://getlilina.org/forums/">forums</a></p>
		</div>
	</body>
</html>
	<?php
		die();
	}

	/**
	 * Guess the base URL
	 *
	 * @return string
	 */
	public static function guess_baseurl() {
		$schema = ( isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ) ? 'https://' : 'http://';
		$guessurl = $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		if(strpos('.', $_SERVER['REQUEST_URI']))
			$guessurl = dirname($guessurl);
		$guessurl = preg_replace('|/admin.*|i', '', $guessurl);
		$guessurl = str_replace('install.php', '', $guessurl);
		$guessurl = str_replace('?' . $_SERVER['QUERY_STRING'], '', $guessurl);
		if($guessurl[strlen($guessurl)-1] != '/') {
			$guessurl .= '/';
		}
		return $guessurl;
	}

	/**
	 * Attempt to load the class before PHP fails with an error.
	 *
	 * This method is called automatically in case you are trying to use a class which hasn't been defined yet.
	 * @param string $class_name Class called by the user
	 */
	public static function autoload($class_name) {
		$file = str_replace('_', '/', $class_name);
		$file = LILINA_INCPATH . '/core/' . $file . '.php';
		if (file_exists($file)) {
			require_once($file);
			return;
		}
	}
}

spl_autoload_register(array('Lilina', 'autoload'));