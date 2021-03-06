<?php
/**
 * Backend actions for the LSX TO Plugin
 *
 * @package   LSX_TO_Admin
 * @author    LightSpeed
 * @license   GPL3
 * @link      
 * @copyright 2016 LightSpeedDevelopment
 */

/**
 * Main plugin class.
 *
 * @package LSX_TO_Admin
 * @author  LightSpeed
 */
class LSX_TO_Admin extends LSX_Tour_Operators {

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	public function __construct() {
		$this->options = get_option('_lsx_lsx-settings',false);	
		$this->set_vars();

		add_action( 'init', array( $this, 'require_post_type_classes' ) , 1 );
		add_action( 'init', array( $this, 'global_taxonomies') );
		add_filter( 'lsx_framework_settings_tabs', array( $this, 'settings_page_array') );

		add_action('lsx_framework_dashboard_tab_content',array($this,'dashboard_tab_content'));

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_stylescripts' ) );
		add_action( 'cmb_save_custom', array( $this, 'post_relations' ), 3 , 20 );

		add_filter( 'plugin_action_links_' . plugin_basename(LSX_TOUR_OPERATORS_CORE), array($this,'add_action_links'));		
	}		

	/**
	 * outputs the dashboard tabs settings
	 */
	public function dashboard_tab_content() {
		?>	
		<tr class="form-field-wrap">
			<th scope="row">
				<label for="modules"> Modules</label>
			</th>
			<td><ul>
			<?php 	
			$post_types = apply_filters('lsx_tour_operators_post_types',$this->post_types);
			if(is_array($post_types) && !empty($post_types)){

				foreach($post_types as $slug => $label){
					if('envira' === $slug){ continue; }
					?>
					<li>
						<input type="checkbox" <?php if(in_array($slug,$this->active_post_types)){ echo 'checked="checked"'; } ?> name="post_types[<?php echo esc_attr( $slug ); ?>]" /> <label for="post_types"><?php echo esc_html( $label ); ?></label> 
					</li>
				<?php }
			}else{
				?>
					<li>
						You have no modules active. 
					</li>
				<?php
			}
			?>
			</ul></td>
		</tr>	

		<?php $this->modal_setting(); ?>

		<?php if(!class_exists('LSX_Currency')) { ?>
			<tr class="form-field-wrap">
				<th scope="row">
					<label for="currency"> Currency</label>
				</th>
				<td>
					<select value="{{currency}}" name="currency">
						<option value="USD" {{#is currency value=""}}selected="selected"{{/is}} {{#is currency value="USD"}} selected="selected"{{/is}}>USD (united states dollar)</option>
						<option value="GBP" {{#is currency value="GBP"}} selected="selected"{{/is}}>GBP (british pound)</option>
						<option value="ZAR" {{#is currency value="ZAR"}} selected="selected"{{/is}}>ZAR (south african rand)</option>
						<option value="NAD" {{#is currency value="NAD"}} selected="selected"{{/is}}>NAD (namibian dollar)</option>
						<option value="CAD" {{#is currency value="CAD"}} selected="selected"{{/is}}>CAD (canadian dollar)</option>
						<option value="EUR" {{#is currency value="EUR"}} selected="selected"{{/is}}>EUR (euro)</option>
						<option value="HKD" {{#is currency value="HKD"}} selected="selected"{{/is}}>HKD (hong kong dollar)</option>
						<option value="SGD" {{#is currency value="SGD"}} selected="selected"{{/is}}>SGD (singapore dollar)</option>
						<option value="NZD" {{#is currency value="NZD"}} selected="selected"{{/is}}>NZD (new zealand dollar)</option>
						<option value="AUD" {{#is currency value="AUD"}} selected="selected"{{/is}}>AUD (australian dollar)</option>
					</select>
				</td>
			</tr>
		<?php } ?>

			<tr class="form-field-wrap">
				<th scope="row">
					<label for="currency"><?php esc_html_e('General Enquiry','lsx-tour-operators'); ?></label>
				</th>
				<?php
					if(true === $this->show_default_form()){
						$forms = $this->get_activated_forms(); ?>
						<td>
							<select value="{{enquiry}}" name="enquiry">
							<?php
							if(false !== $forms && '' !== $forms){ ?>
								<option value="" {{#is enquiry value=""}}selected="selected"{{/is}}>Select a form</option>
								<?php
								foreach($forms as $form_id => $form_data){ ?>
									<option value="<?php echo esc_attr( $form_id ); ?>" {{#is enquiry value="<?php echo esc_attr( $form_id ); ?>"}} selected="selected"{{/is}}><?php echo esc_html( $form_data ); ?></option>
								<?php
								}
							}else{ ?>
								<option value="" {{#is enquiry value=""}}selected="selected"{{/is}}>You have no form available</option>
							<?php } ?>
							</select>
						</td>
					<?php }else{ ?>
						<td>
							<textarea class="description enquiry" name="enquiry" rows="10">{{#if enquiry}}{{{enquiry}}}{{/if}}</textarea>
						</td>
					<?php
					}
				?>
			</tr>
			<tr class="form-field">
				<th scope="row">
					<label for="description"><?php esc_html_e('Disable Enquire Modal','lsx-tour-operators'); ?></label>
				</th>
				<td>
					<input type="checkbox" {{#if disable_enquire_modal}} checked="checked" {{/if}} name="disable_enquire_modal" />
					<small><?php esc_html_e('This disables the enquire modal, and instead redirects to the link you provide below.','lsx-tour-operators'); ?></small>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row">
					<label for="title"><?php esc_html_e('Enquire Link','lsx-tour-operators'); ?></label>
				</th>
				<td>
					<input type="text" {{#if enquire_link}} value="{{enquire_link}}" {{/if}} name="enquire_link" />
				</td>
			</tr>							
		<?php  
	}

	/**
	 * outputs the dashboard tabs settings
	 */
	public function single_settings() { 
		$this->modal_setting();
	}		

	/**
	 * outputs the modal setting field
	 */
	public function modal_setting() { ?>
		<tr class="form-field">
			<th scope="row">
				<label for="description"><?php esc_html_e('Enable Connected Modals','lsx-tour-operators'); ?></label>
			</th>
			<td>
				<input type="checkbox" {{#if enable_modals}} checked="checked" {{/if}} name="enable_modals" />
				<small><?php esc_html_e('Any connected item showing on a single will display a preview in a modal.','lsx-tour-operators'); ?></small>
			</td>
		</tr>
	<?php
	}

	/**
	 * checks which plugin is active, and grabs those forms.
	 */
	public function show_default_form() {
		if(class_exists('Caldera_Forms_Forms') || class_exists('GFForms') || class_exists('Ninja_Forms')) {
			return true;
		}else{
			return false;
		}		
	}

	/**
	 * checks which plugin is active, and grabs those forms.
	 */
	public function get_activated_forms() {
		$all_forms = false;
		if(class_exists('Ninja_Forms')){
			$all_forms = $this->get_ninja_forms();
		}elseif(class_exists('GFForms')){
			$all_forms = $this->get_gravity_forms();
		}elseif(class_exists('Caldera_Forms_Forms')) {
			$all_forms = $this->get_caldera_forms();
		}
		return $all_forms;
	}

	/**
	 * gets the currenct ninja forms
	 */
	public function get_ninja_forms() {
		global $wpdb;
		$results = $wpdb->get_results("SELECT id,title FROM {$wpdb->prefix}nf3_forms");
		$forms = false;
		if(!empty($results)){
			foreach($results as $form){
				$forms[$form->id] = $form->title;
			}
		}
		return $forms;
	}
	/**
	 * gets the currenct gravity forms
	 */
	public function get_gravity_forms() {
		global $wpdb;
		$results = RGFormsModel::get_forms( null, 'title' );
		$forms = false;
		if(!empty($results)){
			foreach($results as $form){
				$forms[$form->id] = $form->title;
			}
		}
		return $forms;
	}
	/**
	 * gets the currenct caldera forms
	 */
	public function get_caldera_forms() {
		global $wpdb;
		$results = Caldera_Forms_Forms::get_forms(true);
		$forms = false;
		if(!empty($results)){
			foreach($results as $form => $form_data){
				$forms[$form] = $form_data['name'];
			}
		}
		return $forms;
	}		

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @return    null
	 */
	public function enqueue_admin_stylescripts() {
		$screen = get_current_screen();
		if( !is_object( $screen ) ){
			return;
		}
		wp_enqueue_style( 'lsx-tour-operators-admin-style', LSX_TOUR_OPERATORS_URL . '/assets/css/admin.css');
	}

	/**
	 * Register the global post types.
	 *
	 *
	 * @return    null
	 */
	public function global_taxonomies() {
			
		$labels = array(
				'name' => _x( 'Travel Styles', 'lsx-tour-operators' ),
				'singular_name' => _x( 'Travel Style', 'lsx-tour-operators' ),
				'search_items' =>  __( 'Search Travel Styles' , 'lsx-tour-operators' ),
				'all_items' => __( 'Travel Styles' , 'lsx-tour-operators' ),
				'parent_item' => __( 'Parent Travel Style' , 'lsx-tour-operators' ),
				'parent_item_colon' => __( 'Parent Travel Style:' , 'lsx-tour-operators' ),
				'edit_item' => __( 'Edit Travel Style' , 'lsx-tour-operators' ),
				'update_item' => __( 'Update Travel Style' , 'lsx-tour-operators' ),
				'add_new_item' => __( 'Add New Travel Style' , 'lsx-tour-operators' ),
				'new_item_name' => __( 'New Travel Style' , 'lsx-tour-operators' ),
				'menu_name' => __( 'Travel Styles' , 'lsx-tour-operators' ),
		);
		
		// Now register the taxonomy
		register_taxonomy('travel-style',array('accommodation','tour','destination','review','vehicle','special'), array(
			'hierarchical' => true,
			'labels' => $labels,
			'show_ui' => true,
			'public' => true,
			'exclude_from_search' => true,
			'show_admin_column' => true,
			'query_var' => true,
			'rewrite' => array('travel-style'),
		));	
		
		$labels = array(
				'name' => _x( 'Brands', 'lsx-tour-operators' ),
				'singular_name' => _x( 'Brand', 'lsx-tour-operators' ),
				'search_items' =>  __( 'Search Brands' , 'lsx-tour-operators' ),
				'all_items' => __( 'Brands' , 'lsx-tour-operators' ),
				'parent_item' => __( 'Parent Brand' , 'lsx-tour-operators' ),
				'parent_item_colon' => __( 'Parent Brand:' , 'lsx-tour-operators' ),
				'edit_item' => __( 'Edit Brand' , 'lsx-tour-operators' ),
				'update_item' => __( 'Update Brand' , 'lsx-tour-operators' ),
				'add_new_item' => __( 'Add New Brand' , 'lsx-tour-operators' ),
				'new_item_name' => __( 'New Brand' , 'lsx-tour-operators' ),
				'menu_name' => __( 'Brands' , 'lsx-tour-operators' ),
		);
		
		
		// Now register the taxonomy
		register_taxonomy('accommodation-brand',array('accommodation'), array(
				'hierarchical' => true,
				'labels' => $labels,
				'show_ui' => true,
				'public' => true,
				'exclude_from_search' => true,
				'show_admin_column' => true,
				'query_var' => true,
				'rewrite' => array('slug'=>'brand'),
		));	

		$labels = array(
				'name' => _x( 'Location', 'lsx-tour-operators' ),
				'singular_name' => _x( 'Location', 'lsx-tour-operators' ),
				'search_items' =>  __( 'Search Locations' , 'lsx-tour-operators' ),
				'all_items' => __( 'Locations' , 'lsx-tour-operators' ),
				'parent_item' => __( 'Parent Location' , 'lsx-tour-operators' ),
				'parent_item_colon' => __( 'Parent Location:' , 'lsx-tour-operators' ),
				'edit_item' => __( 'Edit Location' , 'lsx-tour-operators' ),
				'update_item' => __( 'Update Location' , 'lsx-tour-operators' ),
				'add_new_item' => __( 'Add New Location' , 'lsx-tour-operators' ),
				'new_item_name' => __( 'New Location' , 'lsx-tour-operators' ),
				'menu_name' => __( 'Locations' , 'lsx-tour-operators' ),
		);
		// Now register the taxonomy
		register_taxonomy('location',array('accommodation'), array(
				'hierarchical' => true,
				'labels' => $labels,
				'show_ui' => true,
				'public' => true,
				'exclude_from_search' => true,
				'show_admin_column' => true,
				'query_var' => true,
				'rewrite' => array('slug'=>'location'),
		));			

	}

	/**
	 * Returns the array of settings to the UIX Class in the lsx framework
	 */	
	public function settings_page_array($tabs){
		// This array is for the Admin Pages. each element defines a page that is seen in the admin
		
		$post_types = apply_filters('lsx_tour_operators_post_types',$this->post_types);
		
		if(false !== $post_types && !empty($post_types)){
			foreach($post_types as $index => $title){

				$disabled = false;
				if(!in_array($index,$this->active_post_types)){
					$disabled = true;
				}

				$tabs[$index] = array(
					'page_title'        => 'General',
					'page_description'  => '',
					'menu_title'        => $title,
					'template'          => apply_filters('lsx_tour_operators_settings_path',LSX_TOUR_OPERATORS_PATH,$index).'includes/settings/'.$index.'.php',
					'default'	 		=> false,
					'disabled'			=> $disabled
				);
			}
			ksort($tabs);
		}
		return $tabs;
	}	

	/**
	 * Requires the post type classes
	 *
	 * @since 0.0.1
	 */
	public function require_post_type_classes() {
		if(!empty($this->active_post_types)){
			
			foreach($this->post_types as $post_type => $label){
				if(in_array($post_type,$this->active_post_types)){
					require_once( LSX_TOUR_OPERATORS_PATH . 'classes/class-'.$post_type.'.php' );		
				}
			}
			
			//If The Envira Plugin has been activated.
			if(class_exists('Envira_Gallery') && 'accommodation' !== $post_type){
				require_once( LSX_TOUR_OPERATORS_PATH . 'classes/class-envira-integration.php' );
				$this->post_types['envira'] = 'Envira'; 
			}
			$this->connections = $this->create_post_connections();	
			$this->single_fields = apply_filters('lsx_tour_operators_search_fields',array());

			if(is_admin()){
				foreach($this->active_post_types as $pt){
					add_action('lsx_framework_'.$pt.'_tab_single_settings_bottom', array($this,'single_settings'),40);
				}	
			}
		}
	}

	/**
	 * Generates the post_connections used in the metabox fields
	 */
	public function create_post_connections() {
		$connections = array();
		$post_types = apply_filters('lsx_tour_operators_post_types',$this->post_types);
		foreach($post_types as $key_a => $values_a){
			foreach($this->post_types as $key_b => $values_b){
				// Make sure we dont try connect a post type to itself.
				if($key_a !== $key_b){
					$connections[] = $key_a.'_to_'.$key_b;
				}
			}
		}
		return $connections;
	}	

	/**
	 * Sets up the "post relations"
	 *
	 * @return    object|Module_Template    A single instance of this class.
	 */
	public function post_relations($post_id, $field, $value) {
		
		if('group' === $field['type'] && array_key_exists($field['id'], $this->single_fields)){
				
			$delete_counter = array();
			foreach($this->single_fields[$field['id']] as $fields_to_save){
				$delete_counter[$fields_to_save] = 0;
			}
			
			//Loop through each group in case of repeatable fields
			$relations = $previous_relations = false;

			foreach($value as $group){
		
				//loop through each of the fields in the group that need to be saved and grab their values.
				foreach($this->single_fields[$field['id']] as $fields_to_save){
					
					//Check if its an empty group
					if(isset($group[$fields_to_save]) && !empty($group[$fields_to_save])){;
						if($delete_counter[$fields_to_save]<1){
							//If this is a relation field, then we need to save the previous relations to remove any items if need be.
							if(in_array($fields_to_save,$this->connections)){
								$previous_relations[$fields_to_save] = get_post_meta($post_id,$fields_to_save,false);
							}							
							delete_post_meta( $post_id, $fields_to_save );
							$delete_counter[$fields_to_save]++;
						}
						
						//Run through each group
						foreach($group[$fields_to_save] as $field_value){
								
							if(null !== $field_value){
			
								if(1 === $field_value){ $field_value = true; }
								add_post_meta($post_id,$fields_to_save,$field_value);
							
								//If its a related connection the save that
								if(in_array($fields_to_save,$this->connections)){
									$relations[$fields_to_save][$field_value] = $field_value;
								}
							}
						}
					}
				}// end of the inner foreach
				
			}//end of the repeatable group foreach
			
			//If we have relations, loop through them and save the meta
			if(false!==$relations){
				foreach($relations as $relation_key => $relation_values){
					$temp_field = array('id'=>$relation_key);
					$this->save_related_post($post_id, $temp_field, $relation_values,$previous_relations[$relation_key]);
				}
			}			
			
		}else{			
			if(in_array($field['id'],$this->connections)){
				$this->save_related_post($post_id, $field, $value);
			}
		}
	}

	/**
	 * Save the reverse post relation.
	 *
	 *
	 * @return    null
	 */
	public function save_related_post($post_id, $field, $value,$previous_values=false) {
		$ids = explode('_to_',$field['id']);
			
		$relation = $ids[1].'_to_'.$ids[0];

		if(in_array($relation,$this->connections)){
			
			if(false===$previous_values){
				$previous_values = get_post_meta($post_id,$field['id'],false);
			}
		
			if(false !== $previous_values && !empty($previous_values)){
				foreach($previous_values as $tr){
					delete_post_meta( $tr, $relation, $post_id );
				}
			}		

			if(is_array($value)){
				foreach($value as $v){
					if('' !== $v && null !== $v && false !== $v){
						add_post_meta($v,$relation,$post_id);
					}
				}
			}
		}		
	}

	/**
	 * Adds in the "settings" link for the plugins.php page
	 */
	public function add_action_links ( $links ) {
		 $mylinks = array(
		 	'<a href="' . admin_url( 'options-general.php?page=lsx-lsx-settings' ) . '">'.__('Settings',$this->plugin_slug).'</a>',
		 	'<a href="https://www.lsdev.biz/documentation/lsx-tour-operator-plugin/" target="_blank">'.__('Documentation',$this->plugin_slug).'</a>',
		 	'<a href="https://feedmysupport.zendesk.com/home" target="_blank">'.__('Support',$this->plugin_slug).'</a>',
		 );
		return array_merge( $links, $mylinks );
	}					
}