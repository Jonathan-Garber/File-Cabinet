=== File Cabinet ===

Contributors: Jonathan-Garber, ryan.burnette
Donate link: http://techstudio.co/wordpress/plugins/file-cabinet
Tags: file, embed, embedding, youtube, vimeo, dropbox, google drive
Requires at least: 2.0.2
Tested up to: 3.4.2
Stable tag: 1.2.3
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

File Cabinet creates a custom post type with special functions designed for managing a library of files of various types.

== Description ==

File Cabinet creates a custom post type with special functions designed for managing a library of files. Add files to the library by adding directly to WordPress's media management system, setting a remote URL to allow use of services like Dropbox and Google Drive. File Cabinet also supports YouTube and Vimeo videos via the video ID on either site. Novice users can use shortcodes to add items to their posts, while developers can take advantage of the loop and custom functions to create custom library output as well as a built-in permissions system.

== Installation ==

# Upload the `file-cabinet` directory to the `/wp-content/plugins/` directory
# Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= I set permissions on files but they're still showing up in loops. =

The permissions system is just for developer reference at the current time. It makes no changes to standard WordPress loops. Steps must be taken by an expert developer to make the permissions system 'secure'. Even then no steps have been taken to protect the URL of the downloadable file when hosted by WordPress. We will add documentation on how to implement this system upon request.

== Screenshots ==

1. Attach files to posts in the form of: WordPress Media, Public URL, YouTube or Vimeo
2. Use a list of available shortcodes from any post editor.
3. Each file has all the capabilities of any normal post.

== Changelog ==

= 1.2.3 =
* Added the ability to select which post types get the File Cabinet Shortcodes metabox.
* Fixed a bug which caused shortcodes to only put output at the top of content output.

= 1.2.2 =
*re-wrote a portion of the code to ensure a smoother operation of a lot of features
*incorporated a proper update system so you will not need to deactivate/reactivate the plugin to ensure some updates take effect


= 1.2.1 =
* Documentation updates.
* The permissions system can now be turned on and off. It is off by default, but its built-in functions are always available.
* Numerous interface improvements.
* Settings page moved to General Options sub-menu
* Settings page reorganized, additional options added
* Improved shortcode insert meta box
* Added embed template support

= 1.1.12 =

* Added ability to properly rename the file cabinet permalink & menu labels
* Added ability to automatically pull in the video thumbnail image for any youtube or vimeo videos in the cabinet. can call tsfc_check_add_thumb($post_id); to fetch the thumbnail if one is not already assigned.
* Vimeo or youtube videos automatically grab their thumbnail when added or updated if the option is enabled in settings.

= 1.1.11 =

* Added click to insert thumbnail shortcode to editor pages.
* Fixed bug that prevented users from modify the current video files sizes during edits.
* Added new shortcode [fc_thumb id=ID] to display the thumb anywhere shortcode is called.
* Added support for post thumbnails / featured image in file cabinet file/posts.
* Added new function tsfc_showthumb($post_id) This function will echo the thumbnail where its called in theme.

= 1.1.10 =

* Modified display for video width and height fields.
* Added ability to enforce default width and height on all current video embeds in the file cabinet

= 1.1.9 =

* Fixed bug in category selections
* Added some small shortcuts between edit category and edit file pages
* Renamed the slug for the "category" term/taxonomy to prevent future conflicts
* Added function tsfc_category_check($post->ID); - returns true if user is allowed to access a category
* Added function tsfc_file_Check($post->ID); - returns true if user is allowed access to the single file within the category
* Added function tsfc_dr_all_come($post->ID); - does the same as both functions above combined. Will show the file or nothing based on users permission against file and category permissions.

= 1.1.8 =

* Moved settings pane to a Page called settings in the File Cabinet menu
* Added shortcode list to post and page editors for click to insert shortcode ability
* Modified the information displayed in post list columns

= 1.1.7 =

* Added New Settings Pane for Default Settings for Video Embeds and Remote File URLS
* Cleaned up extra whitespace that was created around video embed code

= 1.1.6 =

* Added shortcode to display any file from the cabinet. [fc_file id=idhere]

= 1.1.5 =

* Fixed a bug that didn't let the plugins' scripts load

= 1.1.4 =

* Documentation update

= 1.1.3 =

* Bug fix for Extend

= 1.1.2 =

* Misc changes
* Prep for Extend

= 1.1.1 =

* Misc bug fixes

= 1.1.0 =

* Added Youtube and Vimeo capability
* Added shortcode for video embeds

= 1.0.0 =

* Initial release

== Upgrade Notice ==
