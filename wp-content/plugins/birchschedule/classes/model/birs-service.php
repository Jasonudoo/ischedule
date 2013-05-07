<?php

class BIRS_Service extends BIRS_Model {

    public function __construct($id, $options) {
        parent::__construct($id, $options);
        $this['post_type'] = 'birs_service';
    }

    public function get_service_length() {
        $length = $this['_birs_service_length'];
        $length_type = $this['_birs_service_length_type'];
        if ($length_type == 'hours') {
            $length = $length * 60;
        }
        return $length;
    }
    
    public function get_padding($type) {
        $padding_type = $this['_birs_service_padding_type'];
        if ($padding_type === 'before-and-after' || $padding_type === $type) {
            $padding = $this['_birs_service_padding'];
        } else {
            $padding = 0;
        }
        return $padding;
    }

    public function get_padding_before() {
        return $this->get_padding('before');
    }

    public function get_padding_after() {
        return $this->get_padding('after');
    }

    public function get_assigned_staff_ids() {
        $assigned_staff = $this['_birs_assigned_staff'];
        $assigned_staff = unserialize($assigned_staff);
        $assigned_staff = $assigned_staff ? $assigned_staff : array();
        return $assigned_staff;
    }

    public function pre_save() {
        
    }
    
    public function post_save() {
        
    }

}

?>