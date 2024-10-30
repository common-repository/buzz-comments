<?php
class buzzCommentsBuzz {
	private $commentCount = 0;
	private $url = false;
	private $link = false;
	private $cacheTimestamp = false;
	private $comments = array();
	
	function setCommentCount($c) {
		$this->commentCount = $c;
	}
	function getCommentCount() {
		return $this->commentCount;
	}
	function setUrl($u) {
		$this->url = $u;
	}
	function getUrl() {
		return $this->url;
	}
	function setLink($l) {
		$this->link = $l;
	}
	function getLink() {
		return $this->link;
	}
	function setCacheTimestamp($t) {
		$this->cacheTimestamp = $t;
	}
	function getCacheTimestamp() {
		return $this->cacheTimestamp;
	}
	function setComments($c) {
		if(is_array($c)) {
			$this->comments = $c;
			$this->setCommentCount(count($c));
			$this->updateCache();
		}
	}
	function getComments() {
		return $this->comments;
	}
	
	
	function __construct($post = false) {
		if(!$post) return false;

		$buzzData = unserialize(@current(get_post_custom_values('buzz_comments_data', $post->ID)));
		
		$this->setCommentCount($buzzData['commentCount']);
		$this->setUrl($buzzData['url']);
		$this->setLink($buzzData['link']);
		$this->setCacheTimestamp($buzzData['cacheTimestamp']);

		$maxCacheTime = $this->filterCacheTime(60*rand(60,600));
		
		$this->debug('Url: '.$this->getUrl().', Timestamp: '.$this->getCacheTimestamp().' <= '.(time()-$maxCacheTime));
		
		if((!$this->getUrl() && $this->getCacheTimestamp() <= (time()-$maxCacheTime)) || (get_option('buzz_comments_debug') && $_REQUEST['buzzCommentsDisableCache'])) {
			global $buzzComments;
			
			require_once( ABSPATH . 'wp-includes/class-snoopy.php');
			$snoopy = new Snoopy();
			
			
			$reserved = array('!', '#', '$', '%', '&', '\'', '(', ')', '*', '+', ',', '/', ':', ';', '=', '?', '@', '[', ']', ' ');
			
			$url = 'http://www.googleapis.com/buzz/v1/activities/search?q=body:"'.str_replace($reserved, '+', $post->post_title).'"&alt=json';
			$result = $snoopy->fetch($url);
						
			$this->debug('Find Buzz: '.$url);
			
			if(!$result) return false;
			
			$result = json_decode($snoopy->results);
			$this->debug('found '.count($result->data->items).' Buzzez.');
			//TODO: remove this part, as soon as searching for date works
			$match = false;
			if(is_array($result->data->items)) foreach($result->data->items as $item) {
				$this->debug('match '.strtotime($item->published).' == '.strtotime($post->post_date_gmt).' && '.$buzzComments->getProfileId().' == '.$item->actor->id);
				if(strtotime($item->published) == strtotime($post->post_date_gmt)
				&& $buzzComments->getProfileId() == $item->actor->id) {
					$match = $item;
					$this->debug('machting Buzz found.');
					break;
				}
			}
			
			if(!$match) {
				$this->debug('No Buzz found.');
				$this->setCommentCount(0);
				$this->updateCache();
				return false;
			}
			$this->debug('Buzz found.');
			$this->setCommentCount($match->links->replies[0]->count);
			$this->setUrl($match->links->replies[0]->href);
			$this->setLink($match->links->alternate[0]->href);

			$this->updateCache();
		}
		elseif($this->getUrl()) $this->fetchComments();

		return true;
	}
	
	function fetchComments() {
		global $post;
				
		$this->debug('Fetch comments.');
		
		if(!$this->getUrl()) {
			$this->debug('No comment URL.');
			return false;
		}
		
		$maxCacheTime = $this->filterCacheTime(60*rand(15,25));
		
		$commentData = get_post_custom_values('buzz_comments_comment_data', $post->ID);
		if($commentData) {
			$commentData = unserialize(current($commentData));
			$comments = unserialize($commentData['comments']);
		}
		
		$this->debug('Comments: '.count($comments).', Timestamp:'.$commentData['cacheTimestamp'].' >= '.(time()-$maxCacheTime));
		
		if($commentData && $commentData['cacheTimestamp'] >= (time()-$maxCacheTime)) {
			$this->debug('Deliver cached.');
			$this->setComments($comments);
		} else {
			require_once( ABSPATH . 'wp-includes/class-snoopy.php');
			$snoopy = new Snoopy();
			
			//TODO: we should use https, but it may cause compatibility problems, so there should be a option to disable it.
			//https isn't very important so far, because we just read public buzzez, but as soon as we use the write support,
			//only secure connections should be established
			$result = $snoopy->fetch('http'.substr($this->getUrl(), 5));
			
			$this->debug('Find Comments: http'.substr($this->getUrl(), 5));
			
			if(!$result) return false;
	
			$result = json_decode($snoopy->results);
			
			$this->debug('Found Comments: '.count($result->data->items).' ('.$this->getCommentCount().')');
			
			if(count($result->data->items) >= $this->getCommentCount()) {
				$this->setComments($result->data->items);

				update_post_meta($post->ID, 'buzz_comments_comment_data', array('comments' => serialize($this->getComments()),
																	'cacheTimestamp' => time()
																	));
				$this->setCommentCount(count($this->getComments()));
				$this->updateCache();
				$this->debug('Comments updated.');
			}
			
		}

		return $this->getComments();
	}
	
	function updateCache() {
		global $post;
		update_post_meta($post->ID, 'buzz_comments_data', array('commentCount' => $this->getCommentCount(),
															'url' => $this->getUrl(),
															'link' => $this->getLink(),
															'cacheTimestamp' => time()
															));
	}
	
	function filterCacheTime($time) {
		return (get_option('buzz_comments_debug') && $_REQUEST['buzzCommentsDisableCache'] ? 0 : $time);
	}
	
	function debug($string) {
		$debug = get_option('buzz_comments_debug', false);
		if($debug && $_REQUEST['buzzCommentsDebug']) {
			echo "
			<!-- buzzCommentsDebug - ".$string." -->
			";
		}
	}
}
?>