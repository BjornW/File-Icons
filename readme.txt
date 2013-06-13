=== File Icons ===
Contributors: BjornW
Tags: links, files, icons, regex, css, regular expressions, style 
Requires at least: 3.x
Tested up to: 3.2.1
Stable tag: trunk

Easily add icons to links, files and downloads using CSS classes, regular expressions and an image sprite

== Description ==

_WARNING_
The File Icons plugin has a new developer and has been completely rewritten. This version (3.x and upwards) is NOT backwards compatible 
with the old version of the plugin. Be careful when updating the old plugin!    

Using the File Icons plugin you can easily add icons to links in Posts, Pages and even Widgets 
(as long as the widget uses the widget_text filter such as WordPress default text widget). 

_How does it work?_
The File Icons plugin searches for specified link characteristics using <a href='https://en.wikipedia.org/wiki/Regular_expression' target='_new' title='Read about Regular Expressions on Wikipedia in a new window'>regular expressions</a>. When a match is found, the plugin will add the defined CSS class(es)to the link. Existing CSS classes will be preserved. The plugin goes through the file icons in the order they are shown and uses the first match it find.
The CSS classes are only added to the output, acting on the the_content and widget_text filters and thus the CSS classes are NOT written to the database.

By adding the css classes defined in the File Icons plugin to your theme's stylesheet you are able to style links using the added css classes, either with icons or something completely different. Or you may upload an image sprite and custom stylesheet instead of changing your theme's.


== Installation ==

1. Unzip the file-icons.zip 
2. Upload the unzipped `file-icons` directory to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Add the css classes defined in the File Icons settings to your theme's stylesheet
5. Add an image sprite used for the images in your css


== Frequently Asked Questions ==

= Where can I find the documentation? =

The File-Icons plugin uses the WordPress contextual help to provide documentation. 
You may access the documentation in the File Icons settings (Settings -> File Icons) 
by pressing the 'help' button in the top-right corner.


== Screenshots ==

no screenshots yet

== Changelog ==

= 3.2 = 
Added code to make sure a regex added by a user is not empty or invalid. 
If a regex is invalid the user is warned and asked to fix it or remove the regex.
These tests are done during the saving of the plugin options

= 3.1 = 
If a link contains an image, video, object or embed tag there will be no icon class added to the link. 
This prevents an unwanted side-effect of having an image/video/object/embed linking to a file so an icon is not
needed anymore.

= 3.0 =
BjornW took over development and completely rewrote plugin using a different approach, 
which is based on CSS sprites and regular expression. 
NOTE: This release is NOT backwards compatible with the previous versions.

= 2.2.1 =
Last release by wpdprx.

== Upgrade Notice ==

= From version 3.x and upwards: the plugin has seen a complete rewrite be careful when updating =
BjornW took over development and completely rewrote plugin using a different approach, 
which is based on CSS sprites and regular expression. 
NOTE: Releases 3.x and upwards are NOT backwards compatible with the previous version.


