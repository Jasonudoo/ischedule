<?php

class BIRS_Calendar_View extends BIRS_Admin_View {

    function __construct() {
        parent::__construct();
    }

    function init() {
        add_filter('birchschedule_location_service_map', array($this, 'get_location_service_map'));
        add_filter('birchschedule_location_staff_map', array($this, 'get_location_staff_map'));
    }

    function admin_init() {
        parent::admin_init();
        add_action('wp_ajax_birs_query_appointments', array(&$this, 'ajax_query_appointments'));
        add_action('wp_ajax_birs_render_edit_form', array(&$this, 'ajax_render_edit_form'));
        add_action('wp_ajax_birs_save_appointment', array(&$this, 'ajax_save_appointment'));
        add_action('wp_ajax_birs_delete_appointment', array(&$this, 'ajax_delete_appointment'));
        add_filter('birchschedule_general_appointment_section_admin_edit',
            array($this, 'get_general_appointment_section_html'), 10, 4);
        add_filter('birchschedule_general_client_section_admin_edit', array($this, 'get_general_client_section_html'), 10, 2);
        add_filter('birchschedule_client_details_admin_edit', array($this, 'get_client_details_html'), 10, 3);
        add_filter('birchschedule_appointment_details_admin_edit', array($this, 'get_appointment_details_html'), 10, 2);
        add_filter('birchschedule_appointment_details_admin_edit_duration', array($this, 'get_appointment_duration_html'), 10, 2);
        add_filter('birchschedule_validate_booking_info_admin', array($this, 'validate_data'));
        register_post_type('birs_appointment', array(
            'labels' => array(
                'name' => __('Appointments', 'birchschedule'),
                'singular_name' => __('Appointment', 'birchschedule'),
                'add_new' => __('Add Appointment', 'birchschedule'),
                'add_new_item' => __('Add New Appointment', 'birchschedule'),
                'edit' => __('Edit', 'birchschedule'),
                'edit_item' => __('Edit Appointment', 'birchschedule'),
                'new_item' => __('New Appointment', 'birchschedule'),
                'view' => __('View Appointment', 'birchschedule'),
                'view_item' => __('View Appointment', 'birchschedule'),
                'search_items' => __('Search Appointments', 'birchschedule'),
                'not_found' => __('No Appointments found', 'birchschedule'),
                'not_found_in_trash' => __('No Appointments found in trash', 'birchschedule'),
                'parent' => __('Parent Appointment', 'birchschedule')
            ),
            'description' => __('This is where appointments are stored.', 'birchschedule'),
            'public' => false,
            'show_ui' => false,
            'capability_type' => 'post',
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'show_in_menu' => 'birchschedule_schedule',
            'hierarchical' => false,
            'show_in_nav_menus' => false,
            'rewrite' => false,
            'query_var' => true,
            'supports' => array('custom-fields'),
            'has_archive' => false
                )
        );
        if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'birchschedule_calendar') {
            do_action('birchschedule_view_calendar_admin_init');
            do_action('birchschedule_add_admin_scripts_view_calendar');
        }
    }

    function get_admin_scripts() {
        $scripts = array();
        $save_button_html = '<input type="button" class="button-primary tips" name="save_appointment" id="save_appointment" value="Save" alt="Save Data" onclick="javascript:void(0)">';
        $delete_button_html = '<a id="delete_appointment" class="submitdelete deletion" href="javascript:void(0)">' . __('Delete', 'birchschedule') . '</a>';

        $add_appointment_title = '<div>' . $save_button_html .
                '<span>' . __('Add Appointment', 'birchschedule') . '</span>' . '</div>';
        $edit_appointment_title = '<div class="submitbox">' .
                $save_button_html .
                $delete_button_html .
                '<span>' . __('Edit Appointment', 'birchschedule') . '</span>' . '</div>';
        $gmt_offset = $this->get_util()->get_gmt_offset();
        $fc_time_format = $this->get_util()->date_time_format_php_to_fullcalendar(get_option('time_format', 'g:i a'));
        $staff_order = apply_filters('birchschedule_staff_listing_order', array());
        array_unshift($staff_order, -1);
        $params = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'default_calendar_view' => apply_filters('birchschedule_default_calendar_view', 'agendaWeek'),
            'add_appointment_title' => $add_appointment_title,
            'edit_appointment_title' => $edit_appointment_title,
            'gmt_offset' => $gmt_offset,
            'fc_time_format' => $fc_time_format,
            'location_staff_map' => apply_filters('birchschedule_location_staff_map', array()),
            'staff_order' => $staff_order,
            'fc_i18n_options' => $this->get_util()->get_fullcalendar_i18n_params(),
            'i18n' => array(
                'loading' => __('Loading...', 'birchschedule'),
                'loading_appointments' => __('Loading appointments...', 'birchschedule')
            )
        );
        $scripts[] = array('birs_admin_calendar', 'birs_calendar_params', $params);
        $jquery_date_format = $this->get_util()->date_time_format_php_to_jquery(get_option('date_format'));
        $appointment_edit_params = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'service_staff_map' => $this->get_service_staff_map(),
            'service_price_map' => $this->get_service_price_map(),
            'service_duration_map' => $this->get_service_duration_map(),
            'location_staff_map' => apply_filters('birchschedule_location_staff_map', array()),
            'location_services_map' => apply_filters('birchschedule_location_service_map', array()),
            'staff_order' => apply_filters('birchschedule_staff_listing_order', array()),
            'jquery_date_format' => $jquery_date_format,
            'datepicker_i18n_options' => $this->get_util()->get_datepicker_i18n_params()
        );
        $scripts[] = array('birs_admin_appointment_edit', 'birs_appointment_edit_params', $appointment_edit_params);
        return $scripts;
    }

    function get_admin_styles() {
        return array('jquery-ui-bootstrap', 'birs_lib_fullcalendar', 'birchschedule_admin_styles', 'select2', 'jgrowl');
    }

    function get_service_price_map() {
        $query = new BIRS_Model_Query(
                        array('post_type' => 'birs_service'),
                        array(
                            'meta_keys' => array('_birs_service_price', '_birs_service_price_type'))
        );
        $services = $query->query();
        $price_map = array();
        foreach ($services as $service) {
            $price_map[$service['ID']] = array(
                'price' => $service['_birs_service_price'],
                'price_type' => $service['_birs_service_price_type']
            );
        }
        return $price_map;
    }
    
    function get_service_duration_map() {
        $query = new BIRS_Model_Query(
                        array('post_type' => 'birs_service'),
                        array(
                            'meta_keys' => array(
    							'_birs_service_length_type_0',
    							'_birs_service_length_0',
    							'_birs_service_length_type_1',
    							'_birs_service_length_1',
    							'_birs_service_length_type_2',
    							'_birs_service_length_2'
                           	)
                        )
        );
        $services = $query->query();
        $duration_map = array();
        foreach ($services as $service) {
        	for($i = 0; $i < 3; $i++){
	            $duration_map[$i][$service['ID']] = array(
	                'duration' => $service->get_service_length($i)
	            );
        	}
        }
        return $duration_map;
    }
    
    function get_service_length($service_id, $client_id = 0)
    {
    	$length_meta = '_birs_service_length_' . $client_id;
    	$length_type_meta = '_birs_service_length_type_' . $client_id;
    	$length = get_post_meta($service_id, $length_meta, true);
    	$length_type = get_post_meta($service_id, $length_type_meta, true);
    	
    	if( $length_type == 'hours') {
    		$length = $length * 60;
    	}
    	
    	$padding = get_post_meta($service_id, '_birs_service_padding', true);
    	$padding_type = get_post_meta($service_id, '_birs_service_padding_type', true);
    	
    	if($padding_type == 'before-and-after') {
    		$padding *= 2;
    	}
    	
    	return $length + $padding;
    }
    
    function get_service_length_map()
    {
    	global $birchschedule;
		
    	$services_view = $birchschedule->services_view;
    	$text_map = $services_view->get_price_type_text_map();
    	
    	$query = new BIRS_Model_Query(array(
    			'post_type' => 'birs_service',
    			'order' => 'ASC',
    			'orderby' => 'title'),
    			array(
    				'meta_keys' => array(
    					'_birs_service_length_type_0',
    					'_birs_service_length_0',
    					'_birs_service_length_type_1',
    					'_birs_service_length_1',
    					'_birs_service_length_type_2',
    					'_birs_service_length_2',
    					'_birs_service_price',
    					'_birs_service_price_type'
    				),
    				'base_keys' => array('post_title')				
    			)
    	);
    	
    	$services = $query->query();
    	$service_map = array();
    	
    	foreach($services as $service)
    	{
    		$price_type = $service['_birs_service_price_type'];
    		if($price_type == 'fixed')
    		{
    			$price = apply_filters('birchschedule_price', $service['_birs_service_price']);
    		} 
    		else if ($price_type == 'dont-show')
    		{
    			$price = '';	
    		}
    		else
    		{
    			$price = $text_map[$price_type];
    		}
    		
    		$service_map[0][$service['ID']] = array(
    			'service_length' => $service['_birs_service_length_0'],
    			'service_length_type' => $service['_birs_service_length_type_0'],
    			'service_price' => $price,
    			'service_price_type' => $service['_birs_service_price_type'],
    			'service_title' => $service['post_title']
    		);

    		$service_map[1][$service['ID']] = array(
    			'service_length' => $service['_birs_service_length_1'],
    			'service_length_type' => $service['_birs_service_length_type_1'],
    			'service_price' => $price,
    			'service_price_type' => $service['_birs_service_price_type'],
    			'service_title' => $service['post_title']
    		);
    		
    		$service_map[2][$service['ID']] = array(
    				'service_length' => $service['_birs_service_length_2'],
    				'service_length_type' => $service['_birs_service_length_type_2'],
    				'service_price' => $price,
    				'service_price_type' => $service['_birs_service_price_type'],
    				'service_title' => $service['post_title']
    		);    		
    	}
    	
		return $service_map;
    }
    
    function get_location_service_map() {
        $map = array();
        $query = new BIRS_Model_Query(
                        array(
                            'post_type' => 'birs_location'
                        ),
                        array()
        );
        $locations = $query->query();
        $query = new BIRS_Model_Query(
                        array(
                            'post_type' => 'birs_service',
                            'order' => 'ASC',
                            'orderby' => 'title'
                        ),
                        array(
                            'base_keys' => array(
                                'post_title'
                            )
                        )
        );
        $services = $query->query();
        $service_map = array();
        foreach($services as $service_id => $service) {
            $service_map[$service_id] = $service->post_title;
        }
        foreach ($locations as $location) {
            $map[$location->ID] = $service_map;
        }
        return $map;
    }

    function get_location_staff_map() {
        $map = array();
        $query = new BIRS_Model_Query(
                        array(
                            'post_type' => 'birs_location'
                        ),
                        array()
        );
        $locations = $query->query();
        foreach ($locations as $location) {
            $map[$location->ID] = $location->get_assigned_staff();
        }
        return $map;
    }

    function get_service_staff_map() {
        $map = array();
        $query = new BIRS_Model_Query(
                        array(
                            'post_type' => 'birs_service'
                        ),
                        array(
                            'meta_keys' => array(
                                '_birs_assigned_staff'
                            )
                        )
        );
        $services = $query->query();
        foreach ($services as $service) {
            $assigned_staff_ids = $service->get_assigned_staff_ids();
            $query = new BIRS_Model_Query(
                            array(
                                'post_type' => 'birs_staff'
                            ),
                            array(
                                'base_keys' => array(
                                    'post_title'
                                )
                            )
            );
            $staff = $query->query();
            $assigned_staff = array();
            foreach ($staff as $thestaff) {
                if (array_key_exists($thestaff->ID, $assigned_staff_ids)) {
                    $assigned_staff[$thestaff->ID] = $thestaff->post_title;
                }
                $map[$service->ID] = $assigned_staff;
            }
        }
        return $map;
    }

    function ajax_query_appointments() {
        $start = $_GET['birs_time_start'];
        $start = $this->get_util()->get_wp_datetime($start)->format('U');
        $end = $_GET['birs_time_end'];
        $end = $this->get_util()->get_wp_datetime($end)->format('U');
        $location_id = $_GET['birs_location_id'];
        $staff_id = $_GET['birs_staff_id'];
        $meta_query = array(
            array('key' => '_birs_appointment_timestamp',
                'value' => $start,
                'compare' => '>=',
                'type' => 'SIGNED'
            ), array('key' => '_birs_appointment_timestamp',
                'value' => $end,
                'compare' => '<=',
                'type' => 'SIGNED'),
            array('key' => '_birs_appointment_location',
                'value' => $location_id,
                'type' => 'UNSIGNED')
        );
        if ($staff_id != -1) {
            $meta_query[] = array('key' => '_birs_appointment_staff',
                'value' => $staff_id,
                'type' => 'UNSIGNED');
        }
        $appointments = get_posts(
                array(
                    'post_type' => 'birs_appointment',
                    'nopaging' => true,
                    'meta_query' => $meta_query
                )
        );
        $apmts = array();
        foreach ($appointments as $appointment) {
            $post_id = $appointment->ID;
            $duration = get_post_meta($post_id, '_birs_appointment_duration', true);
            $duration = intval($duration);
            $price = get_post_meta($post_id, '_birs_appointment_price', true);
            $time_start = get_post_meta($post_id, '_birs_appointment_timestamp', true);
            $time_end = $time_start + $duration * 60;
            $time_start = $this->get_util()->get_wp_datetime($time_start)->format('c');
            $time_end = $this->get_util()->get_wp_datetime($time_end)->format('c');
            $apmt = array(
                'id' => $appointment->ID,
                'title' => $appointment->post_title,
                'start' => $time_start,
                'end' => $time_end,
                'allDay' => false,
                'editable' => true
            );
            $apmts[] = $apmt;
        }
        $apmts = apply_filters('birchschedule_query_appointments', $apmts, $staff_id);
        ?>
        <div id="birs_response">
            <?php
            echo json_encode($apmts);
            ?>
        </div>
        <?php
        exit;
    }

    function render_admin_page() {
        do_action('birchschedule_show_update_notice');
        ?>
        <div class="birchschedule wrap">
            <h2 id="birs_calendar_title">
                <?php _e('Calendar', 'birchschedule'); ?>&nbsp;
                <a href="javascript:void(0)"
                   id="birs_add_appointment"
                   class="add-new-h2">
                       <?php _e('Add Appointment', 'birchschedule'); ?>
                </a>
                <a href="javascript:void(0)"
                   id="birs_calendar_refresh"
                   class="add-new-h2">
                       <?php _e('Refresh', 'birchschedule'); ?>
                </a>
            </h2>
            <div id="birs_calendar_toolbar">
                <div id="birs_calendar_filter">
                    <label><?php _e('Location', 'birchschedule'); ?></label>
                    <select id="birs_calendar_location">
                        <?php $this->render_location_options(); ?>
                    </select>
                    <label><?php _e('Staff', 'birchschedule'); ?></label>
                    <select id="birs_calendar_staff">
                    </select>
                </div>
                <div class="clear"></div>
            </div>
            <div  id="birs_calendar"></div>
            <div id="birs_add_new_dialog">
            </div>
        </div>
        <?php
    }

    function get_client_details_html($html, $client_id) {
        $client_titles = $this->get_util()->get_client_title_options();
        $states = $this->get_util()->get_us_states();
        $countries = $this->get_util()->get_countries();
        $client_title = '';
        $address1 = '';
        $address2 = '';
        $city = '';
        $state = '';
        $province = '';
        $zip = '';
        $country = false;
        $default_country = apply_filters('birchschedule_default_country', 'US');
        if ($client_id) {
            $client_title = get_post_meta($client_id, '_birs_client_title', true);
            $address1 = get_post_meta($client_id, '_birs_client_address1', true);
            $address2 = get_post_meta($client_id, '_birs_client_address2', true);
            $city = get_post_meta($client_id, '_birs_client_city', true);
            $state = get_post_meta($client_id, '_birs_client_state', true);
            $province = get_post_meta($client_id, '_birs_client_province', true);
            $country = get_post_meta($client_id, '_birs_client_country', true);
            $zip = get_post_meta($client_id, '_birs_client_zip', true);
        }
        ob_start();
        ?>
        <ul>
            <li class="birs_form_field">
                <label><?php _e('Title', 'birchschedule'); ?></label>
                <div class="birs_field_content">
                    <select name="birs_client_title" id="birs_client_title">
                        <?php $this->get_util()->render_html_options($client_titles, $client_title); ?>
                    </select>
                    <input type="hidden" name="birs_client_fields[]" value="_birs_client_title" />
                </div>
            </li>
            <li class="birs_form_field">
                <label><?php _e('Address', 'birchschedule'); ?></label>
                <div class="birs_field_content">
                    <input type="text" name="birs_client_address1" id="birs_client_address1" value="<?php echo $address1; ?>">
                    <input type="text" name="birs_client_address2" id="birs_client_address2" value="<?php echo $address2; ?>">
                    <input type="hidden" name="birs_client_fields[]" value="_birs_client_address1" />
                    <input type="hidden" name="birs_client_fields[]" value="_birs_client_address2" />
                </div>
            </li>
            <li class="birs_form_field">
                <label><?php _e('City', 'birchschedule') ?></label>
                <div class="birs_field_content">
                    <input type="text" name="birs_client_city" id="birs_client_city" value="<?php echo $city; ?>">
                    <input type="hidden" name="birs_client_fields[]" value="_birs_client_city" />
                </div>
            </li>
            <li class="birs_form_field">
                <label><?php _e('State/Province', 'birchschedule') ?></label>
                <div class="birs_field_content">
                    <select name="birs_client_state" id ="birs_client_state">
                        <?php $this->get_util()->render_html_options($states, $state); ?>
                    </select>
                    <input type="hidden" name="birs_client_fields[]" value="_birs_client_state" />
                    <input type="text" name="birs_client_province" id="birs_client_province" value="<?php echo esc_attr($province); ?>" style="display: none;">
                    <input type="hidden" name="birs_client_fields[]" value="_birs_client_province" />
                </div>
            </li>
            <li class="birs_form_field">
                <label><?php _e('Country', 'birchschedule'); ?></label>
                <div class="birs_field_content">
                    <select name="birs_client_country" id="birs_client_country">
                        <?php
                        $this->get_util()->render_html_options($countries, $country, $default_country);
                        ?>
                    </select>
                    <input type="hidden" name="birs_client_fields[]" value="_birs_client_country" />
                </div>
            </li>
            <li class="birs_form_field">
                <label><?php _e('Zip Code', 'birchschedule'); ?></label>
                <div class="birs_field_content">
                    <input type="text" name="birs_client_zip" id="birs_client_zip" value="<?php echo $zip; ?>">
                    <input type="hidden" name="birs_client_fields[]" value="_birs_client_zip" />
                </div>
            </li>
        </ul>
        <?php
        $content = ob_get_clean();
        return $content;
    }

    function get_appointment_details_html($html, $appointment_id) {
        $notes = '';
        if ($appointment_id) {
            $notes = get_post_meta($appointment_id, '_birs_appointment_notes', true);
        }
        ob_start();
        ?>
        <ul>
            <li class="birs_form_field">
                <label>
                    <?php _e('Notes', 'birchschedule'); ?>
                </label>
                <div class="birs_field_content">
                    <textarea id="birs_appointment_notes" name="birs_appointment_notes"><?php echo $notes; ?></textarea>
                    <input type="hidden" name="birs_appointment_fields[]" value="_birs_appointment_notes" />
                </div>
            </li>
        </ul>
        <?php
        $content = ob_get_clean();
        return $content;
    }

    function ajax_render_edit_form() {
        if (isset($_REQUEST['birs_appointment_location'])) {
            $location_id = $_REQUEST['birs_appointment_location'];
        } else {
            $location_id = 0;
        }
        if (isset($_REQUEST['birs_appointment_staff'])) {
            $staff_id = $_REQUEST['birs_appointment_staff'];
        } else {
            $staff_id = 0;
        }
        $appointment_id = 0;
        $client_id = 0;
        if (isset($_GET['birs_appointment_id'])) {
            $appointment_id = $_GET['birs_appointment_id'];
            $client_id = get_post_meta($appointment_id, '_birs_appointment_client', true);
        }
        ?>
        <div id="birs_appointment_edit">
            <ul>
                <li><a href="#birs_appointment_info"><?php _e('General Info', 'birchschedule'); ?></a></li>
                <li><a href="#birs_appointment_details"><?php _e('Appointment Details', 'birchschedule'); ?></a></li>
                <li><a href="#birs_client_details"><?php _e('Client Details', 'birchschedule'); ?></a></li>
                <li><a href="#birs_appointment_payments_details"><?php _e('Payments', 'birchschedule'); ?></a></li>
            </ul>
            <form id="birs_appointment_form">
                <div class="wrap" id="birs_appointment_info">
                    <?php wp_nonce_field("birs_save_appointment-$appointment_id"); ?>
                    <?php wp_nonce_field("birs_delete_appointment-$appointment_id", 'birs_delete_appointment_nonce', false); ?>
                    <input type="hidden" name="birs_appointment_id" id="birs_appointment_id" value="<?php echo $appointment_id; ?>">
                    <input type="hidden" name="birs_appointment_location" id="birs_appointment_location" value="<?php echo $location_id; ?>">
                    <div id="birs_general_section_appointment">
                        <?php
                            echo apply_filters('birchschedule_general_appointment_section_admin_edit',
                                '', $appointment_id, $location_id, $staff_id);
                        ?>
                    </div>
                    <div class="splitter"></div>
                    <div id="birs_general_section_client">
                        <?php echo apply_filters('birchschedule_general_client_section_admin_edit', '', $client_id);
                        ?>
                    </div>
                </div>
                <div id="birs_appointment_details">
                    <?php
                    echo apply_filters('birchschedule_appointment_details_admin_edit', '', $appointment_id);
                    ?>
                </div>
                <div id="birs_client_details">
                    <?php
                    echo apply_filters('birchschedule_client_details_admin_edit', '', $client_id,
                        array('client_name_first', 'client_name_last', 'client_email', 'client_phone'));
                    ?>
                </div>
                <div id="birs_appointment_payments_details">
                    <?php
                    echo apply_filters('birchschedule_appointment_payments_details_admin_edit', '', $appointment_id);
                    ?>
                </div>
            </form>
        </div>
        <?php
        exit();
    }
    
    function get_appointment_duration_html($html, $appointment_duration) {
        ob_start();
        ?>
        <input type="hidden" id="birs_appointment_duration"
                name="birs_appointment_duration"
                value="<?php echo $appointment_duration; ?>" />
        <?php
        return ob_get_clean();
    }
    
    function get_general_appointment_section_html($html, $appointment_id,
        $location_id, $staff_id) {
        $timestamp = time();
        $price = 0;
        $service_id = 0;
        $date = '';
        $date4picker='';
        $time = 540;
        $appointment_duration = 0;
        if($appointment_id) {
            $timestamp = get_post_meta($appointment_id, '_birs_appointment_timestamp', true);
            $timestamp = $this->get_util()->get_wp_datetime($timestamp);
            $date4picker = $timestamp->format(get_option('date_format'));
            $date = $timestamp->format('m/d/Y');
            $time = $timestamp->format('H') * 60 + $timestamp->format('i');
    
            $appointment_duration = get_post_meta($appointment_id, '_birs_appointment_duration', true);
            $service_id = get_post_meta($appointment_id, '_birs_appointment_service', true);
            $staff_id = get_post_meta($appointment_id, '_birs_appointment_staff', true);
        }
        ob_start();
        ?>
        <ul>
            <li class="birs_form_field">
                <label>
                    <?php _e('Service', 'birchschedule'); ?>
                </label>
                <div class="birs_field_content">
                    <select id="birs_appointment_service" name="birs_appointment_service"
                        style="width:0;">
                        <?php $service_id = $this->render_service_options($location_id, $service_id); ?>
                    </select>
                </div>
            </li>
            <li class="birs_form_field">
                <label>
                    <?php _e('Staff', 'birchschedule'); ?>
                </label>
                <div class="birs_field_content">
                    <select id="birs_appointment_staff" name="birs_appointment_staff"
                        style="width:0;">
                        <?php $this->render_staff_options($location_id, $service_id, $staff_id); ?>
                    </select>
                    <div class="birs_error" id="birs_appointment_service_error"></div>
                </div>
            </li>
            <li class="birs_form_field">
                <label>
                    <?php _e('Date', 'birchschedule'); ?>
                </label>
                <div class="birs_field_content">
                    <input id="birs_appointment_datepicker" name="birs_appointment_datepicker" type="text" value="<?php echo $date4picker ?>">
                    <input id="birs_appointment_date" name="birs_appointment_date" type="hidden" value="<?php echo $date ?>">
                    <div class="birs_error" id="birs_appointment_date_error"></div>
                </div>
            </li>
            <li class="birs_form_field">
                <label>
                    <?php _e('Time', 'birchschedule'); ?>
                </label>
                <div class="birs_field_content">
                    <select id="birs_appointment_time" name="birs_appointment_time"
                        style="width:0;">
                        <?php $this->render_time_options($time); ?>
                    </select>
                    <div class="birs_error" id="birs_appointment_time_error"></div>
                </div>
            </li>
            <?php echo apply_filters('birchschedule_appointment_details_admin_edit_duration', '', $appointment_duration); ?>
        </ul>
        <?php
        $content = ob_get_clean();
        return $content;
    }

    function get_general_client_section_html($html, $client_id) {
        $first_name = '';
        $last_name = '';
        $email = '';
        $phone = '';
        if ($client_id) {
            $first_name = get_post_meta($client_id, '_birs_client_name_first', true);
            $last_name = get_post_meta($client_id, '_birs_client_name_last', true);
            $email = get_post_meta($client_id, '_birs_client_email', true);
            $phone = get_post_meta($client_id, '_birs_client_phone', true);
        }
        ob_start();
        ?>
        <ul>
            <li class="birs_form_field">
                <label><?php _e('First Name', 'birchschedule'); ?></label>
                <div class="birs_field_content">
                    <input type="text" id="birs_client_name_first" name="birs_client_name_first" value="<?php echo $first_name; ?>">
                    <input type="hidden" name="birs_client_fields[]" value="_birs_client_name_first" />
                    <div class="birs_error" id="birs_client_name_first_error"></div>
                </div>
            </li>
            <li class="birs_form_field">
                <label><?php _e('Last Name', 'birchschedule'); ?></label>
                <div class="birs_field_content">
                    <input type="text" id="birs_client_name_last" name="birs_client_name_last" value="<?php echo $last_name; ?>">
                    <input type="hidden" name="birs_client_fields[]" value="_birs_client_name_last" />
                    <div class="birs_error" id="birs_client_name_last_error"></div>
                </div>
            </li>
            <li class="birs_form_field">
                <label><?php _e('Email', 'birchschedule'); ?></label>
                <div class="birs_field_content">
                    <input type="text" id="birs_client_email" name="birs_client_email" value="<?php echo $email; ?>">
                    <input type="hidden" name="birs_client_fields[]" value="_birs_client_email" />
                    <div class="birs_error" id="birs_client_email_error"></div>
                </div>
            </li>
            <li class="birs_form_field">
                <label><?php _e('Phone', 'birchschedule'); ?></label>
                <div class="birs_field_content">
                    <input type="text" id="birs_client_phone" name="birs_client_phone" value="<?php echo $phone; ?>">
                    <input type="hidden" name="birs_client_fields[]" value="_birs_client_phone" />
                    <div class="birs_error" id="birs_client_phone_error"></div>
                </div>
            </li>
        </ul>
        <?php
        $html = ob_get_clean();
        return $html;
    }

    function save_appointment() {
        if (isset($_POST['birs_appointment_id'])) {
            $appointment_id = $_POST['birs_appointment_id'];
        } else {
            $appointment_id = 0;
        }
        if (isset($_POST['birs_appointment_fields'])) {
            $fields = $_POST['birs_appointment_fields'];
        } else {
            $fields = array();
        }
        $fields = array_merge($fields, array(
            '_birs_appointment_service', '_birs_appointment_staff',
            '_birs_appointment_location', '_birs_appointment_price',
            '_birs_appointment_timestamp', '_birs_appointment_duration',
            '_birs_appointment_padding_before', '_birs_appointment_padding_after',
            '_birs_appointment_client', '_birs_appointment_payment_status',
			'_birs_appointment_client_type'
                ));
        $appointment = new BIRS_Appointment($appointment_id, array(
                    'meta_keys' => $fields,
                    'base_keys' => array(
                        'post_title'
                    )
                ));
        $appointment->copyFromRequest($_POST);
        $client_id = $this->save_client();
        $appointment['_birs_appointment_client'] = $client_id;
        $appointment_id = $appointment->save();
        
        $payments = array();
        if(isset($_POST['birs_appointment_payments'])) {
            $payments = $_POST['birs_appointment_payments'];
        }
        do_action('birchschedule_save_appointment_payments', 
            $appointment_id, $client_id, $payments);
        return $appointment_id;
    }
    
    function save_multi_appointment() {
		if (isset($_POST['birs_appointment_fields'])) {
			$fields = $_POST['birs_appointment_fields'];
		} else {
			$fields = array();
		}
		$fields = array_merge($fields, array(
				'_birs_appointment_service', '_birs_appointment_staff',
				'_birs_appointment_location', '_birs_appointment_price',
				'_birs_appointment_timestamp', '_birs_appointment_duration',
				'_birs_appointment_padding_before', '_birs_appointment_padding_after',
				'_birs_appointment_client', '_birs_appointment_payment_status',
				'_birs_appointment_client_type'
		));
		
		$client_id = $this->save_client();
		$appointment_id = array();
		if( isset($_POST['birs_appointment_service']) && is_array($_POST['birs_appointment_service']) ){
			$services = $_POST['birs_appointment_service'];
			for($i = 0; $i < sizeof($services); $i++)
			{
		        $appointment = new BIRS_Appointment(0, array(
		                    'meta_keys' => $fields,
		                    'base_keys' => array(
		                        'post_title'
		                    )
		                ));
		        $appointment->copyFromRequest($_POST);
		        $appointment['_birs_appointment_service'] = $services[$i];
		        $appointment['_birs_appointment_client'] = $client_id;
		        $appointment_id[] = $appointment->save();
		        
		        //echo $appointment_id. "<br/>";
			}
		}
        return $appointment_id;
    }

    function ajax_save_appointment() {
        if (isset($_POST['birs_appointment_id'])) {
            $appointment_id = $_POST['birs_appointment_id'];
        } else {
            $appointment_id = 0;
        }
        $permitted = check_ajax_referer("birs_save_appointment-$appointment_id", '_wpnonce', false);
        if($permitted) {
            $errors = apply_filters('birchschedule_validate_booking_info_admin', array());
            if (!$errors) {
                $this->save_appointment();
            }
        }
        ?>
        <div id="birs_response">
            <?php if ($errors): ?>
                <div id="birs_errors">
                    <?php foreach ($errors as $error_id => $message): ?>
                        <p id="<?php echo $error_id; ?>"><?php echo $message; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        die;
    }

    function ajax_delete_appointment() {
        $appointment_id = $_REQUEST['birs_appointment_id'];
        check_ajax_referer("birs_delete_appointment-$appointment_id");
        $appointment = new BIRS_Appointment($appointment_id);
        $appointment->delete();
        die;
    }

    function save_client() {
        if (isset($_POST['birs_client_fields'])) {
            $fields = $_POST['birs_client_fields'];
        } else {
            $fields = array();
        }
        $client = new BIRS_Client(0, array(
                    'meta_keys' => $fields,
                    'base_keys' => array(
                        'post_title'
                    )
                ));
        $client->copyFromRequest($_POST);
        $client->load_id_by_email();
        $client_id = $client->save();

        return $client_id;
    }
    
    function validate_appointment_info() {
        $errors = array();
        $staff_text = 'service provider';
        if ($_POST['action'] == 'birs_save_appointment') {
            $staff_text = 'staff';
        }
        if (!isset($_POST['birs_appointment_staff']) || !isset($_POST['birs_appointment_service'])) {
            if ($_POST['action'] == 'birs_save_appointment') {
                $errors['birs_appointment_service'] = __('Please select a service and a staff', 'birchschedule');
            } else {
                $errors['birs_appointment_service'] = __('Please select a service and a service provider', 'birchschedule');
            }
        }
        if (!isset($_POST['birs_appointment_date']) || !$_POST['birs_appointment_date']) {
            $errors['birs_appointment_date'] = __('Date is required', 'birchschedule');
        }
        if (!isset($_POST['birs_appointment_time']) || !$_POST['birs_appointment_time']) {
            $errors['birs_appointment_time'] = __('Time is required', 'birchschedule');
        }
        if (isset($_POST['birs_appointment_date']) && $_POST['birs_appointment_date'] &&
            isset($_POST['birs_appointment_time']) && $_POST['birs_appointment_time']) {
            $datetime = array(
                'date' => $_POST['birs_appointment_date'],
                'time' => $_POST['birs_appointment_time']
            );
            $datetime = $this->get_util()->get_wp_datetime($datetime);
            if (!$datetime) {
                $errors['birs_appointment_datetime'] = __('Date & time is incorrect', 'birchschedule');
            } else {
                $timestamp = $datetime->format('U');
                $_POST['birs_appointment_timestamp'] = $timestamp;
            }
        }
        return $errors;
    }

    function validate_data() {
        $errors = $this->validate_appointment_info();

        if (!$_POST['birs_client_name_first']) {
            $errors['birs_client_name_first'] = __('First name is required', 'birchschedule');
        }
        if (!$_POST['birs_client_name_last']) {
            $errors['birs_client_name_last'] = __('Last name is required', 'birchschedule');
        }
        if (!$_POST['birs_client_email']) {
            $errors['birs_client_email'] = __('Email is required', 'birchschedule');
        } else if (!is_email($_POST['birs_client_email'])) {
            $errors['birs_client_email'] = __('Email is incorrect', 'birchschedule');
        }
        if (!$_POST['birs_client_phone']) {
            $errors['birs_client_phone'] = __('Phone is required', 'birchschedule');
        }

        return $errors;
    }
	/*
    function get_service_length($service_id) {
        $length = get_post_meta($service_id, '_birs_service_length', true);
        $length_type = get_post_meta($service_id, '_birs_service_length_type', true);
        if ($length_type == 'hours') {
            $length = $length * 60;
        }
        $padding = get_post_meta($service_id, '_birs_service_padding', true);
        $padding_type = get_post_meta($service_id, '_birs_service_padding_type', true);
        if ($padding_type == 'before-and-after') {
            $padding *= 2;
        }
        return $length + $padding;
    }*/

    function map_service($service) {
        return $service->post_title;
    }

    function render_location_options() {
        $query = new BIRS_Model_Query(
                        array(
                            'post_type' => 'birs_location',
                            'order' => 'ASC',
                            'orderby' => 'title'
                        ),
                        array(
                            'base_keys' => array('post_title')
                        )
        );
        $locations = array_map(array($this, 'map_service'), $query->query());
        $this->get_util()->render_html_options($locations, false);
        return key($locations);
    }

    function render_service_options($location_id, $selection) {
        $map = $this->get_location_service_map();
        $services = $map[$location_id];
        $this->get_util()->render_html_options($services, $selection);
        if ($selection) {
            return $selection;
        } else {
            return key($services);
        }
    }

    function render_time_options($selection) {
        $options = $this->get_util()->get_time_options(5);
        foreach ($options as $val => $text) {
            if ($selection == $val) {
                $selected = ' selected="selected" ';
            } else {
                $selected = '';
            }
            echo "<option value='$val' $selected>$text</option>";
        }
    }

    function render_staff_options($location, $service, $selection) {
        $locatoin_map = $this->get_location_staff_map();
        if (isset($locatoin_map[$location])) {
            $location_staff = $locatoin_map[$location];
        } else {
            $location_staff = array();
        }
        $service_map = $this->get_service_staff_map();
        if (isset($service_map[$service])) {
            $service_staff = $service_map[$service];
        } else {
            $service_staff = array();
        }
        $staff = array_intersect_assoc($service_staff, $location_staff);
        $this->get_util()->render_html_options($staff, $selection);
    }

}
?>