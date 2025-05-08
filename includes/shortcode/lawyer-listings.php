<?php

function ld_lawyer_listing_shortcode()
{
    ob_start();

    // Get filter values from the URL
    $selected_state = isset($_GET['ld_state']) ? sanitize_text_field($_GET['ld_state']) : '';
    $selected_specialization = isset($_GET['ld_specialization']) ? sanitize_text_field($_GET['ld_specialization']) : '';

    // Prepare meta query
    $meta_query = ['relation' => 'OR'];

    if ($selected_state) {
        $meta_query[] = [
            'key' => '_ld_state',
            'value' => $selected_state,
            'compare' => '='
        ];
    }

    if ($selected_specialization) {
        $meta_query[] = [
            'key' => '_ld_specializations',
            'value' => $selected_specialization,
            'compare' => 'LIKE'
        ];
    }

    // Pagination
    $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;

    // Query lawyers
    $args = [
        'post_type' => 'lawyer',
        'posts_per_page' => 6, // Adjust per page as needed
        'paged' => $paged,
        'meta_query' => $meta_query
    ];

    $lawyer_query = new WP_Query($args);

    // Collect unique states and specializations for filter dropdowns
    $all_lawyers = new WP_Query(['post_type' => 'lawyer', 'posts_per_page' => -1]);
    $states = [];
    $specializations = [];

    if ($all_lawyers->have_posts()) {
        while ($all_lawyers->have_posts()) {
            $all_lawyers->the_post();
            $state = get_post_meta(get_the_ID(), '_ld_state', true);
            $specs = get_post_meta(get_the_ID(), '_ld_specializations', true);

            if ($state && !in_array($state, $states)) $states[] = $state;

            if ($specs) {
                $spec_list = array_map('trim', explode(',', $specs));
                foreach ($spec_list as $spec) {
                    if ($spec && !in_array($spec, $specializations)) $specializations[] = $spec;
                }
            }
        }
    }
    wp_reset_postdata();

?>

    <div class="lawyer-listing">
        <h1>Our Lawyers</h1>

        <!-- Filter Form -->
        <form class="row g-3 align-items-end mb-4" method="get" id="lawyer_filter_form">
            <div class="col-md-4">
                <label for="ld_state" class="form-label">Select State</label>
                <select name="ld_state" id="ld_state" class="form-select">
                    <option value="">All States</option>
                    <?php foreach ($states as $state) : ?>
                        <option value="<?php echo esc_attr($state); ?>" <?php selected($selected_state, $state); ?>>
                            <?php echo esc_html($state); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label for="ld_specialization" class="form-label">Select Specialization</label>
                <select name="ld_specialization" id="ld_specialization2" class="form-select">
                    <option value="">All Specializations</option>
                    <?php foreach ($specializations as $spec) : ?>
                        <option value="<?php echo esc_attr($spec); ?>" <?php selected($selected_specialization, $spec); ?>>
                            <?php echo esc_html($spec); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

            </div>


            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>


        <!-- Lawyer Cards -->
        <div class="row lawyer-items" id="lawyer-items">
            <?php if ($lawyer_query->have_posts()) : ?>
                <?php while ($lawyer_query->have_posts()) : $lawyer_query->the_post(); ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <?php if (has_post_thumbnail()) : ?>
                                <img src="<?php the_post_thumbnail_url('medium'); ?>" class="card-img-top" alt="<?php the_title_attribute(); ?>">
                            <?php else : ?>
                                <img src="https://lawyersnearme.ai/wp-content/uploads/2025/05/placeholder-laywer.webp" class="card-img-top" alt="Placeholder Lawyer">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php the_title(); ?></h5>
                                <p class="card-text"><strong>State:</strong> <?php echo esc_html(get_post_meta(get_the_ID(), '_ld_state', true)); ?></p>
                                <p class="card-text"><strong>Specializations:</strong> <?php echo esc_html(get_post_meta(get_the_ID(), '_ld_specializations', true)); ?></p>
                                <a href="tel:+11234567890" class="btn btn-primary mt-3 w-100">📞 Call Available 24/7</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else : ?>
                <div class="col-12">
                    <div class="alert alert-warning text-center">No lawyers found matching your criteria.</div>
                </div>
            <?php endif; ?>


            <!-- Pagination -->
            <div class="result-pagination" id="pagination">
                <?php
                echo paginate_links([
                    'total' => $lawyer_query->max_num_pages,
                    'current' => $paged,
                ]);
                ?>
            </div>
        </div>


    </div>

    <?php
    wp_reset_postdata();
    return ob_get_clean();
}

add_shortcode('lawyer_list', 'ld_lawyer_listing_shortcode');


add_action('wp_ajax_filter_lawyers', 'handle_ajax_lawyer_filter');
add_action('wp_ajax_nopriv_filter_lawyers', 'handle_ajax_lawyer_filter');

function handle_ajax_lawyer_filter()
{
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $state = sanitize_text_field($_POST['ld_state'] ?? '');
    $specialization = sanitize_text_field($_POST['ld_specialization'] ?? '');

    $meta_query = ['relation' => 'AND'];

    if ($state) {
        $meta_query[] = [
            'key' => '_ld_state',
            'value' => $state,
            'compare' => '='
        ];
    }

    if ($specialization) {
        $meta_query[] = [
            'key' => '_ld_specializations',
            'value' => $specialization,
            'compare' => 'LIKE'
        ];
    }

    $args = [
        'post_type' => 'lawyer',
        'posts_per_page' => 6,
        'paged' => $paged,
        'meta_query' => $meta_query
    ];

    $lawyer_query = new WP_Query($args);

    ob_start();

    if ($lawyer_query->have_posts()) :
        echo '<div class="row lawyer-items">';
        while ($lawyer_query->have_posts()) : $lawyer_query->the_post(); ?>
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm">
                    <?php if (has_post_thumbnail()) : ?>
                        <img src="<?php the_post_thumbnail_url('medium'); ?>" class="card-img-top" alt="<?php the_title_attribute(); ?>">
                    <?php else : ?>
                        <img src="https://lawyersnearme.ai/wp-content/uploads/2025/05/placeholder-laywer.webp" class="card-img-top" alt="Placeholder Lawyer">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php the_title(); ?></h5>
                        <p class="card-text"><strong>State:</strong> <?php echo esc_html(get_post_meta(get_the_ID(), '_ld_state', true)); ?></p>
                        <p class="card-text"><strong>Specializations:</strong> <?php echo esc_html(get_post_meta(get_the_ID(), '_ld_specializations', true)); ?></p>
                        <a href="tel:+11234567890" class="btn btn-primary mt-3 w-100">📞 Call Available 24/7</a>
                    </div>
                </div>
            </div>
        <?php endwhile;
        echo '</div>';

        echo '<div class="pagination" id="pagination">';
        echo paginate_links([
            'total' => $lawyer_query->max_num_pages,
            'current' => $paged,
            'format' => '?paged=%#%',
            'type' => 'plain'
        ]);
        echo '</div>';
    else :
        echo '<div class="col-12"><div class="alert alert-warning text-center">No lawyers found matching your criteria.</div></div>';
    endif;

    wp_reset_postdata();
    echo ob_get_clean();
    wp_die();
}




// Ajax Handler for Lawyer Listing
function ld_lawyer_listing_ajax()
{
    check_ajax_referer('ld_lawyer_nonce', 'nonce');

    $selected_state = isset($_POST['ld_state']) ? sanitize_text_field($_POST['ld_state']) : '';
    $selected_specialization = isset($_POST['ld_specialization']) ? sanitize_text_field($_POST['ld_specialization']) : '';
    $paged = isset($_POST['paged']) ? absint($_POST['paged']) : 1;

    // Prepare meta query
    $meta_query = ['relation' => 'OR'];

    if ($selected_state) {
        $meta_query[] = [
            'key' => '_ld_state',
            'value' => $selected_state,
            'compare' => '='
        ];
    }

    if ($selected_specialization) {
        $meta_query[] = [
            'key' => '_ld_specializations',
            'value' => $selected_specialization,
            'compare' => 'LIKE'
        ];
    }

    // Query lawyers
    $args = [
        'post_type' => 'lawyer',
        'posts_per_page' => 6,
        'paged' => $paged,
        'meta_query' => $meta_query
    ];

    $lawyer_query = new WP_Query($args);

    if ($lawyer_query->have_posts()) :

        ?>
        <div id="lawyer-preloader-overlay">
            <div class="spinner"></div>
        </div>

        <div class="row lawyer-items">
            <?php while ($lawyer_query->have_posts()) : $lawyer_query->the_post(); ?>
                <div class="col-md-4 mb-4">
                    <div class="card  shadow-sm">
                        <?php if (has_post_thumbnail()) : ?>
                            <img src="<?php the_post_thumbnail_url('medium'); ?>" class="card-img-top" alt="<?php the_title_attribute(); ?>">
                        <?php else : ?>
                            <img src="https://lawyersnearme.ai/wp-content/uploads/2025/05/placeholder-laywer.webp" class="card-img-top" alt="Placeholder Lawyer">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php the_title(); ?></h5>
                            <p class="card-text"><strong>State:</strong> <?php echo esc_html(get_post_meta(get_the_ID(), '_ld_state', true)); ?></p>
                            <p class="card-text"><strong>Specializations:</strong> <?php echo esc_html(get_post_meta(get_the_ID(), '_ld_specializations', true)); ?></p>
                            <a href="tel:+11234567890" class="btn btn-primary mt-3 w-100">📞 Call Available 24/7</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <div class="pagination" id="pagination">
            <?php
            echo paginate_links([
                'total' => $lawyer_query->max_num_pages,
                'current' => $paged,
                'format' => '?paged=%#%',  // Ensure pagination links have the correct structure
                'type' => 'plain',
                'prev_text' => __('« Prev'),
                'next_text' => __('Next »'),
            ]);
            ?>
        </div>

    <?php else : ?>
        <p>No lawyers found matching your criteria.</p>
<?php endif;

    wp_die();
}

add_action('wp_ajax_ld_lawyer_filter', 'ld_lawyer_listing_ajax');
add_action('wp_ajax_nopriv_ld_lawyer_filter', 'ld_lawyer_listing_ajax');
?>