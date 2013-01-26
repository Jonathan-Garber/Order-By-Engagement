=== Order By Engagement ===
Author: TechStudio
Contributors: Jonathan-Garber, ryan.burnette
Donate link: http://techstudio.co/wordpress/plugins/order-by-engagement
Requires at least: 3.3.1
Tested up to: 3.5.1
Stable tag: 1.2.0

Order By Engagement allows developers to track post engagement and sort by those tracked values.


== Description ==

Order By Engagement is a WordPress plugin designed to allow theme developers to order posts by a value which corresponds to how engaged with a post users appear to be.

At the moment, OBE tracks engagement only be increasing a value, stored as a custom field value, when a corresponding post is accessed. Plugin settings allow administrators to throttle the increase of that value and to erode the current value over time. This is how the plugin tracks the maximum engagement value for a post and contrasts it with the current engagement value.


== Installation ==

1. Download the plugin and extract it
2. Upload the '/wp-obe/' directory to the '/wp-content/plugins/' directory
3. Activate the plugin through the 'Plugins' menu in WordPress


== Frequently Asked Questions ==

Q: How do I display posts in order by their engagement values?
A: Developers can utilize the WP_Query class in WordPress to sort posts by their engagement values. Check out [this OBE Wiki page](https://github.com/ryanburnette/Order-By-Engagement/wiki/Sort-WP_Query-By-Engagement) for more details.


== Changelog ==

= 1.2.0 =
* OBE now uses either PHP or Ajax to function, this allows the plugin to work on sites cached by plugins such as W3 Total Cache

= 1.1.0 =
* The plugin now saves settings in a more modern way
* Removed unnecessary functions
* Improved documentation

= 1.0.2 =
* Minor cosmetic bug fix to prevent the spam dection from displaying its debug message

= 1.0.1 =
* Minor bug fix

= 1.0.0 =
* Initial release