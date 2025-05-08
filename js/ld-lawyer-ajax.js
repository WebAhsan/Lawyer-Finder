jQuery(document).ready(function($) {
    $('#lawyer_filter_form').on('submit', function(e) {
        e.preventDefault();

        var state = $('#ld_state').val();
        var specialization = $('#ld_specialization').val();
        var page = 1;

        // Get the current page from pagination links
        if ($('.pagination .page-numbers').length) {
            page = $('.pagination .page-numbers.current').text();
        }

        $.ajax({
            url: ldLawyerAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'ld_lawyer_filter',
                nonce: ldLawyerAjax.nonce,
                ld_state: state,
                ld_specialization: specialization,
                paged: page
            },
            beforeSend: function() {
                $('#lawyer-items').html('<p>Loading...</p>'); // Show loading message
            },
            success: function(response) {
                $('#lawyer-items').html(response);
            }
        });
    });
});
