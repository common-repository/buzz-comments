<?php
/*
Plugin Name: Buzz Comments
Plugin URI: http://code.google.com/p/wpbuzzcomments/
Description: If you've connected your Wordpress Blog with Google Buzz, Buzz Comments can add the Buzz replys to your Blog Comments.
Version: 0.9.4
Author: Christoph Stickel
Author URI: http://www.google.com/profiles/mixer2
License: Released under MIT License
*/

define('buzzComments', '0.9.4');

if(get_option("buzz_comments_version", false) < buzzComments) {
	update_option("buzz_comments_version", buzzComments);
	buzz_comments_clear_cache();
}

require_once 'buzzComments_class.php';

$buzzComments = new buzzComments();

$buzzComments->addFilters();
$buzzComments->addActions();

/* deinstallation */
register_uninstall_hook(__FILE__, 'buzz_comments_uninstall');

function buzz_comments_uninstall() {
	global $wpdb;
		
	$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key in ('buzz_comments_buzzez',
																	 'buzz_comments_count',
																	 'buzz_comments_link',
																	 'buzz_comments_timestamp_detail',
																	 'buzz_comments_timestamp_main',
																	 'buzz_comments_url',
																	 'buzz_comment_count',
																	 'buzz_comment_timestamp_main',
																	 'buzz_comment_url',
																	 'buzz_comments_comment_data',
																	 'buzz_comments_data')");
	$wpdb->query("DELETE FROM $wpdb->options WHERE option_name = 'buzz_comments_cachetime_comments'
													OR option_name = 'buzz_comments_feed'
													OR option_name = 'buzz_comments_cachetime_main'
													OR option_name = 'buzz_comments_cachetime'
													OR option_name = 'buzz_comments_author_uri'
													OR option_name = 'buzz_comments_author_id'
													OR option_name = 'buzz_comments_avatar_image'
													OR option_name = 'buzz_comments_buzzNoteAfterContent'
													OR option_name = 'buzz_comments_version'
													OR option_name = 'buzz_comments_email'
													OR option_name = 'buzz_comments_debug'
													");
}
function buzz_comments_clear_cache() {
	global $wpdb;
	//clear cache
	$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key in ('buzz_comments_comment_data', 'buzz_comments_data')");
}
?>