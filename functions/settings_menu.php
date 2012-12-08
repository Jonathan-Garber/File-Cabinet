<?php
	if (isset($_POST['save_settings'])){		
		$fc_nameS = $_POST['fc_nameS'];
		$fc_nameP = $_POST['fc_nameP'];
		$fc_slug = $_POST['fc_slug'];
		$fc_slug = str_replace(" ", "_", $fc_slug);
		$fc_slug = preg_replace("/[^\w\.]/","",$fc_slug);
		$fc_slug = strtolower($fc_slug);
		$fc_desc = $_POST['fc_desc'];
		$fc_public_query = $_POST['fc_public_query'];
		$fc_auto_thumb = $_POST['fc_auto_thumb'];
		$fc_permissions = $_POST['fc_permissions'];
		$fc_video_width = $_POST['fc_video_width'];
		$fc_video_height = $_POST['fc_video_height'];				
		$post_types_all = $_POST['post_types_all'];					
		
		if ($post_types_all == 'on'){				
		update_option("fc_metabox_types", 'all');			
		}else{				
		$post_types = $_POST['post_types'];				
		update_option("fc_metabox_types", $post_types);			
		}
		
		update_option("fc_nameS", $fc_nameS);
		update_option("fc_nameP", $fc_nameP);
		update_option("fc_slug", $fc_slug);
		update_option("fc_desc", $fc_desc);
		update_option("fc_public_query", $fc_public_query);
		update_option("fc_auto_thumb", $fc_auto_thumb);
		update_option("fc_permissions", $fc_permissions);
		update_option("fc_video_width", $fc_video_width);
		update_option("fc_video_height", $fc_video_height);
		tsfc_register_posts();
		flush_rewrite_rules();
		
		$resp = 'Your settings have been updated.';
	}
	
	if (isset($_POST['do_video_defaults'])){
		if (isset($_POST['fc_video_width']) || isset($_POST['fc_video_height'])){	
		$width = $_POST['fc_video_width'];
		$height = $_POST['fc_video_height'];
		$update_all_video = tsfc_update_embeds($width, $height);
		update_option("tsfc_default_width", $_POST['fc_video_width']);
		update_option("tsfc_default_height", $_POST['fc_video_height']);
		$resp = 'All video embeds are now set to the default width and height.';
		}
	}
	
	function tsfc_update_embeds($width, $height){
		$args = array(
			'meta_key'        => 'vimeo_video',
			'post_type'       => 'tsfc_files',
			'post_status'     => 'publish' );
	
		$vimeo = get_posts($args);
			foreach ($vimeo as $v){
					update_post_meta( $v->ID, 'width', $width );	
					update_post_meta( $v->ID, 'height', $height );
					$vimeo_url = get_post_meta($v->ID, 'vimeo_video', true);
					tsfc_generate_video('vimeo_video', $vimeo_url, $v->ID, $width, $height);				
			}
	
		$args2 = array(
			'meta_key'        => 'youtube_video',
			'post_type'       => 'tsfc_files',
			'post_status'     => 'publish' );
	
		$youtube = get_posts($args2);
			foreach ($youtube as $t){
					update_post_meta( $t->ID, 'width', $width );	
					update_post_meta( $t->ID, 'height', $height );
					$youtube_url = get_post_meta($t->ID, 'youtube_video', true);
					tsfc_generate_video('youtube_video', $youtube_url, $t->ID, $width, $height);						
			}
	}
	
	
	$fc_nameS = get_option( "fc_nameS" );
	$fc_nameP = get_option( "fc_nameP" );
	$fc_slug = get_option( "fc_slug" );
	$fc_desc = get_option( "fc_desc" );
	$fc_public_query = get_option( "fc_public_query" );
	$fc_video_width = get_option( "fc_video_width" );
	$fc_video_height = get_option( "fc_video_height" );
	$fc_auto_thumb = get_option( "fc_auto_thumb" );
	$fc_permissions = get_option( "fc_permissions" );		$current_post_types = get_option( "fc_metabox_types" );
?>

<div class="wrap file-cabinet settings">
	<div class="title">
		<div id="icon-options-general" class="icon32"></div>
		<h2>File Cabinet Settings</h2>
	</div>
	
	<?php if ( $resp ) : ?>
	<div id="setting-error-settings_updated" class="updated settings-error"> 
		<p><strong><?php echo $resp; ?></strong></p>
	</div>
	<?php endif; ?>

	<form method="POST">
	
		<table class="form-table">
	
			<?php
				// Jonathan, I changed this area so users can set up the plural, singular, slug, description and whether or not the custom post type is publicly queryable, you'll need to make it work
				// Defaults can be (in order of appearance): Files, File, fc-files, (blank), no
			?>
			<!-- old code, left for reference, remove when you're done with it
			File Cabinet Name: <input id="fc_name" name="fc_name" type="text" value="<?php echo $fc_name ?>"/><br/><br/>		<input onClick="return confirm('This will change the permalink structure for your file cabinet and update how it appears in the admin menu. Do you wish to continue?');" type="submit" name="do_name" value="Change File Cabinet Name">
			-->
			
			<tr>
				<th><h3>Custom Post Type</h3></th>
				<th><p><em>File Cabinet stores your items in a WordPress Custom Post Type. Set the details for it here.</em></p></th>
			</tr>
			<tr>
				<th scope="row">Custom Post Type Name (Singular)</th>
				<td>
					<input id="fc_cpt_name_singular" name="fc_nameS" type="text" value="<?php echo $fc_nameS ?>" size="25">
				</td>
			</tr>			
			<tr>
				<th scope="row">Custom Post Type Name (Plural)</th>
				<td>
					<input id="fc_cpt_name_plural" name="fc_nameP" type="text" value="<?php echo $fc_nameP ?>" size="25">
				</td>
			</tr>
			<tr>
				<th scope="row">Custom Post Type Slug</th>
				<td>
					<input id="fc_cpt_slug" name="fc_slug" type="text" value="<?php echo $fc_slug ?>" size="17">
				</td>
			</tr>
			<tr>
				<th scope="row">Description</th>
				<td>
					<textarea id="fc_cpt_description" name="fc_desc" rows="7" cols="50" type='textarea'><?php echo $fc_desc ?></textarea>
				</td>
			</tr>
			<tr>
				<th scope="row">Publicly Queryable</th>
				<td>
					<label>
						<input name="fc_public_query" type="radio" <?php if ($fc_public_query == 'true') { echo 'checked="true"'; }?> value="true"> Yes
					</label><br>
					<label>
						<input name="fc_public_query" type="radio" <?php if ($fc_public_query == 'false') { echo 'checked="true"'; }?> value="false"> No
					</label>
				</td>
			</tr>

			<tr>
				<th><h3>File Cabinet Shortcodes Metabox</h3></th>
				<th><p><em>Choose which post types' editors get the Shortcodes metabox.</em></p></th>
			</tr>
			<tr>
				<th scope="row">Post Types</th>
				<td class="fc-post-type-section">
					<?php 
						$args=array(
						'public'   => true,
						'_builtin' => false,
						);
 						$output = 'names';
						$operator = 'and';
						$post_types = get_post_types($args,$output,$operator);
						$post_types[] = 'post';
						$post_types[] = 'page';
							foreach ($post_types  as $post_type ) {
							if ($post_type != 'tsfc_files'){
								if (is_array($current_post_types)){
								if (in_array($post_type, $current_post_types)){
						?>
									<span><input checked="checked" class="post-type" name="post_types[]" type="checkbox" value="<?php echo $post_type ?>"/> <?php echo ucfirst($post_type) ?></span><br />
						<?php 	}else{ ?>
									<span><input class="post-type" name="post_types[]" type="checkbox" value="<?php echo $post_type ?>"/> <?php echo ucfirst($post_type) ?></span><br />
						<?php 	}
									}else{
						?>
									<span><input class="post-type" name="post_types[]" type="checkbox" value="<?php echo $post_type ?>"/> <?php echo ucfirst($post_type) ?></span><br />						
						<?php
								}
							}
						}
						?>
						
					<span><input <?php if ($current_post_types == 'all'){ echo 'checked="checked"'; } ?> class="all" name="post_types_all" type="checkbox"/> All</span>
				</td>
			</tr>

			<tr>
				<th><h3>Videos</h3></th>
				<th><p><em>Set video options here.</em></p></th>
			</tr>
			<tr>
				<?php // defualt to 640 ?>
				<th scope="row">Default Width</th>
				<td>
					<?php // let's validate that these are just numbers ?>
					<input id="fc_video_default_width" name="fc_video_width" type="text" value="<?php echo $fc_video_width ?>" size="5">px<br>
					<span>Set the default width in pixels. Leave either option plank to have the plugin calculate the blank option via aspect ratio.</span>
				</td>
			</tr>
			<tr>
				<?php // default to blank ?>
				<th scope="row">Default Height</th>
				<td>
					<?php // let's validate that these are just numbers ?>
					<input id="fc_video_default_height" name="fc_video_height" type="text" value="<?php echo $fc_video_height ?>" size="5">px<br>
					<span>Set the default height. Leave either option plank to have the plugin calculate the blank option via aspect ratio.</span>
				</td>
			</tr>
			<tr>
				<td>Reset height and width setting of all videos with current.</td>
				<td>
					<input class="button-secondary" onClick="return confirm('This will overwrite the width and height settings for all videos.');" type="submit" name="do_video_defaults" value="<?php _e("Update Videos"); ?>">
				</td>
			</tr>
			<tr>
				<th scope="row">Thumbnail Auto Download</th>
				<td>
					<label>
						<input name="fc_auto_thumb" type="radio" <?php if ($fc_auto_thumb == 'true') { echo 'checked="true"'; }?> value="true"> On
					</label><br>
					<label>
						<input name="fc_auto_thumb" type="radio" <?php if ($fc_auto_thumb == 'false') { echo 'checked="true"'; }?> value="false"> Off
					</label><br>
					<span>File Cabinet can attempt automatically download video thumbnails from YouTube or Vimeo. If enabled, the thumbnail will be inserted as teh post thumbnail if none exists when the loop runs.</span>
				</td>
			</tr>	
			<tr>
				<th><h3>Permissions</h3></th>
				<th><p><em>File Cabinet's Permissions capabilities must be implemented by a developer. If enabled, please read the documentation to learn how to implement this feature.</em></p></th>
			</tr>
			<tr>
				<th scope="row">Permissions</th>
				<td>
					<?php // off by default, the only thing turning this off does is make it so the permissions settings aren't visible to users, it still works the same way all the time ?>
					<label>
						<input name="fc_permissions" type="radio" <?php if ($fc_permissions == 'true') { echo 'checked="true"'; }?> value="true"> On
					</label><br>
					<label>
						<input name="fc_permissions" type="radio" <?php if ($fc_permissions == 'false') { echo 'checked="true"'; }?> value="false"> Off
					</label><br>
					<span>This setting enables or disables the ability for users to set permissions when editing files or categories.</span>
				</td>
			</tr>
			
			<tr>
				<td>
					<input class="button-primary" type="submit" name="save_settings" value="<?php _e("Save Settings") ?>">	
				</td>
			</tr>
	
		</table>
	
	</form>

</div>
