=== Plugin Name ===
Author: TechStudio
Contributors: 
Donate link: http://techstudio.co/wordpress/plugins/order-by-engagement
Tags: 
Requires at least: 2.0.2
Tested up to: 3.3.1
Stable tag: 1.0.2

Order By Engagement is a WordPress plugin designed to allow site admins to sort posts by a new factor: engagement. Engagement is defined by the plugin in three ways: landing on a post from another referring URI, engaging in any social media integrating on the site, or any other hook that calls the modify_engagement() function.


== Description ==


Order By Engagement is a WordPress plugin designed to allow site admins to sort posts by a new factor: engagement. Engagement is defined by the plugin in three ways: landing on a post from another referring URI, engaging in any social media integrating on the site, or any other hook that calls the modify_engagement() function. The plugin keeps track of engagement using custom post meta. Posts that then be looped in ascending or descending order using the get_engaged_posts() function to modify the main WordPress $post query. Imaging all the things you can do with posts in order by engagement. Have fun. :)


== Installation ==

1. Download the plugin and extract it
2. Upload the '/wp-obe/' directory to the '/wp-content/plugins/' directory
3. Activate the plugin through the 'Plugins' menu in WordPress


== Frequently Asked Questions ==

Q: How do I set posts to be in order by the engagement factor?
A: Ordering posts by engagement is no automatic. You must edit your theme to use the get_engaged_posts() function.


== Screenshots ==


== Changelog ==

= 1.0.2 =
* Minor cosmetic bug fix to prevent the spam dection from displaying its debug message

= 1.0.1 =
* Minor bug fix

= 1.0.0 =
* Initial release


== Upgrade Notice ==

