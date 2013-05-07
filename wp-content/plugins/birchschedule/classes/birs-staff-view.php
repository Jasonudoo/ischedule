<?php

class BIRS_Staff_View extends BIRS_Content_View {

    var $time_options;

    function __construct() {
        parent::__construct();
        $this->init_time_options();
    }

    function get_content_type() {
        return 'birs_staff';
    }

    function process_content($post_id, $post) {
        $this->save_field_string($post_id, 'birs_staff_name');
        $this->save_field_string($post_id, 'birs_staff_description');
        $this->save_field_array($post_id, 'birs_staff_schedule');
        $this->save_assigned_services($post_id);
        $this->handle_errors();
    }
    
    function save_assigned_services($post_id) {
        $this->save_content_relations($post_id, 'birs_service', 'birs_assigned_services', 'birs_assigned_staff');
    }

    function get_edit_columns($columns) {
        $columns = array();

        $columns["cb"] = "<input type=\"checkbox\" />";
        $columns["title"] = __("Staff Name", 'birchschedule');
        $columns["description"] = __("Description", 'birchschedule');
        return $columns;
    }

    function get_updated_messages($messages) {
        global $post, $post_ID;

        $messages['birs_staff'] = array(
            0 => '', // Unused. Messages start at index 1.
            1 => __('Staff updated.', 'birchschedule'),
            2 => __('Custom field updated.', 'birchschedule'),
            3 => __('Custom field deleted.', 'birchschedule'),
            4 => __('Staff updated.', 'birchschedule'),
            5 => isset($_GET['revision']) ? sprintf(__('Staff restored to revision from %s', 'birchschedule'), wp_post_revision_title((int) $_GET['revision'], false)) : false,
            6 => __('Staff updated.', 'birchschedule'),
            7 => __('Staff saved.', 'birchschedule'),
            8 => __('Staff submitted.', 'birchschedule'),
            9 => sprintf(__('Staff scheduled for: <strong>%1$s</strong>.', 'birchschedule'), date_i18n(__('M j, Y @ G:i', 'birchschedule'), strtotime($post->post_date))),
            10 => __('Staff draft updated.', 'birchschedule')
        );

        return $messages;
    }

    function init() {
        parent::init();

        register_post_type($this->get_content_type(), array(
            'labels' => array(
                'name' => __('Staff', 'birchschedule'),
                'singular_name' => __('Staff', 'birchschedule'),
                'add_new' => __('Add Staff', 'birchschedule'),
                'add_new_item' => __('Add New Staff', 'birchschedule'),
                'edit' => __('Edit', 'birchschedule'),
                'edit_item' => __('Edit Staff', 'birchschedule'),
                'new_item' => __('New Staff', 'birchschedule'),
                'view' => __('View Staff', 'birchschedule'),
                'view_item' => __('View Staff', 'birchschedule'),
                'search_items' => __('Search Staff', 'birchschedule'),
                'not_found' => __('No Staff found', 'birchschedule'),
                'not_found_in_trash' => __('No Staff found in trash', 'birchschedule'),
                'parent' => __('Parent Staff', 'birchschedule')
            ),
            'description' => __('This is where staff are stored.', 'birchschedule'),
            'public' => false,
            'show_ui' => true,
            'capability_type' => 'post',
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'show_in_menu' => 'birchschedule_schedule',
            'hierarchical' => false,
            'show_in_nav_menus' => false,
            'rewrite' => false,
            'query_var' => true,
            'supports' => array('title', 'editor'),
            'has_archive' => false
                )
        );
    }
    
    function admin_init() {
        parent::admin_init();
        add_action('wp_ajax_birs_new_staff_schedule', array(&$this, 'ajax_new_staff_schedule'));

    }

    function create_admin_panel() {
        parent::create_admin_panel();
        add_meta_box('birchschedule-work-schedule', __('Work Schedule', 'birchschedule'), array(&$this, 'render_work_schedule'), 'birs_staff', 'normal', 'default');
        add_meta_box('birchschedule-staff-services', __('Services', 'birchschedule'), array(&$this, 'render_assign_services'), 'birs_staff', 'side', 'default');
    }

    function get_admin_scripts() {
        $params = array(
            'ajax_url' => admin_url('admin-ajax.php'),
        );
        return array(
            array('birs_admin_staff', "birs_staff_view_params", $params) 
        );
    }

    function init_time_options($interval = 15) {
        if (!isset($this->time_options)) {
            $this->time_options = $this->get_util()->get_time_options();
        }
    }

    function ajax_new_staff_schedule() {
        $location_id = $_POST['birs_location_id'];
        $uid = uniqid();
        $schedule = array(
            'minutes_start' => 540,
            'minutes_end' => 1020,
            'weeks' => array(
                1 => 'on',
                2 => 'on',
                3 => 'on',
                4 => 'on',
                5 => 'on'
            )
        );
        $this->render_schedule($location_id, $uid, $schedule);
        die;
    }

    function render_schedule($location_id, $uid, $schedule) {
        global $birchschedule;
        $time_options = $birchschedule->staff_view->time_options;
        $start = $schedule['minutes_start'];
        $end = $schedule['minutes_end'];
        $weeks = $this->get_util()->get_weekdays_short();
        $schedule_dom_id = 'birs_schedule_' . $uid;
        $start_of_week = apply_filters('birchschedule_first_day_of_week', 0);
        ?>
        <div id="<?php echo $schedule_dom_id; ?>"
            class="birs_schedule_item">
            <?php do_action('birchschedule_render_staff_schedule_item_before', $location_id, $uid, $schedule); ?>
            <ul>
                <li>
                    <span class="birs_schedule_field_label"><?php _e('From', 'birchschedule'); ?></span>
                    <div class="birs_schedule_field_content">
                        <select
                            name="birs_staff_schedule[<?php echo $location_id; ?>][schedules][<?php echo $uid; ?>][minutes_start]">
                                <?php $this->get_util()->render_html_options($time_options, $start); ?>
                        </select>
                        <a href="javascript:void(0);"
                            data-schedule-id="<?php echo $uid; ?>"
                            class="birs_schedule_delete">
                            <?php echo "Delete"; ?>
                        </a>
                    </div>
                </li>
                <li>
                    <span class="birs_schedule_field_label"><?php _e('To', 'birchschedule'); ?></span>
                    <div class="birs_schedule_field_content">
                        <select
                            name="birs_staff_schedule[<?php echo $location_id; ?>][schedules][<?php echo $uid; ?>][minutes_end]">
                                <?php $this->get_util()->render_html_options($time_options, $end); ?>
                        </select>
                    </div>
                </li>
                <li>
                    <span class="birs_schedule_field_label"></span>
                    <div class="birs_schedule_field_content">
                    <?php
                        foreach($weeks as $week_value => $week_name): 
                            if($week_value < $start_of_week) {
                                continue;
                            }
                            if (isset($schedule['weeks']) && isset($schedule['weeks'][$week_value])) {
                                $checked_attr = ' checked="checked" ';
                            } else {
                                $checked_attr = '';
                            }
                    ?>
                        <label>
                            <input type="checkbox" 
                                name="birs_staff_schedule[<?php echo $location_id; ?>][schedules][<?php echo $uid; ?>][weeks][<?php echo $week_value; ?>]"
                                <?php echo $checked_attr; ?>/>
                                <?php echo $week_name; ?>
                        </label>
                    <?php endforeach; ?>
                    <?php
                        foreach($weeks as $week_value => $week_name): 
                            if($week_value >= $start_of_week) {
                                continue;
                            }
                            if (isset($schedule['weeks']) && isset($schedule['weeks'][$week_value])) {
                                $checked_attr = ' checked="checked" ';
                            } else {
                                $checked_attr = '';
                            }
                    ?>
                        <label>
                            <input type="checkbox" 
                                name="birs_staff_schedule[<?php echo $location_id; ?>][schedules][<?php echo $uid; ?>][weeks][<?php echo $week_value; ?>]"
                                <?php echo $checked_attr; ?>/>
                                <?php echo $week_name; ?>
                        </label>
                    <?php endforeach; ?>
                    </div>
                </li>
            </ul>
            <?php do_action('birchschedule_render_staff_schedule_item_after', $location_id, $uid, $schedule); ?>
        </div>
        <script type="text/javascript">
            jQuery(document).ready( function($) {
                var scheduleId = '<?php echo $schedule_dom_id; ?>';
                $('#' + scheduleId + ' .birs_schedule_delete').click(function() {
                    $('#' + scheduleId).remove();
                });
            });
        </script>
        <?php
    }
    
    function get_schedules($staff_id, $location_id) {
        $schedules = array();
        $staff = new BIRS_Staff($staff_id, array(
                    'meta_keys' => array('_birs_staff_schedule')
                ));
        $staff->load();
        $schedule = $staff->get_schedule_by_location($location_id);
        if(isset($schedule['schedules'])) {
            $schedules = $schedule['schedules'];
        }
        return $schedules;
    }
    
    function render_timetable($staff_id, $location_id) {
        $schedules = $this->get_schedules($staff_id, $location_id);
        ?>
        <div style="margin-bottom:20px;">
            <div id="<?php echo 'birs_schedule_' . $location_id ?>">
            <?php foreach($schedules as $uid => $schedule): ?>
                <?php $this->render_schedule($location_id, $uid, $schedule); ?>
            <?php endforeach; ?>
            </div>
            <div class="birs_schedule_new_box">
                <a href="javascript:void(0);" 
                    class="birs_schedule_new"
                    data-location-id="<?php echo $location_id; ?>">
                    <?php _e('+ Add Schedule', 'birchschedule'); ?>
                </a>
            </div>
        </div>
        <?php
    }

    function render_work_schedule($post) {
        do_action('birchschedule_render_work_schedule');
        $weeks = $this->get_util()->get_weekdays_short();
        $locations = get_posts(
                array(
                    'post_type' => 'birs_location',
                    'nopaging' => true,
                    'orderby' => 'post_title'
                )
        );
        $schedule = get_post_meta($post->ID, '_birs_staff_schedule', true);
        if (!isset($schedule)) {
            $schedule = array();
        } else {
            $schedule = unserialize($schedule);
        }
        ?>
        <div class="panel-wrap birchschedule">
            <?php if (sizeof($locations) > 0): ?>
                <div id="location_list">
                    <ul>
                        <?php
                        $index = 0;
                        foreach ($locations as $location):
                            ?>
                            <li data-location-id="<?php echo $location->ID; ?>"
                                <?php if ($index++ === 0) echo ' class="current" '; ?>><a><?php echo $location->post_title; ?>
                                </a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div id="timetable">
                    <?php
                    $index = 0;
                    foreach ($locations as $location):
                        if (!isset($schedule[$location->ID])) {
                            $location_schedule = array();
                        } else {
                            $location_schedule = $schedule[$location->ID];
                        }
                    ?>
                    <div data-location-id="<?php echo $location->ID; ?>"
                         <?php if ($index++ !== 0) echo 'style="display:none;"'; ?>>
                        <?php
                            $this->render_timetable($post->ID, $location->ID);
                            do_action('birchschedule_staff_timetable_after', $post->ID, $location->ID);
                        ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="clear"></div>
            <?php else: ?>
                <p>
                    <?php
                    printf(__('There is no locations. Click %s here %s to add one.', 'birchschedule'), '<a
                        href="post-new.php?post_type=birs_location">', '</a>');
                    ?>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }

    function render_service_checkboxes($services, $assigned_services) {
        foreach ($services as $service) {
            if (array_key_exists($service->ID, $assigned_services)) {
                $checked = 'checked="checked"';
            } else {
                $checked = '';
            }
            echo '<li><label>' .
            "<input type=\"checkbox\" " .
            "name=\"birs_assigned_services[$service->ID]\" $checked >" .
            $service->post_title .
            '</label></li>';
        }
    }

    function render_assign_services($post) {
        $services = get_posts(
                array(
                    'post_type' => 'birs_service',
                    'nopaging' => true
                )
        );
        $assigned_services = get_post_meta($post->ID, '_birs_assigned_services', true);
        $assigned_services = unserialize($assigned_services);
        if ($assigned_services === false) {
            $assigned_services = array();
        }
        ?>
        <div class="panel-wrap birchschedule">
            <?php if (sizeof($services) > 0): ?>
                <p><?php _e('Assign services that this staff can perform:', 'birchschedule'); ?></p>
                <div><ul>
                        <?php $this->render_service_checkboxes($services, $assigned_services); ?>
                    </ul></div>
            <?php else: ?>
                <p>
                    <?php
                    printf(__('There is no services to assign. Click %s here %s to add one.', 'birchschedule'), '<a
                        href="post-new.php?post_type=birs_service">', '</a>');
                    ?>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }

}