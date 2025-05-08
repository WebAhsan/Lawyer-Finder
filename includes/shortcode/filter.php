<?php

function ld_lawyer_directory_shortcode()
{
    // Get the user's IP address
    $user_ip = $_SERVER['REMOTE_ADDR'];

    // Initialize the state variable
    $user_state = '';

    if ($user_ip) {
        $response = wp_remote_get("http://ipinfo.io/{$user_ip}/json");

        if (is_array($response) && !is_wp_error($response)) {
            $data = json_decode($response['body']);
            if (isset($data->region)) {
                $user_state = $data->region;
            }
        }
    }

    ob_start();
?>
    <form method="get" action="<?php echo site_url('/lawyers-founder'); ?>" class="p-4 border rounded shadow-sm">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="ld_state_select" class="form-label">State:</label>
                <select name="ld_state" id="ld_state_select" class="form-select">
                    <option value="">Select a state</option>
                    <?php
                    $lawyer_states = get_posts([
                        'post_type'      => 'lawyer',
                        'post_status'    => 'publish',
                        'numberposts'    => -1,
                        'fields'         => 'ids',
                    ]);

                    $states = [];

                    if ($lawyer_states) {
                        foreach ($lawyer_states as $post_id) {
                            $state = get_post_meta($post_id, '_ld_state', true);
                            if ($state && !in_array($state, $states)) {
                                $states[] = $state;
                            }
                        }
                        sort($states);
                    }

                    foreach ($states as $state) {
                        // Check if this is the user's state and pre-select it
                        $selected = ($user_state === $state) ? 'selected' : '';
                        echo "<option value=\"$state\" $selected>$state</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <div class="ld_specialization">
                    <label for="ld_specialization" class="form-label">Practice Area:</label>
                    <input type="text" name="ld_specialization" id="ld_specialization" class="form-control" placeholder="Type practice area" required>
                    <div class="suggest-result"></div>
                </div>
            </div>

            <div class="col-md-4 mb-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
        </div>
    </form>

<?php
    return ob_get_clean();
}
add_shortcode('lawyer_directory', 'ld_lawyer_directory_shortcode');

?>