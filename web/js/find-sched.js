$(document).ready(function() {
    $('#find_sched_date').datepicker({
        format: 'MM d, yyyy',
        minDate: 'todo',
        autoclose: true
    });

    $('#find_sched_origin').select2({
        theme: 'bootstrap'    
    });

    $('#find_sched_destination').select2({
        theme: 'bootstrap'    
    });
});
