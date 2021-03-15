<?php
/*
Plugin Name: RoomRent
Plugin URI: https://github.com/Dennis-Katilov
Description: WP plugin to apartment booking
Version: 1.0
Author: Dennis Katilov
Author URI: https://www.linkedin.com/in/dennis-katilov/
License: GPLv2 or later
Text Domain: roomrent
*/

if(!defined('ABSPATH')){
    die;
}

class RoomRent
{

    public function register(){
        
        // register post type
        add_action('init',[$this,'custom_post_type']);

        //enqueue
        add_action('admin_enqueue_scripts',[$this,'enqueue_admin']);
        add_action('wp_enqueue_scripts',[$this,'enqueue_front']);

        //Load template
        add_filter('template_include', [$this,'room_template']);

        //Add menu admin
        add_action('admin_menu', [$this,'add_admin_menu']);

        //Add links to plugin page
        add_filter('plugin_action_links_'.plugin_basename(__FILE__), [$this,'add_plugin_setting_link']);  

        add_action('admin_init',[$this,'settings_init']);
    }


    static function activation(){
        
        //update rewrite rules
        flush_rewrite_rules();
    }

    static function deactivation(){

        //update rewrite rules
        flush_rewrite_rules();
    }

    public function get_terms_hierarchical($tax_name,$current_term){

        $taxanomy_terms = get_terms($tax_name,['hide_empty'=>false,'parent'=>0]);

        if(!empty($taxanomy_terms)){
            foreach($taxanomy_terms as $term){
                if($current_term == $term->term_id){
                    echo '<option value="'.$term->term_id.'" selected>'.$term->name.'</option>';
                } else {
                    echo '<option value="'.$term->term_id.'">'.$term->name.'</option>';
                }
               

                $child_terms = get_terms($tax_name,['hide_empty'=>false,'parent'=>$term->term_id]);

                if(!empty($child_terms)){
                    foreach($child_terms as $child){
                        echo '<option value="'.$child->term_id.'"> - '.$child->name.'</option>';
                    }
                }
            }
        }
    }

    //Register settings
    public function settings_init(){

        register_setting('booking_settings','booking_settings_options');

        add_settings_section('booking_settings_section', esc_html__('Settings','roomrent'), [$this, 'settings_section_html'], 'roomrent_settings');

        add_settings_field('posts_per_page', esc_html__('Posts per page','roomrent'), [$this, 'posts_per_page_html'], 'roomrent_settings', 'booking_settings_section');
        add_settings_field('title_for_rooms', esc_html__('Archive page title','roomrent'), [$this, 'title_for_rooms_html'], 'roomrent_settings', 'booking_settings_section');

    }

    //Settings section html
    public function settings_section_html(){
       // echo esc_html__("Hello, world!", 'roomrent');
    }

    //Settings fields HTML
    public function posts_per_page_html(){
        $options = get_option('booking_settings_options'); ?>

        <input type="text" name="booking_settings_options[posts_per_page]" value="<?php echo isset($options['posts_per_page']) ? $options['posts_per_page'] : "";  ?>" />

    <?php }

    public function title_for_rooms_html(){
        $options = get_option('booking_settings_options'); ?>

        <input type="text" name="booking_settings_options[title_for_rooms]" value="<?php echo isset($options['title_for_rooms']) ? $options['title_for_rooms'] : "";  ?>" />

    <?php }


    //Add settings link to plugin page
    public function add_plugin_setting_link($link){
        $custom_link = '<a href="admin.php?page=roomrent_settings">'.esc_html__('Settings','roomrent').'</a>';
        array_push($link, $custom_link);
        return $link;
    }

    //Add menu page
    public function add_admin_menu(){
        add_menu_page(
            esc_html__( 'RoomRent Settings Page', 'roomrent' ),
            esc_html__('RoomRent','roomrent'),
            'manage_options',
            'roomrent_settings',
            [$this, 'roomrent_page'],
            'dashicons-admin-multisite',
            100
        );
    }

    //RoomRent Admin HTML
    public function roomrent_page(){
        require_once plugin_dir_path(__FILE__).'admin/admin.php';
    }
    
    //Custom template for rooms
    public function room_template($template){

        if(is_post_type_archive('room')){
            $theme_files = ['archive-room.php','roomrent/archive-room.php'];
            $exist = locate_template( $theme_files, false);
            if($exist != ''){
                return $exist;
            } else {
                return plugin_dir_path(__FILE__).'templates/archive-room.php';
            }
        }
        return $template;
    }

    //Enqueue Admin
    public function enqueue_admin(){
        wp_enqueue_style('roomrentStyle', plugins_url('/assets/admin/styles.css', __FILE__));
        wp_enqueue_script('roomrentScript', plugins_url('/assets/admin/scripts.js', __FILE__));
    }

    //Enqueue Front
    public function enqueue_front(){
        wp_enqueue_style('roomrentStyle', plugins_url('/assets/front/styles.css', __FILE__));
        wp_enqueue_script('roomrentScript', plugins_url('/assets/front/scripts.js', __FILE__));
    }

    //Register CPT
    public function custom_post_type(){
        register_post_type('room',
            [
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug'=>'rooms'],
            'label'  => esc_html__( 'Room', 'roomrent' ),
            'supports' => ['title', 'editor', 'thumbnail']
            ]
        );


        $labels = array(
            'name'              => _x( 'Locations', 'taxonomy general name', 'roomrent' ),
            'singular_name'     => _x( 'Location', 'taxonomy singular name', 'roomrent' ),
            'search_items'      => __( 'Search Locations', 'roomrent' ),
            'all_items'         => __( 'All Locations', 'roomrent' ),
            'parent_item'       => __( 'Parent Location', 'roomrent' ),
            'parent_item_colon' => __( 'Parent Location:', 'roomrent' ),
            'edit_item'         => __( 'Edit Location', 'roomrent' ),
            'update_item'       => __( 'Update Location', 'roomrent' ),
            'add_new_item'      => __( 'Add New Location', 'roomrent' ),
            'new_item_name'     => __( 'New Location Name', 'roomrent' ),
            'menu_name'         => __( 'Location', 'roomrent' ),
        );
     
        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'rooms/location' ),
        );
        register_taxonomy('location','room',$args);


        $labels_type = array(
            'name'              => _x( 'Types', 'taxonomy general name', 'roomrent' ),
            'singular_name'     => _x( 'Type', 'taxonomy singular name', 'roomrent' ),
            'search_items'      => __( 'Search Types', 'roomrent' ),
            'all_items'         => __( 'All Types', 'roomrent' ),
            'parent_item'       => __( 'Parent Type', 'roomrent' ),
            'parent_item_colon' => __( 'Parent Type:', 'roomrent' ),
            'edit_item'         => __( 'Edit Type', 'roomrent' ),
            'update_item'       => __( 'Update Type', 'roomrent' ),
            'add_new_item'      => __( 'Add New Type', 'roomrent' ),
            'new_item_name'     => __( 'New Type Name', 'roomrent' ),
            'menu_name'         => __( 'Type', 'roomrent' ),
        );
     
        $args_type = array(
            'hierarchical'      => false,
            'labels'            => $labels_type,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'room/location' ),
        );
        register_taxonomy('type','room',$args_type);
    }
}
if(class_exists('RoomRent')){
    $roomrent = new RoomRent();
    $roomrent->register();
}

register_activation_hook( __FILE__, array( $roomrent, 'activation' ) );
register_deactivation_hook( __FILE__, array( $roomrent, 'deactivation' ) );
