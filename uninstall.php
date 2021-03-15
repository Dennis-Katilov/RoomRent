<?php

if(!defined('WP_UNINSTALL_PLUGIN')){
    die;
}

//Delete post type from db

$rooms = get_posts(array('post_type'=>'room','numberposts'=>-1));
foreach($rooms as $room){
    wp_delete_post($room->ID,true);
}