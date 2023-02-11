<?php
/**
 * Plugin management page
 *
 * @package Lilina
 * @subpackage Admin
 */

/** */
require_once('admin.php');
require_once(LILINA_PATH . '/admin/includes/settings.php');

if (isset($_GET['_nonce'])) {
	// Check the nonces. This could be written better.
	if (isset($_GET['activate'])) {
		$type = 'activate';
		$plugin = $_GET['activate'];
	}
	elseif (isset($_GET['deactivate'])) {
		$type = 'deactivate';
		$plugin = $_GET['deactivate'];
	}
	else {
		Lilina::nice_die(_r('Incorrect action specified.'));
	}

	if (!check_nonce('plugins.' . $plugin, $_GET['_nonce'])) {
		Lilina::nice_die(_r('Nonces do not match.'));
	}

	if ($type === 'activate') {
		Lilina_Plugins::activate($plugin);
		
		header('HTTP/1.1 302 Found', true, 302);
		header('Location: ' . get_option('baseurl') . 'admin/plugins.php?activated=1');
		die();
	}
	else {
		Lilina_Plugins::deactivate($plugin);
		
		header('HTTP/1.1 302 Found', true, 302);
		header('Location: ' . get_option('baseurl') . 'admin/plugins.php?deactivated=1');
		die();
	}
}

if (isset($_GET['settings'])) {
	$succeeded = apply_filters('settings.plugins.' . $_GET['settings'] . '.settingspage', false);
	if (!$succeeded) {
		header('HTTP/1.1 500 Internal Server Error', true, 500);
		Lilina::nice_die(_r('Plugin page not found'));
	}
	die();
}

admin_header(_r('插件'));

if (!empty($_GET['activated'])) {
	echo '<div class="message"><p>' . _r('Plugin <strong>已经激活</strong>.') . '</p></div>';
}

if(!empty($_GET['deactivated'])) {
	echo '<div class="message"><p>' . _r('Plugin <strong>已经停用</strong>.') . '</p></div>';
}

?>

<h1><?php _e('Plugin Management'); ?></h1>

<form action="settings.php" method="post">
	<fieldset id="plugins">
		<table class="item-table">
			<thead>
				<tr>
					<th scope="col"><?php _e('插件') ?></th>
					<th scope="col"><?php _e('描述') ?></th>
				</tr>
			</thead>
			<tbody>
<?php

foreach (Lilina_Plugins::get_available() as $plugin):
	$activated = Lilina_Plugins::is_activated($plugin->id);
	$new_version = Lilina_Updater_Plugins::check($plugin->id);

	$class = 'plugin-row';
	if ($new_version !== false) {
		$class .= ' needs-update';
	}
	$nonce = generate_nonce('plugins.' . $plugin->id);
	if ($activated) {
		$class .= ' activated';
		$actions = '<a href="plugins.php?deactivate=' . $plugin->id . '&amp;_nonce=' . $nonce . '">' . _r('停用') . '</a>';
	}
	else {
		$class .= ' deactivated';
		$actions = '<a href="plugins.php?activate=' . $plugin->id . '&amp;_nonce=' . $nonce . '">' . _r('激活') . '</a>';
	}

	$usesettings = apply_filters('settings.plugins.showsettingslink', false, $plugin);

	if ($activated && $usesettings === true) {
		$link = apply_filters('settings.plugins.settingslink', 'plugins.php?settings=' . $plugin->id, $plugin);
		$actions .= sprintf(' | <a href="%s">%s</a>', $link, _r('设置'));
	}

	$info = array();
	$info[] = sprintf(_r('版本 %s'), $plugin->version);

	if (!empty($plugin->author)) {
		if ($plugin->author_uri) {
			$info[] = apply_filters('settings.plugins.author', sprintf(
				_r('作者 %s</a>'),
				'<a href="' . $plugin->author_uri . '">' . $plugin->author . '</a>'
			), $plugin);
		}
		else {
			$info[] = apply_filters('settings.plugins.author', sprintf(_r('作者 %s'), $plugin->author), $plugin);
		}
	}

	if (!empty($plugin->uri)) {
		$info[] = apply_filters('settings.plugins.link', sprintf(_r('<a href="%s">访问作者</a>'), $plugin->uri), $plugin);
	}

	$info = apply_filters('settings.plugins.info', $info, $plugin);
?>
				<tr class="<?php echo $class ?>">
					<td class="plugin-name"><span class="name"><?php echo $plugin->name ?></span><p class="plugin-actions"><?php echo $actions ?></p></td>
					<td class="plugin-desc"><?php echo $plugin->description ?><p><?php echo implode(' | ', $info) ?></p></td>
				</tr>
<?php

	if ($new_version !== false) {
?>
				<tr class="update-row">
					<td colspan="2"><p><?php printf(
						_r('An update for %1$s v%2$s is available. <a href="%3$s" class="update-link">Update to v%4$s</a>'),
						$plugin->name,
						$plugin->version,
						'plugins-add.php?action=update&amp;plugin=' . urlencode($plugin->id),
						$new_version->version
					); ?></p></td>
				</tr>
<?php
	}
endforeach;
?>
			</tbody>
		</table>
	</fieldset>
</form>
<?php
admin_footer();
?>