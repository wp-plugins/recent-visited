<?php

/**
 * Plugin Name: Recent visited
 * Plugin URI: http://infogiants.com/
 * Description: A plugin to display recently visisted custom post type. shortcode [VISITED_ITEMS]
 * Version: 1.0
 * Author: Kapil yadav
 * Author URI: http://infogiants.com/
 */

/*
 * Add Menu page to manage settings
 */
if(! function_exists('recentVisitedPanel')){

	function recentVisitedPanel(){

		add_menu_page('Recent Visited', 'Recent Visited', 'manage_options', 'recent-visited', 'showRecentVisitedPanel', plugins_url('recent-visited/images/recent.png'));
	}
}


/*
 * Show Recent Visited Items Panel
 */
if(! function_exists(showRecentVisitedPanel)){

	function showRecentVisitedPanel(){

		//retrive old values
		$oldType	=	get_option('recent_visited_post_type');
		$oldRange	=	get_option('recent_visited_range');

		//Registered post types
		$args = array( 'public' => true );
		$types = get_post_types($args);

		//Unset Page and attachment, not commonly used
		unset($types['page']);
		unset($types['attachment']);

		$dropdown	=	"<select name='unc_post_type' class='unc_post_type regular-text'>";
			foreach($types as $option){

				if( $oldType == $option ){

					$select = "selected";

				}else{

					$select = "";
				}

				$dropdown .= "<option value='".$option."' ".$select.">".ucfirst($option)."</option>";
			}
		$dropdown  .=	"</select>";

		// Form
		$render	=	"<div class='wrap'><h2>Recent Visited</h2>";

		if($_POST['is_visited'] == 'is_visited'){

			$render	.=	"<div id='setting-error-settings_updated' class='updated settings-error hide_settings'>
							<p><strong>Settings saved.</strong></p></div>";
		}
		
		$render	.=	"<form method='post'>
								<table class='form-table'>
									<tbody>
										<tr>
										<th scope='row'><label for='blogname'>Post Types</label></th>
										<td>".$dropdown."</td>
										</tr>
										<tr>
										<th scope='row'><label for='blogname'>Number of Items</label></th>
										<td><input type='range' name='slider' id='slider' value='".$oldRange."' min='0' max='25' /><span class='slide_val'>".$oldRange."</span></td>
										</tr>
										
									</tbody>
								</table>
								<input type='hidden' name='is_visited' value='is_visited'>
								<p class='submit'><input name='submit' id='submit' class='button button-primary' value='Save Changes' type='submit'></p>
							</form>
							<p><strong>Use shortcode: </strong> [VISITED_ITEMS]</p>
						</div>";

		echo 	$render;

		//JS
		wp_enqueue_script('web',plugins_url( '/js/recent.js' , __FILE__ ));

	}
}


/*
 * Save Options
 */
if(! function_exists('saveRecentVisited')){

	function saveRecentVisited(){

		if($_POST['is_visited']){
			$postType	=	sanitize_text_field($_POST['unc_post_type']);
			$number     =   $_POST['slider'];
			update_option('recent_visited_post_type', $postType);
			update_option('recent_visited_range', $number);
		}
	}
}

/* Check setting page and reset session */
if(! function_exists(settingCheckVisited)){

	function settingCheckVisited(){
		//comapre page
		if( ($_GET['page'] == "recent-visited") && ($_POST['is_visited'] == "is_visited") ){

			//check if post type changed
			$postType 	=	get_option('recent_visited_post_type');
			if(sanitize_text_field($_POST['unc_post_type']) != $postType){

				//reset session var
				$_SESSION['UNIQUE_RECENT_VISIT_CUSTOM_ALL_ITEMS'] = array();
			}
		}
	}

}


/*
 * Get recent visited and add shortcode
 */
if(! function_exists('getRecentVisited')){

	function getRecentVisited(){

		$postType 	=	get_option('recent_visited_post_type');
		$number 	=	get_option('recent_visited_range');
		$result		=	array(
			'post_type'	=>	$postType,
			'number'	=>	$number
		);
	}
}

/*
 * Track single templates
 */
if(! function_exists('trackPostTypeTemplate')){

	function trackPostTypeTemplate() {

		global $post;

		$postType 	= 	get_option('recent_visited_post_type');
		$number     =   get_option('recent_visited_range');

	    if ($post->post_type == $postType ) {

	    	if($postType != $_SESSION['old_type']){

	    		$_SESSION['UNIQUE_RECENT_VISIT_CUSTOM_ALL_ITEMS'] = array();
	    	}

	    	if(count($_SESSION['UNIQUE_RECENT_VISIT_CUSTOM_ALL_ITEMS']) <= $number ){
	    		
	    		$_SESSION['UNIQUE_RECENT_VISIT_CUSTOM_ALL_ITEMS'][]	= $post->ID;
	    		$_SESSION['old_type'] = $postType;	
	    	}
	    }
	}

}

/*
 * Get Unique Visited Items
 */
if(! function_exists('getUniqueVisitedItems')){

	function getUniqueVisitedItems(){

		$postType  =  get_option('recent_visited_post_type');

		if( count($_SESSION['UNIQUE_RECENT_VISIT_CUSTOM_ALL_ITEMS']) > 0){

			$uniqueArray	=	array_unique($_SESSION['UNIQUE_RECENT_VISIT_CUSTOM_ALL_ITEMS']);

			//check previous post type
			foreach($uniqueArray as $checkItem){

				$postObj	=	get_post($checkItem);	
				if($postObj->post_type == $postType){

					$newArray[]	= $checkItem;
				}
			}

		}else{

			$uniqueArray = array("No recent visited ".$postType);
		}

		//List Items if array not empty
		if(count($newArray) > 0 ){

			$renderVisited		=	"<ul class='recent_unc_visited'>";

			//max_view 
			$max_view 	=	get_option('recent_visited_range');

			//view count 
			$view_count = 1;

			// limit view check

			foreach($newArray as $itemId){

					//check view limit
					if($view_count > $max_view ){

						break;
					}

					$itemObject		= 	get_post($itemId);
					$itemImg  		=	has_post_thumbnail($itemId)	? get_the_post_thumbnail($itemId, 'thumbnail', array( 'class' => 'visited_items_thumb' )) : "<img src='".plugins_url( '/images/no-img.png' , __FILE__ )."' class='visited_items_thumb'>";
					$renderVisited .=	"<li class='recent_unc_sigle_item'><div class='visited_thumb'><a href=".get_permalink($itemId)." title=".$itemObject->post_title." target='_blank'>".$itemImg."</a></div><a class='visited_title' href=".get_permalink($itemId)." title=".$itemObject->post_title." target='_blank'>".$itemObject->post_title."</a><div class='visited_content'>".wp_trim_words( $itemObject->post_content, 10, '<br><br><a class="visited_read_more" href="'. get_permalink($itemId) .'"> Read More</a>' )."</div></li>";
					
					//increase view count
					$view_count++;
			}
			
			$renderVisited     .=	"</ul><div class='clear_visited'></div>";

			//css
			wp_enqueue_style('web',plugins_url( '/css/web.css' , __FILE__ ));

			//Display Items
			echo $renderVisited;
		}

	}

	// Add Logo Shortcode
	add_shortcode('VISITED_ITEMS', 'getUniqueVisitedItems');
}


/*
 * Active Plguin
 */
if(!function_exists('recentVisitedActive')){

	function recentVisitedActive() {

    	//default post type set
    	update_option('recent_visited_post_type', 'post');
    	update_option('recent_visited_range', '0');
	}
}


/*
 * Uninstall plugin
 */

if(! function_exists('recentVisitedUninstall')){

	function recentVisitedUninstall(){

		//delete options added on activation
		delete_option('recent_visited_post_type');
		delete_option('recent_visited_range');

		//unset session var
		unset($_SESSION['UNIQUE_RECENT_VISIT_CUSTOM_ALL_ITEMS']);

	}
}

/* Handle Wp session */
if(! function_exists(register_recent_visit_session)){

	function register_recent_visit_session(){
    if( !session_id() )
        session_start();
	}

}

//Filters
add_filter( 'single_template', 'trackPostTypeTemplate' );

//Hooks
add_action( 'admin_menu', 'recentVisitedPanel' );
add_action( 'admin_init', 'saveRecentVisited' );
add_action('init','register_recent_visit_session');
register_activation_hook( __FILE__, 'recentVisitedActive' );
register_deactivation_hook( __FILE__, 'recentVisitedUninstall' );

//debug
add_action('admin_init', 'settingCheckVisited');

?>