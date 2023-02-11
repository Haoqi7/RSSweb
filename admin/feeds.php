<?php
/**
 * Feeds page
 *
 * @author Ryan McCue <cubegames@gmail.com>
 * @package Lilina
 * @version 1.0
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
require_once('admin.php');
require_once(LILINA_PATH . '/admin/includes/feeds.php');

admin_header(_r('订阅'));

if(!empty($error))
	echo '<div id="alert" class="fade"><p>' . $error . '</p></div>';
if(!empty($message))
	echo '<div id="message"><p>' . $message . '</p></div>';
?>
<h1><?php _e('订阅'); ?></h1>
<h2><?php _e('选择订阅'); ?></h2>
<p><?php _e('双击名称或地址可编辑它'); ?></p>
<table id="feeds_list" class="item-table">
	<thead>
		<tr>
		<th><?php _e('订阅名称'); ?></th>
		<th><?php _e('URL地址'); ?></th>
		<!--<th><?php _e('栏目'); ?></th>-->
		<?php do_action('admin-feeds-infocol-description'); ?>
		<!--<th class="change-col"><?php _e('编辑'); ?></th>-->
		<th class="remove-col"><?php _e('删除'); ?></th>
		<?php do_action('admin-feeds-actioncol-description'); ?>
		</tr>
	</thead>
	<tbody>
		<tr class="nojs"><td colspan="3"><?php _e('必须启动 Javascript 脚本才能操作.') ?></td></tr>
		<tr id="nofeeds"><td colspan="3"><?php _e("你有订阅之后才能操作！") ?></td></tr>
	</tbody>
</table>
<form action="feeds.php" method="get" id="add_form">
	<h2><?php _e('添加订阅'); ?></h2>
	<fieldset id="required">
		<div class="row">
			<label for="add_url"><?php _e('订阅地址(URL)'); ?>:</label>
			<input type="text" name="add_url" id="add_url" />
			<p class="sidenote"><?php _e('例如'); ?>: https://yefengs.com  https://leyaep.com</p>
		</div>
	</fieldset>
	<fieldset id="advanced" class="optional">
		<div class="row">
			<label for="add_name"><?php _e('名称'); ?>:</label>
			<input type="text" name="add_name" id="add_name" />
			<p class="sidenote"><?php _e('若名称为空，将从订阅网站内获取名称'); ?></p>
		</div>
	</fieldset>
	<input type="hidden" name="action" value="add" />
	<p class="buttons"><button type="submit" class="positive"><?php _e('添加'); ?></button></p>
</form>
<?php
admin_footer();
?>
