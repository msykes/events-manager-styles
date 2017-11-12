<?php
/*
Plugin Name: Events Manager - Event Styles
Plugin URI: http://wp-events-plugin.com
Description: Add styles to your events
Author: Events Manager
Author URI: http://wp-events-plugin.com
Version: 1.0
*/

/**
 * Create a submenu item within the Events Manager menu.
 */
function my_em_submenu () {
	if(function_exists('add_submenu_page')) {
   		$plugin_page = add_submenu_page('events-manager', 'Event Styles', 'Styles', 'edit_events', "events-manager-styles", 'my_em_admin_styles_page');
  	}
}
add_action('admin_menu','my_em_submenu', 20);

/**
 * Handle the styles page 
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

function my_em_styles_event_load($EM_Event){
	global $wpdb;
	$EM_Event->styles = $wpdb->get_col("SELECT meta_value FROM ".EM_META_TABLE." WHERE object_id='{$EM_Event->id}' AND meta_key='event-style'", 0	);
}
add_action('em_event','my_em_styles_event_load',1,1);

function my_em_styles_metabox(){
	global $EM_Event;
	$my_em_styles = (is_array(get_option('my_em_styles'))) ? get_option('my_em_styles'):array();
	?>
	<div id="event-styles" class="stuffbox">
		<h3>Event Styles</h3>
		<div class="inside" style="padding:10px;">
			<?php foreach( $my_em_styles as $style_id => $style ):?>
			<label><input type="checkbox" name="event_styles[]" value="<?php echo $style_id; ?>" <?php if(in_array($style_id, $EM_Event->styles)) echo 'checked="checked"'; ?> /> <?php echo $style ?></label><br />			
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}
add_action('em_admin_event_form_side_footer','my_em_styles_metabox');
add_action('em_front_event_form_footer','my_em_styles_metabox');

function my_em_styles_event_save($result,$EM_Event){
	global $wpdb;
	$my_em_styles = (is_array(get_option('my_em_styles'))) ? get_option('my_em_styles'):array();
	if( $result && !empty($_POST['event_styles']) ){
		$ids_to_add = array();
		$EM_Event->styles = array();
		foreach( $_POST['event_styles'] as $style_id ){
			if( array_key_exists($style_id, $my_em_styles) ){
				$ids_to_add[] = "({$EM_Event->id}, 'event-style', '$style_id')";
				$EM_Event->styles[] = $style_id;
			}
		}
		//First delete any old saves
		$wpdb->query("DELETE FROM ".EM_META_TABLE." WHERE object_id='{$EM_Event->id}' AND meta_key='event-style'");
		if( count($ids_to_add) > 0 ){
			$wpdb->query("INSERT INTO ".EM_META_TABLE." (object_id, meta_key, meta_value) VALUES ".implode(',',$ids_to_add));
		}
	}
	return $result;
}
add_filter('em_event_save','my_em_styles_event_save',1,2);

//search stuff for the style
function my_em_styles_search_form(){
	$my_em_styles = (is_array(get_option('my_em_styles'))) ? get_option('my_em_styles'):array();
	?>
	<!-- START Styles Search -->
	<select name="style">
		<option value=''>All Styles</option>
		<?php foreach($my_em_styles as $style_id=>$style_name): ?>
		 <option value="<?php echo $style_id; ?>" <?php echo ($_POST['style'] == $style_id) ? 'selected="selected"':''; ?>><?php echo $style_name; ?></option>
		<?php endforeach; ?>
	</select>
	<!-- END Styles Search -->
	<?php
}
add_action('em_template_events_search_form_ddm', 'my_em_styles_search_form');

function my_em_styles_accepted_searches($searches){
	$searches[] = 'style';
	return $searches;
}
add_filter('em_accepted_searches','my_em_styles_accepted_searches',1,1);

function my_em_styles_get_default_search($searches, $array){
	if( !empty($array['style']) && is_numeric($array['style']) ){
		$searches['style'] = $array['style'];
	}
	return $searches;
}
add_filter('em_events_get_default_search','my_em_styles_get_default_search',1,2);

function my_em_styles_events_get($events, $args){
	if( !empty($args['style']) && is_numeric($args['style']) ){
		foreach($events as $event_key => $EM_Event){
			if( !in_array($args['style'],$EM_Event->styles) ){
				unset($events[$event_key]);
			}
		}
	}
	return $events;
}
add_filter('em_events_get','my_em_styles_events_get',1,2);

function my_em_styles_event_output_condition($replacement, $condition, $match, $EM_Event){
	//check if there is a conditional tag for a specific style id
	if( is_object($EM_Event) && preg_match('/^has_style_(.+)$/',$condition, $matches) && is_array( $EM_Event->styles ) ){
		if( in_array($matches[1],$EM_Event->styles) ){
			//this event has the desired style id, so show the replacement
			$replacement = preg_replace("/\{\/?$condition\}/", '', $match);
		}else{
			//no style, don't show the conditional
			$replacement = '';
		}
	}
	return $replacement;
}
add_action('em_event_output_condition', 'my_em_styles_event_output_condition', 1, 4);

function my_em_styles_rewrite_rules_array($rules){
	//get the slug of the event page
	$events_page_id = get_option ( 'dbem_events_page' );
	$events_page = get_post($events_page_id);
	$my_em_rules = array();
	if( is_object($events_page) ){
		$events_slug = $events_page->post_name;
		$my_em_rules[$events_slug.'/styles$'] = 'index.php?pagename='.$events_slug.'&styles_page=1'; //events with scope
		$my_em_rules[$events_slug.'/style/(\d+)$'] = 'index.php?pagename='.$events_slug.'&style_id=$matches[1]'; //events with scope
	}
	return $my_em_rules + $rules;
}
add_filter('rewrite_rules_array','my_em_styles_rewrite_rules_array');

function my_em_styles_query_vars($vars){
	array_push($vars, 'style_id','styles_page');
    return $vars;
}
add_filter('query_vars','my_em_styles_query_vars');

function my_em_styles_content($content){
	global $wp_query;
	$my_em_styles = (is_array(get_option('my_em_styles'))) ? get_option('my_em_styles'):array();
	if( is_numeric($wp_query->get('style_id')) ){
		$content = EM_Events::output(array('style'=>$wp_query->get('style_id')));
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

function my_em_styles_placeholders($replace, $EM_Event, $result){
	global $wp_query, $wp_rewrite;
	switch( $result ){
		case '#_STYLES':
			$replace = 'none';
			if( count($EM_Event->styles) > 0 ){
				$my_em_styles = (is_array(get_option('my_em_styles'))) ? get_option('my_em_styles'):array();
				$styles = array();
				foreach( $my_em_styles as $id => $name ){
					if(in_array($id, $EM_Event->styles)){
						$styles[] = $name;
					}
				}
				$replace = implode(', ', $styles);
			}
			break;
	}
	return $replace;
}
add_filter('em_event_output_placeholder_pre','my_em_styles_placeholders',1,3);
?>