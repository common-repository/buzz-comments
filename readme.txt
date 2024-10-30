=== Plugin Name ===
Contributors: mixer2
Tags: comments, buzz
Requires at least: 2.9.0
Tested up to: 3.0.1

Buzz Commments displays comments from your Google Buzz stream to your Wordpress posts, directly in the comment list on your blog.

== Description ==

Buzz Comments is for users who've their Wordpress blog in their Google Buzz connected sites list.
In that case discussions about the Blogposts often split in two seperate threads. Some users reply on Buzz and some on the Blog. This plugin adds the comments posted on Buzz about your posts to the comments on the blog. The Buzz responses just get inserted between the normal comments in the correct chronological order.
So the blog users can see the normal comments and the Buzz comments.

The plugin is beta and under heavy development. Please check from time to time for an update.

Note: It doesn't work in combination with the WPML Multilingual plugin so far.

[Try out a demonstration.](http://www.vertexten.de/2010/03/23/buzz-comments-demo-page/ "Demonstration")

Please read the [Documentation](http://code.google.com/p/wpbuzzcomments/wiki/Documentation "Documentation").

== Installation ==

1. Upload buzzComments folder to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to the 'Settings' menu and choose the 'Buzz Commments' submenu page.
4. Configure at least the 'Google Profile E-Mail' option.
5. Get sure, that your blog is in the connected sites list of your Buzz stream.

== Frequently Asked Questions ==

= Does it work with WP Supercache? =
You've to configure your Supercache that it invalidates the cache periodical. The use of Supercache will increase the time until the new comments are displayed at your posts.

= I get an "Fatal error" when I try to activate the Plugin. =
Install the newest Pluginversion. If you still get the error open a ticket with at least the Wordpress version and other plugins you use as information.

= The plugin is activated, but I don't see the comments. =
Please open a ticket! Provide at least Wordpress version, plugin version and your Google Buzz Profile URL as information.

== Screenshots ==

1. The plugin in action.
2. The configuration screen.

== Changelog ==
= 0.9.4 =
* more detailed debug info
* fixed an additional bug, that only the correct comments get displayed at a blog post

= 0.9.3 =
* added hebrew lang files from elad salomons
* fix for compatibility for strange languages (like hebrew ;) ), that doesn't work with utf-8 decode
* added option to add the XFN rel="me" link to the html head (to help people to connect their blog to buzz more easy)
* minor improvement to debugging mode
* more accurate check which comments belong to which post
* added check if configuration seems to be correct

= 0.9.2 =
* removed email from find query (it's more inaccurate, but does work)

= 0.9.1 =
* removed custom cache configuration
* changed cache behavior
* "Google Profile E-Mail" instead of Profile URL
* added debug mode
* snoopy instead of curl
* added clear cache button

= 0.9.0 =
* the buzz REST api is used, instead of the rss feeds
* clean uninstall
* clean update

= 0.8.7 =
* improved the english descriptions on settings page (thanks to Everett Guerny)

= 0.8.6 =
* should now work again with php 5.2 (0.8.5 used php 5.3 functions, sorry for the buggy version)
* fixed some minor bugs

= 0.8.6 =
* should now work again with php 5.2 (0.8.5 used php 5.3 functions, sorry for the buggy version)
* fixed some minor bugs

= 0.8.5 =
* more simple configuration
* fixed locale time bugs
* fixed problems with daylight savings time
* updated clearup database function (clean uninstall coming soon)
* plugin default folder name changed to buzz-comments (from buzzComments). it would work now with any plugin folder name.
* fixed cache time default value

= 0.8.2 =
* added compatibility to the highlight-author-comments plugin
* author name uses now display_name instead of name for buzzcomments from blog author
* "buzz is originally posted on google buzz" notification without threadded comments
* optional add the notification to the content instead of replacing the reply link

= 0.8.1 =
* added temporary database cleanup option (this option will be removed in the first stable version)
* fixed a new cache bug

= 0.8.0 =
* refactoring
* fixed cache bug

= 0.7.5 =
* replaced magpierss with SimplePie

= 0.7.2 =
* use magpierss from wordpress (maybe the finally fix the "fatal error" on activation bug) 0.8.0 will use SimplePie, so this is just a temporary fix

= 0.7.1 =
* fixed bug, that plugin couldn't be activated if magpierss is already included (sorry, now i know, that magpierss is not the wordpress way, i'll use SimplePie in the next release)

= 0.7.0 =
* i18n
* added german .mo and .po file
* fixed grammar in english "this post is" => "this post was"

= 0.6.2 =
* again tried to fix the problem with html entities in the post title

= 0.6.1 =
* use authors nickname instead of the name
* fixed a problem with html entities in the post title
* get everything ready for http://wordpress.org/extend/plugins (readme.txt, screenshots)

= 0.6.0 =
* added cache for details page
* options moved to settings->buzz comments
* use authors avatar and name from blog, instead of buzz
* custom buzz avatar in the options
* minor bugfixes

= 0.5.2 =
* added buzz icon and avatar

= 0.5.1 =
* added from_buzz class for css styling to buzz comments

= 0.5.0 =
* first public version