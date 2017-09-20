$(document).ready(function() {
    $('#find_sched_date').datepicker({
        format: 'MM d, yyyy',
        startDate: 'today',
        autoclose: true
    });

    $('#find_sched_origin').select2({
        theme: 'bootstrap'    
    });

    $('#find_sched_destination').select2({
        theme: 'bootstrap'    
    });

    $('#scrolldate').on('click', function() {
        $('#find_sched_date').focus();
    });

    $('#scrolldest').on('click', function() {
        $('#find_sched_destination').focus();
        $('#find_sched_destination').select2('open');
    });

    $('#scrolldepart').on('click', function() {
        $('#find_sched_origin').focus();
        $('#find_sched_origin').select2('open');
    });

    if ($('#schedules').attr('class')) {
    location.href = "#";
    location.href = "#schedules";
    }
});
