=== Wordpress Author Logo From Front ===
Contributors: MolaMola
Donate link:[]
Tags: user profile, company logo, frontend image uploading
Requires at least: 3.1.3
Tested up to: 3.1.3
Stable tag: 0.3.5

Upload company logos, profile images etc from front end for WP authors to show in posts and authors page.

== Description ==

Light plugin that adds front end image uploading functionality to any page or sidebar on a wp platform, Admin sets level for users
 to upload images that can be attached to their profile page and to posts etc. A shortcode `<?php echo (do_shortcode('[wpal-author-logo]')); ?>` (templatetag) is added to produce the form. On plugin activation
  6 fields are created in wp_options table, size (kbs) width px, height px, mime type, title and role (or minimum capbability). Admin access plugin via wp dashboard settings "WP Author Logo". Images are saved in wp-content/uploads/wpal_logos 

== Installation ==

Steps required to install the plugin
Upload to plugin directory
Activate
Go to wp-dashboard >> settings >> WP Author Logo and apply settings.

Add this templatetag where upload form needs to be shown `<?php echo (do_shortcode('[wpal-author-logo]')); ?>`

To show Logo / Profile pic on single.php pages add this template tag in the loop `<?php echo (do_shortcode('[wpal-single-image]')); ?>` where you want image to show. 

To show Logo / Profile pic on author.php pages add this template tag anywhere in the page `<?php echo (do_shortcode('[wpal-authorpage-image]')); ?>` where you want image to show. This 
template tag also contains the delete from front checkbox (only visible to the author)

To use with minimum role / capability you will also need to wrap the template tag like so:

`<?php if (current_user_can(get_option(wpal_role))) { ?> 
                         <?php echo (do_shortcode('[wpal-author-logo]')); ?>
                    <?php } ?>`

== Frequently Asked Questions ==

= No questions yet, be the first =

== Screenshots ==
`/tags/0.3.5/screenshot-1.jpg`
`/tags/0.3.5/screenshot-2.jpg`
== Changelog ==
 Fixed uploading issue where it would fail on file renaming.
 Added Front end delete checkbox (only visible to author who owns logo and admin)
== Upgrade Notice ==
Important upgrade fixes file upload and rename issue!
== To Do ==
1) Add a widget option.
2) Allow multiple mime types.