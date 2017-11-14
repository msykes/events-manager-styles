<?php
/*
Plugin Name: Events Manager - Example Add-on - Styles
Plugin URI: http://wp-events-plugin.com
Description: Add styles to your events
Author: Events Manager
Author URI: http://wp-events-plugin.com
Version: 1.0
*/

/**
 * Create a submenu item within the Events Manager menu. 
 * In Multisite Global Mode, the admin menu will only appear on the main blog, this can be changed by modifying the first line of code in this function.
 */
function my_em_styles_submenu () {
	$ms_global_mode = !EM_MS_GLOBAL || is_main_site();
	if(function_exists('add_submenu_page') && ($ms_global_mode) ) {
   		$plugin_page = add_submenu_page('edit.php?post_type='.EM_POST_TYPE_EVENT, 'Event Styles', 'Styles', 'edit_events', "events-manager-styles", 'my_em_admin_styles_page');
  	}
}
add_action('admin_menu','my_em_styles_submenu', 20);

/**
 * Registers the my_em_styles option as a global option for when in MS GLobal mode
 * @param array $globals
 * @return array
 */
function my_em_styles_ms_globals( $globals ){
	$globals[] = 'my_em_styles';
	return $globals;	
}
add_filter('em_ms_globals', 'my_em_styles_ms_globals', 10, 1);

/**
 * Handle and output the styles admin page 
 */
function my_em_admin_styles_page() {      
	global $wpdb, $EM_Event, $EM_Notices, $my_em_styles;
	$my_em_styles = is_array(get_option('my_em_styles')) ? get_option('my_em_styles'):array() ;
	if( !empty($_REQUEST['action']) ){
		if( $_REQUEST['action'] == "style_save" && wp_verify_nonce($_REQUEST['_wpnonce'], 'style_save') ) {
			//Just add it to the array or replace
			if( !empty($_REQUEST['style_id']) && array_key_exists($_REQUEST['style_id'], $my_em_styles) ){
				//A previous style, so we just update
				$my_em_styles[$_REQUEST['style_id']] = $_REQUEST['style_name'];
				$EM_Notices->add_confirm('Style Updated');
			} else {
				//A new style, so we either add it to the end of the array, or start it off at index 1 if it's the first item to be added.
				if( count($my_em_styles) > 0 ){
					$my_em_styles[] = $_REQUEST['style_name'];
				}else{
					$my_em_styles[1] = $_REQUEST['style_name'];
				}			
				$EM_Notices->add_confirm('Style Added');
			}
			update_option('my_em_styles',$my_em_styles);
		} elseif( $_REQUEST['action'] == "style_delete" && wp_verify_nonce($_REQUEST['_wpnonce'], 'style_delete') ){
			//Unset the style from the array and save
			if(is_array($_REQUEST['styles'])){
				foreach($_REQUEST['styles'] as $id){
					unset($my_em_styles[$id]);
				}
				update_option('my_em_styles',$my_em_styles);	
				$EM_Notices->add_confirm('Styles Deleted');
			}
		}
	}
	my_em_styles_table_layout();
} 

/**
 * Outputs the admin area for adding and removing Styles. For purposes of this tutorial, we have a custom interface using WP-style tables.
 * If you wanted to create exactly this sort of scenario, we'd recommend using taxonomies, as the interface is almost identical, and the storage mechanism has more potential for further integration and efficiency.
 */
function my_em_styles_table_layout() {
	global $EM_Notices, $my_em_styles;
	?>
	<div class='wrap'>
		<div id='icon-edit' class='icon32'>
			<br/>
		</div>
  		<h2>Styles</h2>	 		
		<?php echo $EM_Notices; ?>		
		<div id='col-container'>
			<!-- begin col-right -->   
			<div id='col-right'>
			 	<div class='col-wrap'>       
				 	 <form id='bookings-filter' method='post' action=''>
						<input type='hidden' name='action' value='style_delete'/>
						<input type='hidden' name='_wpnonce' value='<?php echo wp_create_nonce('style_delete'); ?>' />
						<?php if (count($my_em_styles)>0) : ?>
							<table class='widefat'>
								<thead>
									<tr>
										<th class='manage-column column-cb check-column' scope='col'><input type='checkbox' class='select-all' value='1'/></th>
										<th><?php echo __('ID', 'dbem') ?></th>
										<th><?php echo __('Name', 'dbem') ?></th>
									</tr> 
								</thead>
								<tfoot>
									<tr>
										<th class='manage-column column-cb check-column' scope='col'><input type='checkbox' class='select-all' value='1'/></th>
										<th><?php echo __('ID', 'dbem') ?></th>
										<th><?php echo __('Name', 'dbem') ?></th>
									</tr>             
								</tfoot>
								<tbody>
									<?php foreach ($my_em_styles as $style_id => $style) : ?>
									<tr>
										<td><input type='checkbox' class ='row-selector' value='<?php echo $style_id ?>' name='styles[]'/></td>
										<td><a href='<?php echo get_bloginfo('wpurl') ?>/wp-admin/admin.php?page=events-manager-styles&amp;action=edit&amp;style_id=<?php echo $style_id ?>'><?php echo $style_id; ?></a></td>
										<td><a href='<?php echo get_bloginfo('wpurl') ?>/wp-admin/admin.php?page=events-manager-styles&amp;action=edit&amp;style_id=<?php echo $style_id ?>'><?php echo htmlspecialchars($style, ENT_QUOTES); ?></a></td>
									</tr>
									<?php endforeach; ?>
								</tbody>
	
							</table>
	
							<div class='tablenav'>
								<div class='alignleft actions'>
							 	<input class='button-secondary action' type='submit' name='doaction2' value='Delete'/>
								<br class='clear'/> 
								</div>
								<br class='clear'/>
							</div>
						<?php else: ?>
							<p>No styles inserted yet!</p>
						<?php endif; ?>
					</form>
				</div>
			</div>
			<!-- end col-right -->     
			
			<!-- begin col-left -->
			<div id='col-left'>
		  		<div class='col-wrap'>
					<div class='form-wrap'> 
						<div id='ajax-response'>
					  		<h3><?php echo empty($_REQUEST['style_id']) ? 'Add':'Update'; ?> Style</h3>
							<form name='add' id='add' method='post' action='' class='add:the-list: validate'>
								<input type='hidden' name='action' value='style_save' />
								<input type='hidden' name='_wpnonce' value='<?php echo wp_create_nonce('style_save'); ?>' />
								<div class='form-field form-required'>
									<label for='style-name'>Style Name</label>
									<?php if( !empty($_REQUEST['style_id']) && array_key_exists($_REQUEST['style_id'], $my_em_styles)): ?>
									<input id='style-name' name='style_name' type='text' size='40' value="<?php echo $my_em_styles[$_REQUEST['style_id']]; ?>" />
									<input id='style-id' name='style_id' type='hidden' value="<?php echo $_REQUEST['style_id']; ?>" />
									<?php else: ?>
									<input id='style-name' name='style_name' type='text' size='40' />
									<?php endif; ?>
								</div>
								<p class='submit'>
									<?php if( !empty($_REQUEST['style_id']) ): ?>
									<input type='submit' class='button' name='submit' value='Update Style' />
									or <a href="admin.php?page=events-manager-styles">Add New</a>
									<?php else: ?>
									<input type='submit' class='button' name='submit' value='Add Style' />
									<?php endif; ?>
								</p>
							</form>
					  	</div>
					</div> 
				</div>    
			</div> 
			<!-- end col-left --> 		
		</div> 
  	</div>
  	<?php
}

/**
 * Add the styles array to your $EM_Event object upon instantiation hook
 * @param EM_Event $EM_Event
 */
function my_em_styles_event_load($EM_Event){
	global $wpdb;
	$sql = $wpdb->prepare("SELECT meta_value FROM ".EM_META_TABLE." WHERE object_id=%s AND meta_key='event-style'", $EM_Event->event_id);
	/* Uncomment this section, and you can get rid of both the my_em_styles_event_save_events and my_em_styles_event_delete_events functions.
	if( $EM_Event->is_recurrence() ){
		$sql = $wpdb->prepare("SELECT meta_value FROM ".EM_META_TABLE." WHERE object_id=%s AND meta_key='event-style'", $EM_Event->recurrence_id);
	}
	*/
	$EM_Event->styles = $wpdb->get_col($sql, 0);
}
add_action('em_event','my_em_styles_event_load',1,1);

/**
 * Add the checkboxes within the styles metabox
 */
function my_em_styles_metabox(){
	global $EM_Event;
	$my_em_styles = (is_array(get_option('my_em_styles'))) ? get_option('my_em_styles'):array();
	foreach( $my_em_styles as $style_id => $style ){ 
		?>
		<label>
		<input type="checkbox" name="event_styles[]" value="<?php echo $style_id; ?>" <?php if(in_array($style_id, $EM_Event->styles)) echo 'checked="checked"'; ?> /> 
		<?php echo $style ?>
		</label><br />			
		<?php
	}
}

/**
 * Declares the meta boxes for both event and recurring events
 */
function my_em_styles_meta_boxes(){
	add_meta_box('em-event-styles', 'Styles', 'my_em_styles_metabox',EM_POST_TYPE_EVENT, 'side','low');
	add_meta_box('em-event-styles', 'Styles', 'my_em_styles_metabox','event-recurring', 'side','low');
}
add_action('add_meta_boxes', 'my_em_styles_meta_boxes');

/**
 * Outputs the styles checkboxes in the front-end form, which essentially wraps the meta box options in the admin area within a front-end form friendly html.
 */
function my_em_styles_frontend_form_input(){
	?>
	<fieldset>
		<legend>Styles</legend>
		<?php my_em_styles_metabox(); ?>
	</div>
	<?php
}
add_action('em_front_event_form_footer', 'my_em_styles_frontend_form_input');

/**
 * Saves the styles meta to the database for this event.
 * @param bool $result
 * @param EM_Event $EM_Event
 * @return bool
 */
function my_em_styles_event_save($result,$EM_Event){
	global $wpdb;
	//First delete any old saves
	$wpdb->query("DELETE FROM ".EM_META_TABLE." WHERE object_id='{$EM_Event->event_id}' AND meta_key='event-style'");
	if( $EM_Event->event_id && !empty($_POST['event_styles']) ){
		$my_em_styles = (is_array(get_option('my_em_styles'))) ? get_option('my_em_styles'):array();
		$ids_to_add = array();
		$EM_Event->styles = array();
		foreach( $_POST['event_styles'] as $style_id ){
			if( array_key_exists($style_id, $my_em_styles) ){
				$ids_to_add[] = "({$EM_Event->event_id}, 'event-style', '$style_id')";
				$EM_Event->styles[] = $style_id;
			}
		}
		if( count($ids_to_add) > 0 ){
			$wpdb->query("INSERT INTO ".EM_META_TABLE." (object_id, meta_key, meta_value) VALUES ".implode(',',$ids_to_add));
		}
	}
	return $result;
}
add_filter('em_event_save','my_em_styles_event_save',1,2);

/**
 * Saves the style meta for each event created in a recurring event set.
 * @param boolean $result
 * @param EM_Event $EM_Event
 * @param array $event_ids
 * @return boolean
 */
function my_em_styles_event_save_events($result, $EM_Event, $event_ids){
	if( $result ){
		global $wpdb;
		array_walk($event_ids, 'absint'); //absint here as $event_ids is used in both insert and delete
		//first, delete all the previous data in case we've changed the styles
		$wpdb->query('DELETE FROM '.EM_META_TABLE.' WHERE object_id IN (' . implode(',', $event_ids).") AND meta_key='event-style'");
		//build array of inserts
		$inserts = array();
		foreach( $EM_Event->styles as $style_id ){
			foreach( $event_ids as $event_id ){
				$inserts[] = "(".$event_id.", 'event-style', ".absint($style_id).")";
			}
		}
		//join inserts into one insert statement, it's faster
		if( !empty($inserts) ){
			$wpdb->query("INSERT INTO ".EM_META_TABLE." (object_id, meta_key, meta_value) VALUES " . implode(',', $inserts));
		}
	}
	return $result;
}
add_filter('em_event_save_events', 'my_em_styles_event_save_events', 10, 3);

/**
 * Deletes the style meta for recurring events when they have been deleted.
 * @param boolean $result
 * @param EM_Event $EM_Event
 * @param array $event_ids
 * @return boolean
 */
function my_em_event_delete_events($result, $EM_Event, $event_ids){
	global $wpdb;
    $wpdb->query("DELETE FROM ".EM_META_TABLE." WHERE meta_key='event-style' AND object_id IN (".implode(',',$event_ids).")");
    return $result;
}
add_filter('em_event_delete_events','my_em_event_delete_events',1,3);


/**
 * Deletes the style meta for an event when it has been deleted.
 * @param boolean $result
 * @param EM_Event $EM_Event
 * @return boolean
 */
function my_em_event_delete_meta($result, $EM_Event){
	global $wpdb;
	$sql = $wpdb->prepare("DELETE FROM ".EM_META_TABLE." WHERE meta_key='event-style' AND object_id = %d", $EM_Event->event_id);
	$wpdb->query($sql);
	return $result;
}
add_filter('em_event_delete_meta','my_em_event_delete_meta',1,3);


//search stuff for the style
/**
 * Generate a field in the search form.
 */
function my_em_styles_search_form(){
	$my_em_styles = (is_array(get_option('my_em_styles'))) ? get_option('my_em_styles'):array();
	?>
	<!-- START Styles Search -->
	<div class="em-search-field">
		<label>
			<span>Styles</span>
			<select name="style">
				<option value=''>All Styles</option>
				<?php foreach($my_em_styles as $style_id=>$style_name): ?>
				 <option value="<?php echo $style_id; ?>" <?php echo (!empty($_REQUEST['style']) && $_REQUEST['style'] == $style_id) ? 'selected="selected"':''; ?>><?php echo $style_name; ?></option>
				<?php endforeach; ?>
			</select>
		</label>
	</div>
	<!-- END Styles Search -->
	<?php
}
add_action('em_template_events_search_form_footer', 'my_em_styles_search_form');

/**
 * Add 'style' to the list of accepted searches.
 * @param array $searches
 * @return array
 */
function my_em_styles_accepted_searches($searches){
	$searches[] = 'style';
	return $searches;
}
add_filter('em_accepted_searches','my_em_styles_accepted_searches',1,1);

/**
 * Adds 'style' to the search options in shortcode etc.
 * @param array $args
 * @param array $array
 * @return array
 */
function my_em_styles_get_default_search($args, $array){
	$args['style'] = false; //registers 'style' as an acceptable value, although set to false by default
	if( !empty($array['style']) && is_numeric($array['style']) ){
		$args['style'] = $array['style'];
	}
	return $args;
}
add_filter('em_events_get_default_search','my_em_styles_get_default_search',1,2);
add_filter('em_calendar_get_default_search','my_em_styles_get_default_search',1,2);
add_filter('em_locations_get_default_search','my_em_styles_get_default_search',1,2);

/**
 * Add an sql condition for searching styles.
 * @param array $conditions
 * @param array $args
 * @return array
 */
function my_em_styles_events_build_sql_conditions($conditions, $args){
	global $wpdb;
	if( !empty($args['style']) && is_numeric($args['style']) ){
		$sql = $wpdb->prepare("SELECT object_id FROM ".EM_META_TABLE." WHERE meta_value=%s AND meta_key='event-style'", $args['style']);
		$conditions['style'] = "event_id IN ($sql)";
	}
	return $conditions;
}
add_filter( 'em_events_build_sql_conditions', 'my_em_styles_events_build_sql_conditions',1,2);
add_filter( 'em_locations_build_sql_conditions', 'my_em_styles_events_build_sql_conditions',1,2);

function my_em_styles_locations_get_join_events_table( $join, $args ){
	if( !empty($args['style']) ){
		return true;
	}
	return $join;
}
add_filter('em_locations_get_join_events_table', 'my_em_styles_locations_get_join_events_table', 10, 2);

/**
 * Add a conditional placeholder for styles within event formats.
 * @param bool $show
 * @param string $condition
 * @param string $full_match
 * @param EM_Event $EM_Event
 * @return string
 */
function my_em_styles_event_output_show_condition($show, $condition, $full_match, $EM_Event){
	//check if there is a conditional tag for a specific style id
	if( !empty( $EM_Event->styles ) && preg_match('/^has_style_(.+)$/',$condition, $matches) ){
		if( is_array($EM_Event->styles) && in_array($matches[1],$EM_Event->styles) ){
			//this event has the desired style id, so show the replacement, which at this point $replacement would usually be false
			$show = true;
		}
	}
	return $show;
}
add_action('em_event_output_show_condition', 'my_em_styles_event_output_show_condition', 1, 4);

/**
 * Add a rewrite rule to create a page for styles.
 * @param array $rules
 * @return array
 */
function my_em_styles_rewrite_rules_array($rules){
	//get the slug of the event page
	$events_page_id = get_option ( 'dbem_events_page' );
	$events_slug = urldecode(preg_replace('/\/$/', '', str_replace( trailingslashit(home_url()), '', get_permalink($events_page_id)) ));
	$events_slug = ( !empty($events_slug) ) ? trailingslashit($events_slug) : $events_slug;
	$my_em_rules = array();
	if( !empty($events_slug) ){
		$my_em_rules[$events_slug.'styles$'] = 'index.php?pagename='.$events_slug.'&styles_page=1'; //styles list
		$my_em_rules[$events_slug.'style/(\d+)$'] = 'index.php?pagename='.$events_slug.'&style_id=$matches[1]'; //single style
	}
	return $my_em_rules + $rules;
}
add_filter('rewrite_rules_array','my_em_styles_rewrite_rules_array');

/**
 * Add the style query variable for WP Queries.
 * @param array $vars
 * @return array
 */
function my_em_styles_query_vars($vars){
	array_push($vars, 'style_id','styles_page');
    return $vars;
}
add_filter('query_vars','my_em_styles_query_vars');

/**
 * Replace the styles page with content.
 * @param string $content
 * @return string
 */
function my_em_styles_content($content){
	global $wp_query;
	$my_em_styles = (is_array(get_option('my_em_styles'))) ? get_option('my_em_styles'):array();
	if( is_numeric($wp_query->get('style_id')) && !empty($my_em_styles[$wp_query->get('style_id')]) ){
		$content = "<p>Events with the {$my_em_styles[$wp_query->get('style_id')]} style</p>";
		$content .= EM_Events::output(array('style'=>$wp_query->get('style_id')));
	}elseif($wp_query->get('styles_page')){
		$content ='';
		foreach($my_em_styles as $style_id => $style_name){
			$content .= "<h4>$style_name</h4>";
			$content .= EM_Events::output(array('style'=>$style_id)); 			
		}
	}
	return $content;
}
add_filter('em_content_pre','my_em_styles_content');

/**
 * Add a page title to the public styles page.
 * @param string $content
 * @return string
 */
function my_em_styles_content_page_title_pre($content){
	global $wp_query;
	$my_em_styles = (is_array(get_option('my_em_styles'))) ? get_option('my_em_styles'):array();
	if( is_numeric($wp_query->get('style_id')) ){
		$content = $my_em_styles[$wp_query->get('style_id')];
	}elseif($wp_query->get('styles_page')){
		$content ='Styles';
	}
	return $content;
}
add_filter('em_content_page_title_pre','my_em_styles_content_page_title_pre');

/**
 * Add a custom placeholder to display the styles of this event.
 * @param string $replace
 * @param EM_Event $EM_Event
 * @param string $result
 * @return string
 */
function my_em_styles_placeholders($replace, $EM_Event, $result){
	if( $result == '#_STYLES' ){
		$replace = 'none';
		if( count($EM_Event->styles) > 0 ){
			$my_em_styles = (is_array(get_option('my_em_styles'))) ? get_option('my_em_styles'):array();
			$styles = array();
			foreach( $EM_Event->styles as $id ){
				if( !empty($my_em_styles[$id]) ){
					$styles[] = $my_em_styles[$id];
				}
			}
			$replace = implode(', ', $styles);
		}
	}
	return $replace;
}
add_filter('em_event_output_placeholder','my_em_styles_placeholders',1,3);

?>