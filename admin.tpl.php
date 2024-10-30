<div class="wrap">
<h2>Buzz Comments</h2>
<?php if($cacheCleared) { ?>
<div id='buzzComments-warning' class='updated fade'><p><strong><?php _e('Cache cleared.', 'buzzComments'); ?></strong></p></div>
<?php } ?>
<div style="color:red;margin:8px 0px;">There were a lot of changes to version 0.9.0, if 0.8.7 worked better for you, you can still <a href="http://code.google.com/p/wpbuzzcomments/downloads/detail?name=buzz-comments_0.8.7.zip&can=2&q=">download the old version here.</a></div>
<form method="post" action="options.php"><?php settings_fields( 'buzz_comments_settings' ); ?>
<table class="form-table" style="width: 800px;">
	<tr valign="top">
		<th scope="row"><?php _e('Google Profile E-Mail', 'buzzComments'); ?></th>
		<td><input type="text" name="buzz_comments_email"
			value="<?php echo get_option('buzz_comments_email'); ?>"
			placeholder="example@gmail.com"
			style="width: 400px;" /><br />
		<i><?php _e('e.g.', 'buzzComments'); ?>
		example@gmail.com</i></td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e('Author\'s User ID', 'buzzComments'); ?></th>
		<td>
		<select name="buzz_comments_author_id">
		<?php 
		
		global $wpdb;
		$authors = $wpdb->get_results("SELECT id, display_name from $wpdb->users", ARRAY_A);
		foreach($authors as $author) {
			echo '<option value="'.$author['id'].'"'.(get_option('buzz_comments_author_id') == $author['id'] ? ' SELECTED':'').'>'.$author['display_name'].'</option>';
		}
		?>
		</select><br />
		<i><?php _e('If you write a comment on Buzz, the comment would appear with the avatar and name of the selected Wordpress user.', 'buzzComments'); ?></i></td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e('Custom Buzz Avatar', 'buzzComments'); ?></th>
		<td><input type="text" name="buzz_comments_avatar_image"
			value="<?php echo get_option('buzz_comments_avatar_image'); ?>"
			placeholder="plugins/buzz-comments/img/buzzavatar.png"
			style="width: 400px;" /><br />
		<i><?php echo sprintf(__('The path to an image relative to your wp-content directory. e.g. %s for the file %s.', 'buzzComments'), 'themes/mytheme/img/buzz.png', 'wp-content/themes/mytheme/img/buzz.png'); ?></i></td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e('Add Buzz Note after Comment', 'buzzComments'); ?></th>
		<td><input type="checkbox" name="buzz_comments_buzzNoteAfterContent" 
			<?php if(get_option('buzz_comments_buzzNoteAfterContent')) echo 'CHECKED'; ?> /><br />
			<i><?php echo _e("When you've activated threaded comments, the \"reply\" link will be replaced with text stating that the reply was originally posted on Buzz. If threaded comment mode is deactivated in Wordpress, this note instead gets added to the comment itself. Activate this setting to force this note to always be added to the comment, regardless of threaded mode.", 'buzzComments'); ?></i>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e('Add XFN Link', 'buzzComments'); ?></th>
		<td><input type="checkbox" name="buzz_comments_xfn"
			<?php if(get_option('buzz_comments_xfn')) echo 'CHECKED'; ?> /><br />
			<i><?php _e("If your blog doesn't appear on your Google Buzz \"connected sites\" list, you can activate this option. Then Google associates your blog with your profile the next time your blog is crawled. (further information on the <a href=\"http://code.google.com/intl/de-DE/apis/buzz/docs/connect.html\" target=\"_blank\">\"Connecting Sites to Google Buzz\"</a> page)", 'buzzComments'); ?></i>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e('Debug Mode', 'buzzComments'); ?></th>
		<td><input type="checkbox" name="buzz_comments_debug"
			<?php if(get_option('buzz_comments_debug')) echo 'CHECKED'; ?> /><br />
			<i><?php _e("If you've problems with the plugin, please <a href=\"http://code.google.com/p/wpbuzzcomments/issues/list\">open a ticket</a>. Sometimes the bug can be fixed much faster, with this option enabled. This option does <b>neither</b> display any security critical information, <b>nor</b> will the debug information be displayed for normal users. You should keep this option disabled, when it isn't needed anyhow.", 'buzzComments'); ?></i>
		</td>
	</tr>
</table>

<p class="submit"><input type="submit" class="button-primary"
	value="<?php _e('Save Changes') ?>" /> <?php
	if(get_option('buzz_comments_email')) {
	/*?>
	<a href="options-general.php?page=<?php echo buzzComments::getPluginDir(); ?>/buzzComments_class.php" class="button-primary"><?php echo _e("check Configuration", 'buzzComments'); ?></a>
	<?php*/
	}
	?>
	<a href="options-general.php?page=<?php echo buzzComments::getPluginDir(); ?>/buzzComments_class.php&clearCache=1" class="button-primary"><?php echo _e("clear Cache", 'buzzComments'); ?></a>
	<a href="options-general.php?page=<?php echo buzzComments::getPluginDir(); ?>/buzzComments_class.php&checkConfig=1" class="button-primary"><?php echo _e("check Configuration", 'buzzComments'); ?></a></p>

</form>
</div>
