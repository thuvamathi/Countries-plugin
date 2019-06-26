<?php

/*
Plugin Name: Countries
Plugin URI: http://wordpress.org/extend/plugins/countries/
Description: Import and manage a list of countries into your WordPress site as Custom Post Types.
Version: 1.1
Author: Thuva
*/


class Countries {



	/**
	 * Constructor
	 */
	function Countries() {
		add_action( 'init', array( $this, 'register_post_types' ) );
       wp_enqueue_script('country-js', plugins_url('/countries/js/country_js.js'), array('jquery'), null, true);

		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'save_post', array( $this, 'save_details' ) );
			add_filter( 'posts_orderby', array( $this, 'countries_orderby' ), 0, 1 );
		}
	}
	
	/**
	 * Create custom post type and custom taxonomy
	 * http://codex.wordpress.org/Function_Reference/register_post_type
	 */
	function register_post_types() {
		$labels = array(
			'name'               => _x( 'Countries', 'post type general name', 'countries' ),
			'singular_name'      => _x( 'Country', 'post type singular name', 'countries' ),
			'add_new'            => _x( 'Add New', 'Country', 'countries' ),
			'add_new_item'       => __( 'Add New Country', 'countries' ),
			'edit_item'          => __( 'Edit Country', 'countries' ),
			'new_item'           => __( 'New Country', 'countries' ),
			'view_item'          => __( 'View Country', 'countries' ),
			'search_items'       => __( 'Search Countries', 'countries' ),
			'not_found'          => __( 'No Countries found', 'countries' ),
			'not_found_in_trash' => __( 'No Countries found in Trash', 'countries' ), 
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Countries', 'countries' )
		);
		$args = array(
			'labels'               => $labels,
			'public'               => true,
			'exclude_from_search'  => true,
			'publicly_queryable'   => true,
			'show_ui'              => true,
			'show_in_nav_menus'    => false, 
			'query_var'            => true,
			'rewrite'              => true,
			'capability_type'      => 'post',
			'hierarchical'         => false,
			'show_in_nav_menus'    => false,
			'menu_position'        => null,
			'menu_icon'            => plugins_url( 'images/menu-icon.png', __FILE__ ),
			'supports'             => array( 'title' ),
			'visible'              => true,
			'register_meta_box_cb' => array( $this, 'country_meta_boxes' )
		);
		register_post_type( 'countries', $args );
	}
	
	/**
	 * Create Country Meta Boxes
	 */
	function country_meta_boxes() {
		//add_meta_box( 'countries_meta', __( 'Quick link - All Countries', 'countries' ), array( $this, 'render_countries_meta' ), 'countries', 'side', 'low' );
		add_meta_box( 'countrycode_meta', __( 'Country', 'countries' ), array( $this, 'render_countrycode_meta' ), 'countries', 'normal', 'low' );
		}
	
	/**
	 * Countries Admin Menu
	 *
	 * Adds XML Importer admin page.
	 */
	function admin_menu() {
		add_submenu_page( 'edit.php?post_type=countries', __( 'Countries Importer', 'countries' ), __( 'Importer', 'countries' ), 'manage_options', 'countries-importer-page', array( $this, 'importer_page' ) );
	}
	
	/**
	 * Countries Import Page
	 */
	function importer_page() {
		include_once( plugin_dir_path( __FILE__ ) . 'includes/xml-parser.php' );
		$xml = new Countries_XML_Parser();
		?>
		<div class="wrap">
			<h2><?php _e( 'Country Importer', 'countries' ); ?></h2>
			<?php $xml->save_initial_countries(); ?>
		</div>
		<?php
	}
	
	/**
	 * Save Country Meta Box Details
	 *
	 * @param int $post_id Post ID.
	 */
	function save_details( $post_id ) {
		global $post;
		
		$post_vars = shortcode_atts( array(
			'country_code'     => '',
			'country_list'     => '',
			'flags'            => '',
			'country_details'  => '',
			'country_currency' => '',
			'currency_symbol'  => '',
			'currency_html'    => '',
			'currency_code'    => '',
			'city_list'        => ''
		), $_POST );
		
		update_post_meta( $post_id, 'country_code', $post_vars['country_code'] );
		update_post_meta( $post_id, 'country_list', $post_vars['country_list'] );
		update_post_meta( $post_id, 'flags', $post_vars['flags'] );
		update_post_meta( $post_id, 'country_details', $post_vars['country_details'] );
		update_post_meta( $post_id, 'country_currency', $post_vars['country_currency'] );
		update_post_meta( $post_id, 'currency_symbol', $post_vars['currency_symbol'] );
		update_post_meta( $post_id, 'currency_html', $post_vars['currency_html'] );
		update_post_meta( $post_id, 'currency_code', $post_vars['currency_code'] );
		update_post_meta( $post_id, 'city_list', $post_vars['city_list'] );
	}
	
	/**
	 * Render Countries Meta Box
	 *
	 * Displays the dropdown list of countries.
	 */
	function render_countries_meta() {
		$arrayofcountries = $this->load_countries_from_xml();
		?>
		<select name="country_list" style="width:265px;">
			<?php
			for ( $numerofcountries = 0; $numerofcountries <= count( $arrayofcountries ) - 1; $numerofcountries++ ) {
				$values = wp_parse_args( $arrayofcountries[$numerofcountries][0], array(
					'countrycode' => '',
					'countryname' => ''
				) );
				echo '<option ' . selected( $values['countrycode'], $values['countrycode'], false ) . ' value="' . $values['countrycode'] . '">' . $values['countryname'] . '</option>';
			}
			?>
		</select>
		<?php
		echo '<p>' . __( 'Quick links to countries are in progress!', 'countries' ) . '</p>';
	}
	
	/**
	 * Render Country Code Meta Box
	 */
	function render_countrycode_meta() {

        //add_action('wp_enqueue_scripts','test_init');

        $url = 'https://restcountries.eu/rest/v2/all';
        $data = array(
        );
        $query_url = sprintf("%s?%s", $url, http_build_query($data));
        $countryList = json_decode(file_get_contents($query_url));

		?>
        <p><label for="country_symbol" style="width:145px; margin-top:6px; float:left; display: block"><?php _e( 'Country', 'countries' ); ?></label>
        <select name="country" id='coun-select' onchange="countryChange(this);">
            <option value="">-- Select Country --</option>
            <?php
            	foreach($countryList as $key => $country) {
			?>
                <option data-topDomain="<?php echo $country->topLevelDomain{0}; ?>" data-alpha2Code="<?php echo $country->alpha2Code; ?>" data-alpha3Code="<?php echo $country->alpha3Code; ?>" data-callCode="<?php echo $country->callingCodes{0}; ?>" data-zone="<?php echo $country->timezones{0}; ?>" data-currency="<?php echo $country->currencies{0}->code; ?>" data-flag="<?php echo $country->flag; ?>" value="<?php echo $key; ?>"><?php echo $country->name; ?></option>
			<?php
            	}
			?>
        </select>
        <p><label for="country_symbol" style="width:145px; margin-top:6px; float:left; display: block"><?php _e( 'TobLevelDomain', 'countries' ); ?></label> <input class="regular-text" id="tobDomain" name="tobLevelDomain" value="<?php  ?>" /></p>
        <p><label for="country_symbol" style="width:145px; margin-top:6px; float:left; display: block"><?php _e( 'Alpha2Code', 'countries' ); ?></label> <input class="regular-text" id="alpha2" name="alpha2Code" value="<?php  ?>" /></p>
        <p><label for="country_symbol" style="width:145px; margin-top:6px; float:left; display: block"><?php _e( 'Alpha3Code', 'countries' ); ?></label> <input class="regular-text" id="alpha3" name="alpha3Code" value="<?php  ?>" /></p>
        <p><label for="country_symbol" style="width:145px; margin-top:6px; float:left; display: block"><?php _e( 'CallingCodes', 'countries' ); ?></label> <input class="regular-text" id="callCode" name="callingCodes" value="<?php  ?>" /></p>
        <p><label for="country_symbol" style="width:145px; margin-top:6px; float:left; display: block"><?php _e( 'Timezone', 'countries' ); ?></label> <input class="regular-text" id="zone" name="timezones" value="<?php  ?>" /></p>
        <p><label for="country_symbol" style="width:145px; margin-top:6px; float:left; display: block"><?php _e( 'Currency', 'countries' ); ?></label> <input class="regular-text" id="currency" name="currency" value="<?php  ?>" /></p>
        <p><label for="country_symbol" style="width:145px; margin-top:6px; float:left; display: block"><?php _e( 'Flag', 'countries' ); ?></label> <input class="regular-text" id="flag" name="flag" value="<?php  ?>" /></p>
		<?php
	}

	/**
	 * Render Country Flags Meta Box
	 */
	function render_flags_meta() {
		global $post;
		$flag = get_post_meta( $post->ID, 'country_code', true );
		$filepath = 'flags/' . strtolower( $flag ) . '.gif';
		if ( is_file( plugin_dir_path( __FILE__ ) . $filepath ) ) {
			?>
			<img name="flags" src="<?php echo plugins_url( $filepath, __FILE__ ); ?>" />
			<?php
		} else {
			echo '<p>' . __( 'Flag image not available.' ) . '</p>';
		}
	}
	
	/**
	 * Render the Country Currency Meta Box
	 */
	function render_currency_meta() {

		?>
		<p><label for="country_symbol" style="width:145px; margin-top:6px; float:left; display: block"><?php _e( 'Currency', 'countries' ); ?></label> <input class="regular-text" name="country_currency" value="<?php  ?>" /></p>
	<?php
	}
	
	/**
	 * Render Cities Meta Box
	 */
	function render_cities_meta() {
		global $post;
		
		$countrycode = get_post_meta( $post->ID, 'country_code', true );
		$citycode = get_post_meta( $post->ID, 'city_list', true );
		$arrayofcountries = $this->load_countries_from_xml();
		
		for ( $numerofcountries = 0; $numerofcountries <= count( $arrayofcountries ) - 1; $numerofcountries++ ) {
			// If the country has cities...
			$values = wp_parse_args( $arrayofcountries[$numerofcountries][0], array(
				'countrycode' => ''
			) );
			if ( $countrycode == $values['countrycode'] && count( $arrayofcountries[$numerofcountries][1] ) > 0 ) {
				?>
				<select name="city_list" style="width:265px;">
					<?php
					for ( $nocities = 0; $nocities <= count( $arrayofcountries[$numerofcountries][1] ) - 1; $nocities++ ) {
						$cities_values = wp_parse_args( $arrayofcountries[$numerofcountries][1][$nocities], array(
							'code' => '',
							'name' => ''
						) );
						echo '<option ' . selected( $citycode, $cities_values['code'], false ) . ' value="' . $cities_values['code'] . '">' . $cities_values['name'] . '</option>';
					}
					?>
				</select>
				<?php
			}
		}
		echo '<p>' . __( 'To add cities to a country please follow the template in the xml folder', 'countries' ) . '</p>';
	}
	
	/**
	 * Render Notes Meta Box
	 */
	function render_notes_meta() {
		global $post;
		$details = get_post_meta( $post->ID, 'country_details', true );
		?>
		<textarea cols="50" rows="5" name="country_details"><?php echo $details; ?></textarea>
		<?php
	}
	
	/**
	 * Countries orderby
	 *
	 * Forces countries to default to order alphabetically in the admin rather
	 * than by publish date. Does this as priority 0 so that by default any other
	 * filters will override this.
	 *
	 * @todo Should we do this by default elsewhere, not just the admin?
	 *
	 * @param string $order The SQL order statement.
	 */
	function countries_orderby( $order ) {
		global $wpdb;
		if ( get_query_var( 'post_type' ) == 'countries' ) {
			return "$wpdb->posts.post_title ASC";
		}
		return $order;
	}
	
	/**
	 * Load Countries from XML
	 */
	function load_countries_from_xml() {
		$doc = new DOMDocument();
		$doc->load( plugin_dir_path( __FILE__ ) . 'xml/countrylist.xml' );
		
		$rootnode = $doc->getElementsByTagName( 'countries' )->item( 0 );
		
		$arrayofcountries = array();
		$arrayposition = 0;
		foreach ( $rootnode->getElementsByTagName( 'country' ) as $countryitem ) {
			
			$arrayattributes = array();
			if ( $countryitem->hasAttributes() ) {
				$xmlattributes = $countryitem->attributes;
				if ( ! is_null( $xmlattributes ) ) {
					foreach ( $xmlattributes as $index => $attr ) {
						$arrayattributes[$attr->name] = $attr->value;
					}
				}
			}
			$arrayofcountries[$arrayposition][0] = $arrayattributes;
			
			$citysubnodes = array();
			if ( $countryitem->childNodes->length ) {
				$cityarrpos = 0;
				foreach ( $countryitem->childNodes as $city ) {
					
					//echo count( $city->attributes ) . ',';
					$cityarrayattributes = array();
					if ( $city->hasAttributes() ) {
						//echo 'has attributes';
						
						$xmlattributes = $city->attributes;
						if ( ! is_null( $xmlattributes ) ) {
							foreach ( $xmlattributes as $index => $attr ) {
								//echo $attr->value;
								$cityarrayattributes[$attr->name] = $attr->value;
								//echo $cityarrayattributes[$attr->name] . 'xx';
								//echo $attr->value;
							}
						}
						
						$citysubnodes[$cityarrpos] = $cityarrayattributes;
						$cityarrpos++;
					}
					
				}
			}
			
			$arrayofcountries[$arrayposition][1] = $citysubnodes;
			//echo count( $arrayofcountries[$arrayposition][1] );
			$arrayposition++;
		}
		
		return $arrayofcountries;
	}
	
}

global $countries;
$countries = new Countries();

/**
 * Load Countries from XML
 *
 * @todo Deprecate this function now that it has been moved to class.
 */
function loadCountriesFromXML() {
	global $countries;
	return $countries->load_countries_from_xml();
}

//function test_init()
//{
   // wp_enqueue_script('country_js', plugin_dir_url(_FILE_) . '/js/country_js.js');
    //wp_register_script('customscripts', plugin_dir_url(_FILE_) . '/js/country_js.js', array('jquery'), '1.0.0', true);
    //wp_enqueue_script('customscripts');
    wp_enqueue_script('country-js', plugins_url('/js/country_js.js'));
//}

////Function to customise table headings
//function custompostColumns($columns){
//    
//$columns = array(
//        "cb"          => "<input type=\"checkbox\" />",
//        "title"       => "Country",
//        //"countrycode" => "Country Code"
//		//"countryinfo" => "Country Details",
//        //"flag"   => "Flag",
//    );
//    return $columns;
//}
//
////Function to customise table data
//function custonpostRowValues($column){
//	global $post;
//	
//	switch ($column) {
//        case 'ID':
//            // Print post title.
//            print $post->ID;
//            break;
//      //  case 'countrycode':
//            // Print the post content
//         //   print $post->post_content;
//         //   break;
//     //    case 'countryinfo':
//            // Print the post content
//          //  print $post->post_content;
//          //  break;
//      //  case 'metavalue':
//            // Extract the metadata field and print value here.
//          //  echo get_metadata('post', $post->ID, 'custompost_subtitle', true);
//           // break;
//    }
//
//
//}

	
	





////Displays the DDL of countries


////Displays the DDL of countries


	









///this function deletes all countries from DB when plug in is deactivated xml query correct in php my admin but not in wp... temp table not supported maybe?
//function flushDB(){
//    global $wpdb;
////    
// $query="CREATE TEMPORARY TABLE posts_with_metadata
//SELECT ID
//FROM $wpdb->posts
//LEFT JOIN $wpdb->postmeta ON wp_postmeta.post_id = wp_posts.ID
////WHERE meta_key = 'country_details';
////
////CREATE TEMPORARY TABLE posts_to_delete
////SELECT ID FROM $wpdb->posts where post_type='countries' and ID not in (SELECT ID from posts_with_metadata);
////
////DELETE FROM $wpdb->posts where post_type='countries' and ID not in (SELECT ID from posts_with_metadata);
////
////DELETE FROM $wpdb->postmeta where post_id in (SELECT ID from posts_to_delete);";
////
////	$wpdb->query($wpdb->prepare( $query));
//}


//Action hooks, attaching functions to application events
	//register_deactivation_hook( __FILE__, 'flushDB' );
	//add_filter("manage_edit-custompost_columns", "custompostColumns");
	//add_action("manage_posts_custom_column", "custonpostRowValues");
	
	
///futher version ideas:
//1.Fix pagintation
//2.Hook into plugin activation and load the xml file from instinct servers instead of delivering it with the plugin
// and then copy the images locally and insert the xml contents to their local db, 
//so we can update the countries on an adhoc basis.
//3.Fix DB query to work with wpress
//4.Update flag file name when country code is changed IMPORTANT
		
?>