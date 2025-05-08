jQuery(document).ready(function($) {

    function loadLawyers(page = 1) {
        var state = $('#ld_state').val();
        var specialization = $('#ld_specialization').val();

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
                $('#lawyer-preloader-overlay').fadeIn(100);
            },
            success: function(response) {
                $('#lawyer-items').html(response);
                $('#lawyer-preloader-overlay').fadeOut(200);
            }
        });
    }

    $('#lawyer_filter_form').on('submit', function(e) {
        e.preventDefault();
        loadLawyers(1);
    });

    $(document).on('click', '.pagination .page-numbers', function(e) {
        e.preventDefault();

        var page = $(this).text();

        if ($(this).hasClass('next') || $(this).hasClass('prev')) {
            var href = $(this).attr('href');
            var match = href.match(/paged=(\d+)/);
            if (match) {
                page = match[1];
            }
        }

        loadLawyers(page);
    });

});
