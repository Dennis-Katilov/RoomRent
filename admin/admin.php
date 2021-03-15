<h1 class="roomrent_title"><?php esc_html_e('Booking Settings','roomrent'); ?></h1>
<?php settings_errors(); ?>
<div class="roomrent_content">
    <form method="post" action="options.php">
        <?php 
            settings_fields('booking_settings');
            do_settings_sections('roomrent_settings');
            submit_button();
        ?>
    </form>
</div>