jQuery(document).ready(function ($) {
    $('input[name="ld_specialization"]').on('input', function () {
        var inputVal = $(this).val().toLowerCase();

        if (inputVal.length > 0) {
            $.get(ld_ajax_object.ajax_url, {
                action: 'ld_get_specializations',
                term: inputVal
            }, function (data) {
                var suggestions = JSON.parse(data);
                var suggestionsHtml = '';
                
                suggestions.forEach(function(suggestion) {
                    suggestionsHtml += '<div class="suggest-item">' + suggestion + '</div>';
                });

                $('.suggest-result').html(suggestionsHtml).show();
            });
        } else {
            $('.suggest-result').empty().hide();
        }
    });

    $(document).click(function (e) {
        if (!$(e.target).closest('.suggest-result, #ld_specialization').length) {
            $('.suggest-result').empty().hide();
        }
    });

    $(document).on('click', '.suggest-item', function () {
        var selectedVal = $(this).text();
        $('input[name="ld_specialization"]').val(selectedVal);
        $('.suggest-result').empty().hide();
    });
});
