<?php
class buzzComments {
	public static function getPluginDir() {
		return basename(dirname(__FILE__));
	}

	public static function gmtToLocal($date) {
		$date = new DateTime($date);

		$tz = get_option('timezone_string');
		if($tz) {
			$timezone = new DateTimeZone($tz);
			$date->setTimezone($timezone);
		} else {
			$offset = get_option('gmt_offset');
			if(is_numeric($offset)) {
				$date->modify('+'.$offset.' hour');
			}
		}
		return $date->format('Y-m-d H:i:s');
	}
	
	static function compare_date($a, $b) {
		if ($a->comment_date == $b->comment_date) {
			return 0;
		}
		return ($a->comment_date < $b->comment_date) ? -1 : 1;
	}

	function addFilters() {
		add_filter('comments_array', array(&$this,'insertComments'));
		add_filter('edit_comment_link', array(&$this,'hideEditCommentLink'));
		add_filter('comment_reply_link', array(&$this,'hideReplyLink'));
		add_filter('comment_text', array(&$this, 'addBuzzNote'));
		add_filter('get_comments_number', array(&$this, 'updateCommentsNumber'));
		add_filter('comment_class',array(&$this,'addBuzzClass'));
		add_filter('get_avatar', array(&$this,'replaceAvatar'), 10, 5);
	}
	function addActions() {
		add_action('wp_head', array(&$this, 'addXfn'));
		add_action('admin_menu', array(&$this, 'createSettingsEntry'));
		add_action('admin_init', array(&$this,'registerSettings'));
		add_action('init', array(&$this, 'initI18n'));
		add_action('admin_notices', array(&$this, 'adminWarnings'));
	}

	function initI18n() {
		load_plugin_textdomain('buzzComments', false, buzzComments::getPluginDir().'/locale');
	}

	function getProfileId($refreshCache = false) {
		$pid = get_option('buzz_comments_pid', false);
		$email = get_option('buzz_comments_email', false);
		if($pid && !$refreshCache) return $pid;
		elseif($email) {
			require_once( ABSPATH . 'wp-includes/class-snoopy.php');
			$snoopy = new Snoopy();
			
			$result = $snoopy->fetch('http://www.googleapis.com/buzz/v1/people/'.$email.'/@self?alt=json');
			
			if(!$result) return false;

			$result = json_decode($snoopy->results);
			
			if(@$result->data->id) {
				update_option('buzz_comments_pid', $result->data->id);
				return $result->data->id;
			}
		}
		return false;
	}
	
	function insertComments($comments) {
		global $post;

		require_once 'buzz.php';
		$buzz = new buzzCommentsBuzz($post);
		if(!$buzz) return $comments;
		
		$buzz->fetchComments();

		if($buzz->getCommentCount() < 1) return $comments;
		else {
			$settings_author_id = get_option('buzz_comments_author_id');
			$settings_author_uri = get_option('buzz_comments_author_uri');
			$author = get_userdata($settings_author_id);

			$i = 1;
			
			foreach($buzz->getComments() as $buzzComment) {
				$comment = null;

				$comment->comment_ID = "buzz_".$i;
				$comment->post_ID = $post->ID;
				$comment->comment_date = buzzComments::gmtToLocal($buzzComment->published);
				$comment->comment_content = $buzzComment->content;
				$comment->comment_approved = "1";
				$comment->comment_parent = "0";
				$comment->comment_type = "";
				$comment->comment_is_buzz = true;
				$comment->comment_url_to_buzz = $buzz->getLink().'#'.substr(strrchr($buzzComment->id, ':'),1);

				if($buzzComment->actor->profileUrl == $settings_author_uri && $author && $author->ID > 0) {
					$comment->comment_author = $author->display_name;
					$comment->comment_author_url = $author->user_url;
					$comment->comment_author_email = $author->user_email;
					$comment->user_id = $settings_author_id;
				} else {
					$comment->comment_author = $buzzComment->actor->name;
					$comment->comment_author_url = $buzzComment->actor->profileUrl;
					$comment->comment_buzz_avatar = $buzzComment->actor->thumbnailUrl;
				}

				$i++;

				$comments[] = $comment;
			}
			
			usort($comments, array("buzzComments","compare_date"));
			return $comments;
		}
	}

	function hideReplyLink($link) {
		global $comment;

		if(!@$comment->comment_is_buzz) {
			return $link;
		}
		elseif(!get_option('buzz_comments_buzzNoteAfterContent')) {
			return '<a href="'.$comment->comment_url_to_buzz.'" target="_blank">
					'.__('This comment was originally posted on Google Buzz.', 'buzzComments').'
					<img alt="buzz icon" src="'.WP_PLUGIN_URL.'/'.buzzComments::getPluginDir().'/img/buzzicon.png" class="buzzicon" height="12" width="12" style="height:12px;width:12px;border:0px;" />
					</a>';
			}
		else return '';
	}
	
	function addBuzzNote($content) {
		global $comment;
		
		if(@$comment->comment_is_buzz && (get_option('buzz_comments_buzzNoteAfterContent') || !get_option('thread_comments'))) {
			return $content.'<br /><a href="'.$comment->comment_url_to_buzz.'" target="_blank">
					'.__('This comment was originally posted on Google Buzz.', 'buzzComments').'<img alt="buzz icon" src="'.WP_PLUGIN_URL.'/'.buzzComments::getPluginDir().'/img/buzzicon.png" class="buzzicon" height="12" width="12" style="height:12px;width:12px;border:0px;" /></a>';
		}
		return $content;
	}

	function hideEditCommentLink($link) {
	 global $comment;

	 if(!@$comment->comment_is_buzz) return $link;
	}

	function updateCommentsNumber($number) {
		global $post;
				
		require_once 'buzz.php';
		
		$buzz = new buzzCommentsBuzz($post);
		if($buzz) {
			$number += $buzz->getCommentCount();
		}
		return $number;
	}

	function addBuzzClass($classes) {
		global $comment;
			
		if(@$comment->comment_is_buzz) $classes[] = 'from_buzz';

		return $classes;
	}

	function replaceAvatar($avatar, $id_or_email, $size, $default, $alt) {
		global $comment;

		$avatar_path = get_option('buzz_comments_avatar_image');

		if(@$comment->comment_is_buzz && !$comment->user_id) {
			if($comment->comment_buzz_avatar) $avatar_url = $comment->comment_buzz_avatar;
			else {
				if($avatar_path) $avatar_url = WP_CONTENT_URL.'/'.$avatar_path;
				else $avatar_url = WP_PLUGIN_URL.'/'.buzzComments::getPluginDir().'/img/buzzavatar.png';
			}
			
			$avatar = '<img alt="'.$alt.'" src="'.$avatar_url.'" class="avatar avatar-'.$size.' buzz" height="'.$size.'" width="'.$size.'" />';			
		}

		return $avatar;
	}

	function addXfn() {
		$pid = $this->getProfileId();
		if(get_option('buzz_comments_xfn', false) && $pid) echo '<link rel="me" type="text/html" href="http://www.google.com/profiles/'.$pid.'" />';
	}

	function createSettingsEntry() {
		add_submenu_page('options-general.php','Buzz Comments', 'Buzz Comments', 'administrator', __FILE__, array(&$this,'settingsPage'));
	}

	function adminWarnings() {
		if(!is_email(get_option('buzz_comments_email')))	
			echo "<div id='buzzComments-warning' class='updated fade'><p><strong>".__('WP Buzz Comments configuration is not finished.', 'buzzComments')."</strong> ".sprintf(__("You've at least to configure your &quot;Google Profile E-Mail&quot; in the <a href=\"%s\">Buzz Comments configuration</a>.", 'buzzComments'), 'options-general.php?page='.buzzComments::getPluginDir().'/buzzComments_class.php')."</p></div>";
		if($_REQUEST['checkConfig']) {
			echo "<div id='buzzComments-check-result' class='updated fade'><p><strong>".__('WP Buzz Comments configuration checked.', 'buzzComments')."</strong> ";
			if($this->getProfileId(true)) {
				echo sprintf(__("It seems to work fine. Please check if this <a href=\"%s\" target=\"_blank\">links to <strong>your</strong> Google Profile</a>.", 'buzzComments'), 'http://www.google.com/profiles/'.$this->getProfileId());
			} else {
				if(!is_email(get_option('buzz_comments_email', false))) echo sprintf(__("Your configuration is not finished.", 'buzzComments'));
				else echo sprintf(__("Anything doesn't work. Either your configured email address is wrong, or the server is unable to connect to the Google Buzz API.", 'buzzComments'));
			}
			echo "</p></div>";
		}
	}
	
	function registerSettings() {	
		register_setting('buzz_comments_settings', 'buzz_comments_email');		
		register_setting('buzz_comments_settings', 'buzz_comments_author_id', 'intval');
		register_setting('buzz_comments_settings', 'buzz_comments_avatar_image');
		register_setting('buzz_comments_settings', 'buzz_comments_buzzNoteAfterContent');
		register_setting('buzz_comments_settings', 'buzz_comments_xfn');
		register_setting('buzz_comments_settings', 'buzz_comments_debug');
	}

	function settingsPage() {
		if($_REQUEST['clearCache']) {
			buzz_comments_clear_cache();
			$cacheCleared = true;
		}
		if($_REQUEST["updated"]) {
			//update profile id, if email changed
			$this->getProfileId();
		}
		include 'admin.tpl.php';
	}
}
?>