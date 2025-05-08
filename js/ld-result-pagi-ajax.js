jQuery(document).ready(function ($) {
    $(document).on('click', '.result-pagination a', function (e) {
        e.preventDefault();

        let pageUrl = $(this).attr('href');
        let paged = pageUrl.split('paged=')[1];

        $.ajax({
            url: ld_resultlawyer_vars.ajaxurl,
            type: 'POST',
            data: {
                action: 'filter_lawyers',
                paged: paged,
                ld_state: $('#ld_state').val(),
                ld_specialization: $('#ld_specialization2').val(),
                nonce: ld_resultlawyer_vars.nonce
            },
            beforeSend: function () {
                $('#lawyer-items').html('<div class="text-center my-5">Loading...</div>');
            },
            success: function (response) {
                $('#lawyer-items').html(response);
                $('html, body').animate({
                    scrollTop: $("#lawyer-items").offset().top - 100
                }, 500);
            }
        });
    });
});
