jQuery(document).ready(function($) {
    $('#ld_state_select').select2({
        width: '100%'
    });
    $("#ld_specialization2").select2({
        width: "100%",
        placeholder: "Select Specialization",
        allowClear: true
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    form.addEventListener('submit', function (e) {
        const specializationInput = document.getElementById('ld_specialization');
        if (!specializationInput.value.trim()) {
            e.preventDefault();
            alert('Please enter a practice area before searching.');
        }
    });
});