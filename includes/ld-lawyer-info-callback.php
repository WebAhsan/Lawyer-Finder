<?php


function ld_lawyer_info_callback($post)
{
    $firm_name = get_post_meta($post->ID, '_ld_firm_name', true);
    $city = get_post_meta($post->ID, '_ld_city', true);
    $state = get_post_meta($post->ID, '_ld_state', true);
    $specializations = get_post_meta($post->ID, '_ld_specializations', true);
    $website = get_post_meta($post->ID, '_ld_website', true);
    $number = get_post_meta($post->ID, '_ld_number', true);
    $address = get_post_meta($post->ID, '_ld_address', true);
?>

    <p><label>Firm Name:</label><br><input type="text" name="ld_firm_name" value="<?php echo esc_attr($firm_name); ?>" style="width: 100%;"></p>
    <p><label>City:</label><br><input type="text" name="ld_city" value="<?php echo esc_attr($city); ?>" style="width: 100%;"></p>
    <p><label>State:</label><br><input type="text" name="ld_state" value="<?php echo esc_attr($state); ?>" style="width: 100%;"></p>
    <p><label>Area of Practice (comma separated):</label><br><input type="text" name="ld_specializations" value="<?php echo esc_attr($specializations); ?>" style="width: 100%;"></p>
    <p><label>Website:</label><br><input type="url" name="ld_website" value="<?php echo esc_attr($website); ?>" style="width: 100%;"></p>
    <p><label>Phone Number:</label><br><input type="text" name="ld_number" value="<?php echo esc_attr($number); ?>" style="width: 100%;"></p>
    <p><label>Address:</label><br><input type="text" name="ld_address" value="<?php echo esc_attr($address); ?>" style="width: 100%;"></p>

<?php
}


function ld_save_lawyer_meta($post_id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    $fields = [
        'ld_firm_name',
        'ld_city',
        'ld_state',
        'ld_specializations',
        'ld_website',
        'ld_number',
        'ld_address',
    ];

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
        }
    }
}
add_action('save_post', 'ld_save_lawyer_meta');
