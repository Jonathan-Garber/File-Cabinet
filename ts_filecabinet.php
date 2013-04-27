<?php

/*

	Plugin Name: File Cabinet
	Plugin URI: http://techstudio.co/wordpress/plugins/file-cabinet
	
	Description: File Cabinet creates a custom post type with special functions designed for managing a library of files of various types.
	
	Version: 1.2.6
	
	Author: TechStudio
	Author URI: http://techstudio.co
	
	License URI: http://www.gnu.org/licenses/gpl-2.0.html
	 
	Copyright 2011 TECH STUDIO, INC (FLORIDA, USA)  | ( email: ryan@techstudio.co )
	This program is free software; you can redistribute it and/or modify it under the terms
	of the GNU General Public License, version 2, as published by the Free Software Foundation.
	This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
	without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details. You should have received a copy of
	the GNU General Public License along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

//set defaults on activation
register_activation_hook( __FILE__, 'fc_defaults' );

function fc_defaults(){
		add_option("fc_nameS", 'File');
		add_option("fc_nameP", 'Files');
		add_option("fc_slug", 'my_files');
		add_option("fc_desc", 'simple file cabinet');
		add_option("fc_public_query", 'true');
		add_option("fc_auto_thumb", 'true');
		add_option("fc_permissions", 'false');
		add_option("fc_video_width", '720');
		add_option("fc_video_height", '1280');
}


$plugin_url = plugin_dir_url(__FILE__);
wp_register_style( 'tsfc-styles', $plugin_url.'css/style.css', false, 'current', 'screen' );

// right now these are just being enqueued for every admin page, let's find a way to be more selective though
if ( is_admin() ) {
	wp_enqueue_style('tsfc-styles');
}

wp_register_script( 'tsfc-scripts', $plugin_url.'js/script.js', false, 'current', true);
wp_register_script( 'tsfc-script-settings', $plugin_url.'js/script-settings.js', false, 'current', true);
wp_register_script( 'tsfc-permissions-form', $plugin_url.'js/script-permissions-form.js', false, 'current', true);
wp_register_script( 'tsfc-attachment-metabox', $plugin_url.'js/script-attachment-metabox.js', false, 'current', true);
wp_register_script( 'tsfc-organic-tabs', $plugin_url.'js/organictabs.jquery.js', false, 'current', true);

/*
	Enqueue scripts for settings page
*/
if ( isset($_GET['page']) && $_GET['page'] == 'file-cabinet-settings' )
	wp_enqueue_script('tsfc-script-settings');

add_filter('manage_edit-tsfc_files_columns', 'new_tsfc_files_columns');

	function new_tsfc_files_columns($tsfc_files_columns) {
		$new_columns['cb'] = '<input type="checkbox" />'; 
		$new_columns['title'] = _x('File Name', 'column name'); 
		$new_columns['shortcode'] = __('Shortcode');	
		$new_columns['category'] = __('Category'); 
		$new_columns['date'] = _x('Date', 'column name'); 	
		return $new_columns;
	}
        // Add to admin_init function
	add_action('manage_tsfc_files_posts_custom_column', 'manage_tsfc_files_columns', 10, 2);
 
	function manage_tsfc_files_columns($column_name, $id) {
		global $wpdb;
		global $plugin_url;
		switch ($column_name) {
		case 'shortcode':
			echo '[fc_file id='.$id.']';
		break;
		
		case 'category':
			$categories = get_the_terms( $id, 'tsfc_category' );
			$seperator = ' ';
			$output = '';
			if($categories){
				foreach($categories as $category) {
					$output .= '<a href="/wp-admin/edit.php?post_type=tsfc_files&tsfc_category='.$category->slug.'" title="' . esc_attr( sprintf( __( "View all posts in %s" ), $category->name ) ) . '">'.$category->name.'</a>'.$seperator;
					
					$settings = '<a href="/wp-admin/edit-tags.php?action=edit&taxonomy=tsfc_category&tag_ID='.$category->term_id.'&post_type=tsfc_files" title="' . esc_attr( sprintf( __( "Edit Settings For %s" ), $category->name ) ) . '">Edit Settings</a>'.$seperator;
				}
			echo trim($output, $seperator);
			echo '<br/>';
			echo trim($settings, $seperator);
			}else{
				echo 'No Category Assigned<br/>Please <a href="/wp-admin/post.php?post='.$id.'&action=edit">Assign a Category</a>';
			}
			
			break;
		default:
			break;
		} // end switch
	}

/*
	Add Settings Menu
*/
function tsfc_menu() {
	add_submenu_page( 'options-general.php', 'File Cabinet Settings', 'File Cabinet', 'manage_options', 'file-cabinet-settings', 'tsfc_settings_menu' );
}
add_action('admin_menu', 'tsfc_menu');

function tsfc_settings_menu(){
	include 'functions/settings_menu.php';
}


add_shortcode('fc_file', 'fc_file');
function fc_file($atts) {	
extract(shortcode_atts(array(
		'id' => '0'
	), $atts));
$id = $atts[id];
ob_start();
$file = tsfc_showfile($id);
$return = ob_get_clean();
return $return;
}

add_shortcode('fc_thumb', 'fc_thumb');
function fc_thumb($atts) {
	extract(shortcode_atts(array(
		'id' => '0'
	), $atts));
$filepath = "fc_thumb.php";
ob_start(); 
$id = $atts[id];
include($filepath);
$content = ob_get_clean(); 
return $content;
}


function modify_form(){
echo  '<script type="text/javascript">
      jQuery("#post").attr("enctype", "multipart/form-data");
        </script>
  ';
}
add_action('admin_footer','modify_form');
// these add_actions were throwing errors so i disabled them
//add_action('admin_print_scripts', 'tsfc_admin_scripts');
//add_action('admin_print_styles', 'tsfc_admin_styles');

add_action('init', 'tsfc_register_posts');
add_action('delete_post', 'tsfc_register_posts');
add_action( 'admin_init', 'tsfc_change_excerpt' );

	function tsfc_change_excerpt() {
		remove_meta_box('postexcerpt', 'tsfc_files', 'normal');
		add_meta_box('postexcerpt', __('File Description'), 'post_excerpt_meta_box', 'tsfc_files', 'normal', 'high');
	}

function tsfc_category_taxonomy_add_new_meta_field() {
	// this will add the custom meta field to the add new term page
 $roles = get_editable_roles();


 // put the term ID into a variable
$t_id = $term->term_id;
?>

	<?php 
		// this should be hidden if permissions are turned off 
		//check if permissions is turned on in settings
		$permissions = get_option('fc_permissions');
		if ($permissions == 'true'){
	?>
	<?php wp_enqueue_script('tsfc-permissions-form'); ?>
	<div class="form-wrap">
		<h3>Permissions</h3>
		<div class="form-field file-cabinet-permissions">
			<?php
				foreach ($roles as $r => $v) {
					$role = $r;
					$id = strtolower($r);
					echo '<label><input type="checkbox" class="not-everyone" style="width:22px;" name="tsfc_role[]" value="'.$id.'">'.$role.'</label>';
				}
			?>
			
			<?php // this should default to everyone and show selected unless someone has changed it ?>
			<label>
				<input class="file-cabinet-permissions-everyone" type="checkbox" style="width:22px;" name="tsfc_role[]" value="everyone">Everyone
			</label>
			<p style="padding-left:5px;color:#666;"><em>Set permission for the File Cabinet plugin. Remember, these features must be implemented by an expert developer in order to be secure.</em></p>
		</div>
	</div>
<?php
	}//end permissions check
}
add_action( 'tsfc_category_add_form_fields', 'tsfc_category_taxonomy_add_new_meta_field', 10, 2 );
function tsfc_taxonomy_edit_meta_field($term) {
 $roles = get_editable_roles();
 
	// put the term ID into a variable
	$t_id = $term->term_id;
 
	// retrieve the existing value(s) for this meta field. This returns an array
	$current_role = get_option( "taxonomy_$t_id" );
	$current_role = unserialize($current_role);

	?>
	<?php // this should be hidden if permissions are turned off 
		//check if permissions is turned on in settings
		$permissions = get_option('fc_permissions');
		if ($permissions == 'true'){
	?>
	<?php wp_enqueue_script('tsfc-permissions-form'); ?>
	<tr class="form-field" style="margin-bottom:20px;">
		<th scope="row" valign="top">Permissions</th>
		<td class="file-cabinet-permissions">
			<?php
					foreach ($roles as $r => $v) {
						$role = $r;
						$id = strtolower($r);
					if ( !empty($current_role) && in_array($id, $current_role ) ) {
						echo '<label><input type="checkbox" id="check-'.$id.'" class="not-everyone" style="width:22px;" checked="checked" name="tsfc_role[]" value="'.$id.'">'.$role.'</label><br>';
					}
					else {
						echo '<label><input type="checkbox" id="check-'.$id.'" class="not-everyone" style="width:22px;"  name="tsfc_role[]" value="'.$id.'">'.$role.'</label><br>';
					}
				}
			?>
			<?php // everyone should be selected by default unless the permissions were inherited from the parent or no changes have been made ?>
			<label>
				<input <?php if (!empty($current_role) && in_array('everyone', $current_role)){ ?> checked="checked" <?php } ?> id="check-everyone" class="file-cabinet-permissions-everyone" style="width:22px;" type="checkbox" name="tsfc_role[]" value="everyone">Everyone
			</label>
			<p style="color:#666;"><em>Set permission for the File Cabinet plugin. Remember, these features must be implemented by an expert developer in order to be secure.</em></p>
		</td>
	</tr>

	<!--
	<tr id="note-normal"><th></th><td>Category Permissions<br/>You can set this category's permission to any user role(s) available. The user role(s) you choose here will be enforced on all files within this category. This is useful for creating private categories for specific roles.</td></tr>
	-->

<?php
	}//end permissions check
}
add_action( 'tsfc_category_edit_form_fields', 'tsfc_taxonomy_edit_meta_field', 10, 2 );

// Save extra taxonomy fields callback function.
function tsfc_save_taxonomy_custom_meta( $term_id ) {
	if ( isset( $_POST['tsfc_role'] ) ) {
		$t_id = $term_id;
		$term = get_term( $term_id, 'tsfc_category' );
		$name = $term->name;

		$term_meta = serialize($_POST['tsfc_role']);
		
		if (in_array('everyone', $_POST['tsfc_role'])){
			$term_meta = serialize(array('0' => 'everyone'));
			update_option( "taxonomy_$t_id", $term_meta );
		}else{
			$posts = get_posts( array(
					'post_type'   => 'tsfc_files',
					'numberposts' => -1,
					'taxonomy'    => 'tsfc_category',
					'term'        => $name
					) );
				foreach ($posts as $p){
				update_post_meta( $p->ID, 'allowed_role', $term_meta );
				}		
				update_option( "taxonomy_$t_id", $term_meta );
		}
	}
}
add_action( 'edited_tsfc_category', 'tsfc_save_taxonomy_custom_meta', 10, 2 );  
add_action( 'create_tsfc_category', 'tsfc_save_taxonomy_custom_meta', 10, 2 );

function tsfc_posttype($post_type) {$tsfc_post_type = strtolower($post_type);$tsfc_post_type = preg_replace("/[^a-z0-9_\-]/i", "", $tsfc_post_type);$tsfc_post_type = stripslashes($tsfc_post_type);$tsfc_post_type = tsfc_truncate_text($tsfc_post_type, 20);return $tsfc_post_type;
}

function tsfc_truncate_text($text, $nbrChar, $append='') {if ( strlen($text) > $nbrChar ) {$text = substr($text, 0, $nbrChar);if ( $append ) $text .= $append;}return $text;
}

function tsfc_register_posts(){
	$fc_nameS = get_option( "fc_nameS" );
	$fc_nameP = get_option( "fc_nameP" );
	
	$fc_slug = get_option( "fc_slug" );
	
	$fc_desc = get_option( "fc_desc" );
	$fc_public_query = get_option( "fc_public_query" );
		if ($fc_public_query == 'true'){
			$fc_public_query = true;
		}else{
			$fc_public_query = false;
		}
	
  $labelsP = array(
    'name' => _x($fc_nameP, 'post type general name'),
    'singular_name' => _x($fc_nameS, 'post type singular name'),
    'add_new' => _x('Add New', $fc_nameS),
    'add_new_item' => __('Add New '.$fc_nameS),
    'edit_item' => __('Edit '.$fc_nameS),
    'new_item' => __('New '.$fc_nameS),
    'all_items' => __('All '.$fc_nameP),
    'view_item' => __('View '.$fc_nameS),
    'search_items' => __('Search '.$fc_nameP),
    'not_found' =>  __('No '.$fc_nameP.' found'),
    'not_found_in_trash' => __('No '.$fc_nameP.' found in Trash'), 
    'parent_item_colon' => '',
    'menu_name' => __($fc_nameP)
  );
	
	register_post_type ( 'tsfc_files',
		array( 
		'label' => $fc_nameS,
		'public' => true,
		'publicly_queryable' => $fc_public_query,
		'description' => $fc_desc,
		'show_ui' => true,
		'labels' => $labelsP,
		'menu_icon' => '/wp-admin/images/generic.png',
		'show_in_admin' => true,
		'show_in_nav_menus' => false,
		'rewrite' => array('slug' => $fc_slug),
		'supports' => array(
						 'title',
						 'excerpt',
						 'custom-fields',
						 'thumbnail'
							)
						)
	);
			flush_rewrite_rules();
  // Add new taxonomy, make it hierarchical (like categories)
  $labels = array(
    'name' => _x( 'Category', 'taxonomy general name' ),
    'singular_name' => _x( 'Category', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Categories' ),
    'all_items' => __( 'All Categories' ),
    'parent_item' => __( 'Parent Category' ),
    'parent_item_colon' => __( 'Parent Category:' ),
    'edit_item' => __( 'Edit Category' ), 
    'update_item' => __( 'Update Category' ),
    'add_new_item' => __( 'Add New Category' ),
    'new_item_name' => __( 'New Category Name' ),
    'menu_name' => __( 'Category' ),
  ); 

   register_taxonomy(tsfc_category,array('tsfc_files'), array(
    'hierarchical' => false,
    'labels' => $labels,
    'show_ui' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'tsfc_category' ),
  )); 
  
  	flush_rewrite_rules();
}

function tsfc_remove_metaboxes() {
 
 //DEBUG
 //remove_meta_box( 'postcustom' , 'tsfc_files' , 'normal' );
 remove_meta_box( 'tagsdiv-tsfc_category' , 'tsfc_files' , 'normal' );
 remove_meta_box( 'slugdiv' , 'tsfc_files' , 'normal' );
}
add_action( 'admin_init' , 'tsfc_remove_metaboxes' );

// Hook into WordPress
add_action( 'admin_init', 'tsfc_add_custom_metabox' );
add_action( 'save_post', 'tsfc_save_custom_metabox' );

/**
 * Add meta box
 */
function tsfc_add_custom_metabox() {
	add_meta_box( 'tsfc-custom-metabox', __( 'File Details' ), 'tsfc_custom_metabox', 'tsfc_files', 'normal', 'default' );
	
	$current_post_type = get_post_type();
	$post_types = get_option( "fc_metabox_types" );	
		if (is_array($post_types)){
		
			foreach ($post_types as $p){
				add_meta_box( 'tsfc-shortcode-metabox', __( 'File Cabinet Shortcodes' ), 'tsfc_shortcode_metabox', $p, 'normal', 'default' );			
			}
			
		}else{
			$args=array(
			  'public'   => true,
			  '_builtin' => false
			); 
			$output = 'names';
			$operator = 'and';
			$post_types = get_post_types($args,$output,$operator);
			$post_types[] = 'post';
			$post_types[] = 'page';
				foreach ($post_types as $p){
			add_meta_box( 'tsfc-shortcode-metabox', __( 'File Cabinet Shortcodes' ), 'tsfc_shortcode_metabox', $p, 'normal', 'default' );
			}
		}
}

function tsfc_shortcode_metabox() {
	global $plugin_url;
	global $post;	
	$args = array(
		'post_type' => 'tsfc_files',
		'numberposts' => -1,
		'orderby' => 'title',
		'order' => 'ASC',
		'post_status' => null
	);
	$files = get_posts($args);
	
	if ( $files ) : ?>
<?php wp_enqueue_script('tsfc-scripts'); ?>
	<div class="inside">

		<table class="tsfc-insert widefat">
		
			<thead>
				<tr>
					<th>ID</th>
					<th>Title</th>
					<th>Preview</th>
					<th>Insert</th>
					<th>Shortcode</th>
				</tr>
			</thead>
		
			<tbody>
	  
				<?php foreach ( $files as $i => $f ) : ?>
				<tr id="tsfc-<?php echo $f->ID ?>">
					<td><?php echo $f->ID ?></td>
					<td><?php echo $f->post_title ?></td>
					<td><a target="_blank" href="/wp-content/plugins/file-cabinet/preview.php?id=<?php echo $f->ID ?>">Preview Attachment</a></td>
					<td><a class="do_code" var="<?php echo $f->ID ?>" href="javascript:void(0);">Insert Shortcode</a></td>
					<td>[fc_file id=<?php echo $f->ID ?>]</td>
				</tr>
				
				<?php endforeach; ?>
		
			</tbody>
		
		</table>
		
		<span class="tsfc-use">Insert items from your File Cabinet.</span>

	</div>

	<?php endif;

}
/**
 * Display the metabox
 */


function tsfc_custom_metabox() {
	
	global $plugin_url;

	global $post;
	$fileid = get_post_meta( $post->ID, 'upload_attachment', false );	
	$roles = get_editable_roles();

$post_role = get_metadata('post', $post->ID, 'allowed_role', true);
if ($post_role == ''){
$post_role = array();
}else{
$post_role = unserialize($post_role);
}
$remote_url = get_metadata('post', $post->ID, 'file_url', true);
$vimeo_video = get_metadata('post', $post->ID, 'vimeo_video', true);
$youtube_video = get_metadata('post', $post->ID, 'youtube_video', true);
if ($vimeo_video){
$width = get_metadata('post', $post->ID, 'vimeo_video_width', true);
$height = get_metadata('post', $post->ID, 'vimeo_video_height', true);}if ($youtube_video){$width = get_metadata('post', $post->ID, 'youtube_video_width', true);$height = get_metadata('post', $post->ID, 'youtube_video_height', true);}

if ($width == ''){
$width = get_option( "tsfc_default_width" );
}

if ($height == ''){
$height = get_option( "tsfc_default_height" );
}


$embed = get_metadata('post', $post->ID, 'embed', true);

 $args = array(
   'post_type' => 'attachment',
   'numberposts' => -1,
   'post_status' => null,
   'post_parent' => $post->ID,
  );

  $attachments = get_posts( $args );
  
  
if ($remote_url){
	$type = 'remote';
}else if ($vimeo_video){
	$type = 'vimeo';
}else if ($youtube_video){
	$type = 'youtube';
}else if ($attachments){
	$type = 'upload';
}else{
	$type = 'none';
}
?>

<?php switch ($type) {

	case 'remote' : ?>
	<input type="hidden" name="meta_field" value="file_url">	
	<?php break; ?>
	
	<?php case vimeo: ?>
		<input type="hidden" name="meta_field" value="vimeo_video">
<?php
		break;
	case youtube:
?>
		<input type="hidden" name="meta_field" value="youtube_video">
<?php
		break;
	case upload:
        foreach ( $attachments as $attachment ) {
?>		
			<?php echo wp_get_attachment_image( $attachment->ID, 'thumbnail', true) ?><br/>
			<a href="<?php echo wp_get_attachment_url( $attachment->ID ) ?>" target="_blank"><?php echo wp_get_attachment_url( $attachment->ID ) ?></a><br/>
			File Name: <?php echo apply_filters( 'the_title', $attachment->post_title ) ?><br/>
			<input type="hidden" name="remove[]" value="<?php echo $attachment->ID ?>"><br/><br/>
<?php
		}
		break;
	case none:
		$no_attachments = true;
		break;

} //end switch  

?>
<?php wp_enqueue_script('tsfc-attachment-metabox'); ?>
<h4>Attachment Method:</h4>
<span id="attachment_type" var="<?php echo $type ?>" style="display: hidden;"></span>
<div id="tsfc-attachment">

	<div class="tsfc-choose-attachment-method inactive">
		
		<span attachmentMethod="upload">WordPress Media</span>
		<span attachmentMethod="remote">Public URL</span>
		<span attachmentMethod="youtube">YouTube</span>
		<span attachmentMethod="vimeo">Vimeo</span>
	
	</div>
	
	<div class="tsfc-attachment-methods inactive">
	
		<div class="attachment-method upload">
			<p><input type="file" name="upload_attachment[]"></p>
			<p><em>Attach a file using WordPress Media. The file will be added to the WordPress Media and will be associated with this post.</em></p>
		</div>
		
		<div class="attachment-method remote">
			<p><input name="remoteurl" type="text" class="text-input" size="50" value="<?php echo $remote_url ?>" /></p>
			<p><em>Attach a file by inputting its public URL starting with http://. This is useful for integration with services such as Dropbox, Google Drive and others.</em></p>
		</div>
		
		<div class="attachment-method youtube">
			<p><input name="youtube_url" type="text" class="text-input" size="15" value="<?php echo $youtube_video ?>" /></p>
			<p><em>Attach a YouTube video by inputting its video ID. This can be found in the URL of the video, usually after '?watch=' and before the following &amp;.</em></p>
		</div>
		
		<div class="attachment-method vimeo">
			<p><input name="vimeo_url" type="text" class="text-input" size="15" value="<?php echo $vimeo_video ?>" /></p>
			<p><em>Attach a Vimeo video by inputting its video ID. This can be found in the URL of the video, usually after 'vimeo.com/'.</em></p>
		</div>
			<div class="video-settings">
				<h4>Video Settings</h4>
<?php
	$vimeo_default = get_metadata('post', $post->ID, 'vimeo_default', true);
	$youtube_default = get_metadata('post', $post->ID, 'youtube_default', true);
	$youtube_related_video = get_metadata('post', $post->ID, 'related_video', true);
?>
			<div class="youtube_player_settings">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">Hide Related Videos</th>
							<td>
								<label>
									<input <?php if ($youtube_related_video == 'on') { echo 'checked="checked"'; } ?> type="checkbox" name="youtube_related_video">
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row">Width & Height</th>
							<td>
								<label>
									<input <?php if ($youtube_default == 'yes') { echo 'checked="checked"'; } ?> class="rb" name="fc_video_settings" type="radio" value="default"> Default
								</label><br>
								<label>
									<input <?php if ($youtube_default == 'no') { echo 'checked="checked"'; } ?> class="rb" name="fc_video_settings" type="radio" value="custom"> Custom
								</label>
							</td>
						</tr>						
					</tbody>
				</table>
				<div class="youtube_custom_settings">
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row">Width</th>
								<td>
								<input class="ti" id="fc_video_default_width" name="yvideo_width" type="text" value="<?php echo $width ?>" size="5">px<br>
								<span><em>Set the default width in pixels. Leave either option plank to have the plugin calculate the blank option via aspect ratio.</em></span>
								</td>
							</tr>
							<tr>
								<th scope="row">Height</th>
								<td>
									<input class="ti" id="fc_video_default_height" name="yvideo_height" type="text" value="<?php echo $height ?>" size="5">px<br>
									<span><em>Set the default height. Leave either option plank to have the plugin calculate the blank option via aspect ratio.</em></span>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="vimeo_player_settings">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">Width & Height</th>
							<td>
								<label>
									<input <?php if ($vimeo_default == 'yes') { echo 'checked="checked"'; } ?> class="rb" name="fc_video_settings" type="radio" value="default"> Default
								</label><br>
								<label>
									<input <?php if ($vimeo_default == 'no') { echo 'checked="checked"'; } ?> class="rb" name="fc_video_settings" type="radio" value="custom"> Custom
								</label>
							</td>
						</tr>						
					</tbody>
				</table>
				<div class="vimeo_custom_settings">
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row">Width</th>
								<td>
								<input class="ti" id="fc_video_default_width" name="vvideo_width" type="text" value="<?php echo $width ?>" size="5">px<br>
								<span><em>Set the default width in pixels. Leave either option plank to have the plugin calculate the blank option via aspect ratio.</em></span>
								</td>
							</tr>
							<tr>
								<th scope="row">Height</th>
								<td>
									<input class="ti" id="fc_video_default_height" name="vvideo_height" type="text" value="<?php echo $height ?>" size="5">px<br>
									<span><em>Set the default height. Leave either option plank to have the plugin calculate the blank option via aspect ratio.</em></span>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>					</div>
	</div>
</div>

<h4>File Category:</h4>

<?php
$args = array(
	'type'                     => 'tsfc_files',
	'orderby'                  => 'name',
	'order'                    => 'ASC',
	'hide_empty'               => 0,
	'hierarchical'             => 0,
	'exclude'				   => 1,
	'taxonomy'                 => 'tsfc_category',
	'pad_counts'               => false );
//exclude cat 1 because we dont count uncategorized files

$categories = get_categories( $args );
$current_category = wp_get_object_terms( $post->ID, 'tsfc_category');
$term_id = $current_category[0]->term_id;
$current_category = $current_category[0]->name;
// retrieve the existing value(s) for this meta field. This returns an array
$category_role = get_option( "taxonomy_$term_id" );
$category_role = unserialize($category_role);

?>	

<select id="select_cat" name="cat">
	<option value="new_cat">New Category</option>
	<?php	
		foreach ($categories as $cat){
			$cat_name = $cat->name;
			if ($current_category == $cat_name){
			echo '<option selected="selected" value="'.$cat_name.'">'.$cat_name.'</option>';
			}else{
			echo '<option value="'.$cat_name.'">'.$cat_name.'</option>';
			}
		}	
	?>
</select>

<div class="new_cat_name">
New Category Name: <input type="text" name="cat_name">
</div>
<?php 
//check if permissions is turned on in settings
$permissions = get_option('fc_permissions');
if ($permissions == 'true'){
?>
<?php wp_enqueue_script('tsfc-permissions-form'); ?>
<span class="file_permissions_label">
<h4>Permissions:</h4>
</span>
<span class="cat_permissions_label">
<h4>Set New Category Permissions:</h4>
</span>
	<?php
	if ( empty($category_role) || in_array('everyone', $category_role) ) {
		
		echo '<div class="file-cabinet-permissions">';
		
		foreach ($roles as $r => $v) {
			$role = $r;
			$id = strtolower($r);
			if (in_array($id, $post_role)) {
				echo '<label style="display:block;margin-bottom:-14px;"><input class="not-everyone" type="checkbox" checked="checked" style="width:22px;"  name="tsfc_role[]" value="'.$id.'" />'.$role.'</label><br/>';
			}
			else {
				echo '<label style="display:block;margin-bottom:-14px;"><input class="not-everyone" type="checkbox" name="tsfc_role[]" style="width:22px;"  value="'.$id.'" />'.$role.'</label><br/>';
			}

		}
	?>
	<label style="display:block;margin-bottom:20px;"><input class="file-cabinet-permissions-everyone" <?php if (in_array('everyone', $post_role)){ ?> checked="checked" <?php } ?> type="checkbox" style="width:22px;" name="tsfc_role[]" value="everyone" />Everyone</label></div>
	<?php
	}
	else {
		echo 'This file is currently locked to the following Role(s) by the category settings.<br/><ol>';
		foreach ($category_role as $role) {
			echo '<li>'.ucfirst($role).'</li>';
		}		
		echo '</ol>To change these settings please move the file to another category or <a href="/wp-admin/edit-tags.php?action=edit&taxonomy=tsfc_category&tag_ID='.$term_id.'&post_type=tsfc_files">modify the category permissions</a>.';
	}
}//end check if permissions is turned on switch

?>

<br><br>
<?php // we offer this button only if someone has opened a post that already existed, it's just a button that saves them having to go to the Update button, it also reminds them that they actually must click Update when they make changes, some WordPress functions save with Ajax and don't require it, so let's make the distinction ?><input name="save" type="submit" class="button-primary" id="publish" tabindex="5" accesskey="p" value="Update">
<?php
	if ($no_attachments != true){
?>
<a target="_blank" href="/wp-content/plugins/file-cabinet/preview.php?id=<?php echo $post->ID ?>" class="button-secondary">Preview Attachment</a>
<input type="submit" name="remove_it" value="Remove Attachment" class="button-secondary" />
<?php		
	}//end if any attachment
	
}//end function

function tsfc_attach($file_handler,$post_id) {

  // check to make sure its a successful upload
  if ($_FILES[$file_handler]['error'] !== UPLOAD_ERR_OK) __return_false();

  require_once(ABSPATH . "wp-admin" . '/includes/image.php');
  require_once(ABSPATH . "wp-admin" . '/includes/file.php');
  require_once(ABSPATH . "wp-admin" . '/includes/media.php');
  $attach_id = media_handle_upload( $file_handler, $post_id );
  return $attach_id;
} 


/**
 * Process the custom metabox fields
 */
function tsfc_save_custom_metabox( $post_id ) {
	global $post;
	$youtube = $_POST['youtube_url'];
	$vimeo = $_POST['vimeo_url'];
	$remote = $_POST['remoteurl'];
	$files = $_FILES['upload_attachment'];
	$cat = $_POST['cat'];
	$permissions = $_POST['tsfc_role'];
	$enabled = get_option( "fc_auto_thumb" );	
	

	//get selected category
	if (!empty($cat)){
		$category_id = $_POST['cat'];
		$category_name = $_POST['cat_name'];		
		
		//is it new cat
		if ($category_name != '' && $category_id == 'new_cat'){
			$slug = preg_replace('/[^a-z]/', '-', strtolower($category_name));
			$slug = substr($slug, 0, 15);			
			$term_id = wp_insert_term( $category_name, 'tsfc_category', $args = array('description'=> '', 'slug' => $slug) );
			$term_id = $term_id[term_id];
			wp_set_object_terms( $post_id, $term_id, 'tsfc_category', false);

		//set permissions for new cat
			$role = $_POST['tsfc_role'];
			$role = serialize($role);	
			update_post_meta( $post->ID, 'allowed_role', $role );		
			update_option( "taxonomy_$term_id", $role );
			
		}
		
		if ($category_id != 'new_cat'){
			$term_id = $category_id;
			wp_set_object_terms( $post_id, $term_id, 'tsfc_category', false);
		}
	}

	//set the permissions for the file
	if (!empty($permissions)){			
		$role = $_POST['tsfc_role'];
		$role = serialize($role);	
		update_post_meta( $post->ID, 'allowed_role', $role );		
		update_option( "taxonomy_$term_id", $role );
	}else{
		$role = serialize(array('0' => 'everyone'));
		update_post_meta( $post->ID, 'allowed_role', $role );
		$term_meta = serialize(array('0' => 'everyone'));		
		update_option( "taxonomy_$term_id", $term_meta );
	}

	//do the videos
	if ( !empty($youtube) || !empty($vimeo) ){
		//remove existing files
		$rm = get_metadata('post', $post->ID, 'upload_attachment', true);
		wp_delete_attachment($rm, false);
		delete_post_meta($post_id, 'upload_attachment');		
		delete_post_meta($post_id, 'vimeo_video');
		delete_post_meta($post_id, 'youtube_video');		
		delete_post_meta($post_id, 'file_url');
		delete_post_meta($post_id, 'embed');						
		delete_post_meta($post_id, 'vimeo_video_height');		
		delete_post_meta($post_id, 'vimeo_video_width');		
		delete_post_meta($post_id, 'youtube_video_height');		
		delete_post_meta($post_id, 'youtube_video_width');				
		delete_post_meta($post_id, 'related_video');		
		$custom = $_POST['fc_video_settings'];
		
if (!empty($youtube)){				
		$video_url = $_POST['youtube_url'];				
		$video_type = 'youtube_video';												
		
		$related_video = $_POST['youtube_related_video'];	
		update_post_meta( $post->ID, 'related_video', $related_video );										
		
		$options .= '?';										
		if ($related_video == 'on'){
			$options .= 'rel=0'.'&';
		}
		
	if ($custom == 'custom'){
		update_post_meta( $post_id, 'youtube_default', 'no' );					
		$width = $_POST['yvideo_width'];					
		$height = $_POST['yvideo_height'];															
	}else{					
		update_post_meta( $post_id, 'youtube_default', 'yes' );					
		$width = get_option( "fc_video_width" );					
		$height = get_option( "fc_video_height" );	}	
}		
		
if (!empty($vimeo)){
		$video_url = $_POST['vimeo_url'];
		$video_type = 'vimeo_video';
		if ($custom == 'custom'){
			update_post_meta( $post_id, 'vimeo_default', 'no' );
			$width = $_POST['vvideo_width'];
			$height = $_POST['vvideo_height'];
		}else{
			update_post_meta( $post_id, 'vimeo_default', 'yes' );
			$width = get_option( "fc_video_width" );
			$height = get_option( "fc_video_height" );
		}
}
	update_post_meta( $post->ID, $video_type, $video_url );
	update_post_meta( $post->ID, $video_type.'_width', $width );
	update_post_meta( $post->ID, $video_type.'_height', $height );
	tsfc_generate_video($video_type, $video_url, $post_id, $width, $height, $options);	
}

	// get thumbs
	if ($enabled == 'true'){
		$type = tsfc_video_type($post_id);
		if ($type == 'vimeo_video' || $type == 'youtube_video'){
			//$post_thumbnail_id = get_post_thumbnail_id( $post_id );
			//wp_delete_post( $post_thumbnail_id, true );
			tsfc_check_add_thumb($post_id);
		}
	}


	//do remote url	
	if ($remote){
		$file_url = $_POST['remoteurl'];
			
		//remove others
		$rm = get_metadata('post', $post->ID, 'upload_attachment', true);
		wp_delete_attachment($rm, false);		
		delete_post_meta($post_id, 'upload_attachment');		
		delete_post_meta($post_id, 'vimeo_video');
		delete_post_meta($post_id, 'youtube_video');
		delete_post_meta($post_id, 'embed');
		//insert remote url
		update_post_meta( $post->ID, 'file_url', $file_url );	
	}

	if (isset($_POST['remove_it'])){
		delete_post_meta($post_id, $_POST['meta_field']);
		delete_post_meta($post_id, 'embed');
		delete_post_meta($post_id, 'width');
		delete_post_meta($post_id, 'height');
		delete_post_meta($post_id, 'default');
		$post_thumbnail_id = get_post_thumbnail_id( $post_id );
		wp_delete_post( $post_thumbnail_id, true );		
	}	

	if (isset($_POST['remove'])){
		foreach ($_POST['remove'] as $rm){
			wp_delete_attachment($rm, false);
			delete_post_meta($post_id, 'default');			
			delete_post_meta($post_id, 'upload_attachment', $rm);
		}
	}
			

	if(isset($_FILES['upload_attachment'])){	
		if ($_FILES ) {
			$files = $_FILES['upload_attachment'];
				foreach ($files['name'] as $key => $value) {
					if ($files['name'][$key]) {
						$file = array(
						'name' => $files['name'][$key],
						'type' => $files['type'][$key],
						'tmp_name' => $files['tmp_name'][$key],
						'error' => $files['error'][$key],
						'size' => $files['size'][$key]
						);
						
				$_FILES = array("upload_attachment" => $file);
					foreach ($_FILES as $file => $array) {
						$newupload = tsfc_attach($file,$post_id);
						$rm = get_metadata('post', $post->ID, 'upload_attachment', true);
							if ($rm){
								wp_delete_attachment($rm, false);
								delete_post_meta($post->ID, 'upload_attachment');	
							}
						delete_post_meta($post->ID, 'file_url');
						add_post_meta( $post->ID, $file, $newupload );				
					}
				}
			}
		}
	}	
}

function tsfc_generate_video($type, $video_id, $post_id, $width, $height, $options = ''){

	if ($type == 'youtube_video'){	
		$embed = '<iframe class="youtube-player" type="text/html" width="'.$width.'" height="'.$height.'" src="http://www.youtube.com/embed/'.$video_id.$options.'" frameborder="0"></iframe>';
		
	}else if ($type == 'vimeo_video'){	
			$embed = '<iframe src="http://player.vimeo.com/video/'.$video_id.'" width="'.$width.'" height="'.$height.'" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';	
	}
	
		update_post_meta( $post_id, 'embed', $embed );
}

function tsfc_generate_preview($type, $video_id, $post_id, $width, $height){

	if ($type == 'youtube_video'){
		
		$embed = '<iframe class="youtube-player" type="text/html" width="'.$width.'" height="'.$height.'" src="http://www.youtube.com/embed/'.$video_id.'" frameborder="0">
</iframe>';
	
	
	}else if ($type == 'vimeo_video'){
	
		$embed = '<iframe src="http://player.vimeo.com/video/'.$video_id.'" width="'.$width.'" height="'.$height.'" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';	
	}
	
		echo $embed;
}
function tsfc_showthumb($post_id){
	$thumb = get_the_post_thumbnail($post_id, 'thumbnail');
	return $thumb;
}

function tsfc_showfile($post_id){
$upload_dir = wp_upload_dir();
$upload_dir = $upload_dir[basedir].'/file-cabinet-templates/';

$plugin = plugin_dir_path(__FILE__);

	$args = array(
		'post_type' => 'attachment',
		'numberposts' => 1,
		'post_status' => 'attached',
		'post_parent' => $post_id
	); 
	$attachments = get_posts($args);

	$remote_url = get_metadata('post', $post_id, 'file_url', true);
	$vimeo_video = get_metadata('post', $post_id, 'vimeo_video', true);
	$youtube_video = get_metadata('post', $post_id, 'youtube_video', true);
	$embed = get_metadata('post', $post_id, 'embed', true);
	
	$title = get_the_title($post_id);
	
	$description = get_post($post_id);
	$description = $description->post_excerpt;
	$height = get_metadata('post', $post_id, 'height', true);
	$width = get_metadata('post', $post_id, 'width', true);	

	if ($remote_url){
		$type = 'remote';
	}else if ($vimeo_video){
		$type = 'vimeo';
	}else if ($youtube_video){
		$type = 'youtube';
	}else if ($attachments){
		$type = 'upload';
	}else{
		$type = 'none';
	}


	switch ($type){

		case remote:
			$remote_new = get_option("tsfc_remote_new");
				if ($remote_new == 'on'){
					$url = '<a target="_blank" href="'.$remote_url.'">'.$remote_url.'</a>';		
				}else{
					$url = '<a href="'.$remote_url.'">'.$remote_url.'</a>';		
				}
			//echo $title.'<br/>';
			echo $url;
		break;
		
		case vimeo:
			if (file_exists($upload_dir.'vimeo_template.txt')){
			$template = file_get_contents($upload_dir.'vimeo_template.txt');
			$template = str_replace('{width}', $width, $template);
			$template = str_replace('{height}', $height, $template);
			$template = str_replace('{title}', $title, $template);
			$template = str_replace('{description}', $description, $template);
			$template = str_replace('{video}', $embed, $template);
			echo $template;
			}else{
			$template = file_get_contents($plugin.'/templates/default_video_template.txt');
			$template = str_replace('{width}', $width, $template);
			$template = str_replace('{height}', $height, $template);
			$template = str_replace('{title}', $title, $template);
			$template = str_replace('{description}', $description, $template);
			$template = str_replace('{video}', $embed, $template);
			echo $template;
			}
		break;
		
		case youtube:
			if (file_exists($upload_dir.'youtube_template.txt')){
			$template = file_get_contents($upload_dir.'youtube_template.txt');
			$template = str_replace('{width}', $width, $template);
			$template = str_replace('{height}', $height, $template);
			$template = str_replace('{title}', $title, $template);
			$template = str_replace('{description}', $description, $template);
			$template = str_replace('{video}', $embed, $template);
			echo $template;
			}else{
			$template = file_get_contents($plugin.'/templates/default_video_template.txt');
			$template = str_replace('{width}', $width, $template);
			$template = str_replace('{height}', $height, $template);
			$template = str_replace('{title}', $title, $template);
			$template = str_replace('{description}', $description, $template);
			$template = str_replace('{video}', $embed, $template);
			echo $template;
			}
		break;
		
		case upload:
			foreach ($attachments as $attachment) {
				//$file = wp_get_attachment_image( $attachment->ID );
				$file = get_the_attachment_link($attachment->ID, false);

			}
				//echo $title.'<br/>';
				echo $file;
		break;
	}

}

function tsfc_dr_all_come($post_id){
$cat_check = tsfc_get_category_by_id($post_id);
	if ($cat_check == 'true'){
		$file_check = tsfc_file_check($post_id);
		if ($file_check == 'true'){
			tsfc_showfile($post->ID).'<br/>';
		}
	}
}

function tsfc_file_check($post_id){
	$file_role = get_post_meta($post_id, 'allowed_role', true);
	$file_role = unserialize($file_role);
	
	if ( is_user_logged_in() ) {
	$user_role = tsfc_current_user_role();
	
		if (in_array($user_role, $file_role) || in_array('everyone', $file_role)){
			$permission = true;
		}else{
			$permission = false;
		}
		
	}else if (in_array('everyone', $file_role)){
			$permission = true;
	}else{
			$permission = false;
	}
			return $permission;
}


function tsfc_category_check($term_id){
	$category_role = tsfc_get_category_permissions($term_id);

	if ( is_user_logged_in() ) {
	$user_role = tsfc_current_user_role();
	
		if (in_array($user_role, $category_role) || in_array('everyone', $category_role)){
			$permission = true;
		}else{
			$permission = false;
		}
		
	}else if (in_array('everyone', $category_role)){
			$permission = true;
	}else{
			$permission = false;
	}
			return $permission;
}

function tsfc_get_category_permissions($term_id){
	$category_role = get_option( "taxonomy_".$term_id );
	$category_role = unserialize($category_role);
return $category_role;
}

function tsfc_get_category_by_id($post_id){
$args = array(
	'type'                     => 'tsfc_files',
	'orderby'                  => 'name',
	'order'                    => 'ASC',
	'hide_empty'               => 0,
	'hierarchical'             => 0,
	'exclude'				   => 1,
	'taxonomy'                 => 'tsfc_category',
	'pad_counts'               => false );
//exclude cat 1 because we dont count uncategorized files

$categories = get_categories( $args );
$current_category = wp_get_object_terms( $post_id, 'tsfc_category');
$term_id = $current_category[0]->term_id;
$current_category = $current_category[0]->name;
// retrieve the existing value(s) for this meta field. This returns an array
$category_role = get_option( "taxonomy_$term_id" );
$category_role = unserialize($category_role);

return $category_role;
}

function tsfc_current_user_role () {
    global $current_user;
    get_currentuserinfo();
    $user_roles = $current_user->roles;
    $user_role = array_shift($user_roles);
    return $user_role;
};

function tsfc_video_type($post_id){
$vimeo = get_post_meta($post_id, 'vimeo_video', true);
$youtube = get_post_meta($post_id, 'youtube_video', true);	
	if ($vimeo){
		$type = 'vimeo_video';	
	}

	if ($youtube){		
	$type = 'youtube_video';	
	}	
return $type;
}

function tsfc_check_add_thumb($post_id){
	
		$video_thumbnail = get_the_post_thumbnail($post_id,'video-thumbnail');		

		if (empty($video_thumbnail)){		
			$type = tsfc_video_type($post_id);			
			if ($type == 'vimeo_video'){				
				$vimeo_id = get_post_meta($post_id,'vimeo_video',true);
				$hash = unserialize(@file_get_contents("http://vimeo.com/api/v2/video/$vimeo_id.php"));
				if ($hash) {
					$imageurl = $hash[0]['thumbnail_large'];
				}
					tsfc_thumb_attach($post_id, $imageurl);
			}

			if ($type == 'youtube_video'){
				$youtube_id = get_post_meta($post_id,'youtube_video',true);	
				$imageurl = 'http://img.youtube.com/vi/'.$youtube_id.'/0.jpg';	
				tsfc_thumb_attach($post_id, $imageurl);
			}
		}
}

function tsfc_thumb_attach($post_id, $imageurl){		
$imageurl = stripslashes($imageurl);		
$uploads = wp_upload_dir();		
$filename = wp_unique_filename( $uploads['path'], basename($imageurl), $unique_filename_callback = null );		
$wp_filetype = wp_check_filetype($filename, null );		
$fullpathfilename = $uploads['path'] . "/" . $filename;					
$image_string = tsfc_fetch_image($imageurl);		
$fileSaved = file_put_contents($uploads['path'] . "/" . $filename, $image_string);	
				
	$attachment = array(				 
	'post_mime_type' => $wp_filetype['type'],				 
	'post_title' => preg_replace('/\.[^.]+$/', '', $filename),				 
	'post_content' => '',				 
	'post_status' => 'inherit',				 
	'guid' => $uploads['url'] . "/" . $filename			
	);
require_once(ABSPATH . "wp-admin" . '/includes/image.php');

$attach_id = wp_insert_attachment( $attachment, $fullpathfilename, $post_id );
$attach_data = wp_generate_attachment_metadata( $attach_id, $fullpathfilename );
wp_update_attachment_metadata( $attach_id,  $attach_data );
update_post_meta( $post_id,'_thumbnail_id',$attach_id);

}

function tsfc_fetch_image($url) {
	if ( function_exists("curl_init") ) {	
		return tsfc_curl_fetch_image($url);
	} elseif ( ini_get("allow_url_fopen") ) {	
		return tsfc_fopen_fetch_image($url);
	}
}	

function tsfc_curl_fetch_image($url) {
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$image = curl_exec($ch);curl_close($ch);
return $image;	
}	

function tsfc_fopen_fetch_image($url) {
$image = file_get_contents($url, false, $context);
return $image;	
}

function tsfc_odd_check($number) {
	if ( $number&1 )
		return true;
	return false; 
}


//check for uprade routines and do upgrade procedure if needed
register_activation_hook( __FILE__, 'tsfc_tst_act' );

function tsfc_get_version() {
$plugin_data = get_plugin_data( __FILE__ );
$plugin_version = $plugin_data['Version'];
return $plugin_version;
}

add_action('admin_init','tsfc_tst_chk');
function tsfc_tst_chk() {
	$last_known_version = get_option('tsfc_version');	
	$current_version = tsfc_get_version();	
	//we check here if the current version is same as old version this will run every page load so we ensure we catch upgrades
	//being a simple comparison it does not hurt to run on every page load
	
	if ( $last_known_version != $current_version && $last_known_version != '' ) {
		update_option( "tsfc_version", $current_version );
		
		//this should be changed or disabled with every version change.
		//this is only called or used when we need to actually adjust something on their site during the update otherwise we comment out the call to it
		update_routine();
	}
	
	
}

function tsfc_tst_act(){
	$version = tsfc_get_version();
	update_option("tsfc_version", $version);
}


//this should be changed or disabled with every version change.
//this is only called or used when we need to actually adjust something on their site during the update otherwise we comment out the call to it
function update_routine(){

	update_option("fc_nameS", 'File');
	update_option("fc_nameP", 'Files');
	update_option("fc_slug", 'my_files');
	update_option("fc_desc", 'simple file cabinet');
	update_option("fc_public_query", 'true');
	update_option("fc_auto_thumb", 'true');
	update_option("fc_permissions", 'false');
	update_option("fc_video_width", '800');
	update_option("fc_video_height", '600');		
}