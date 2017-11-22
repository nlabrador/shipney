$(document).ready(function() {
    $('.input-date').datepicker({
        format: 'MM d, yyyy',
        startDate: 'today',
        autoclose: true
    });

    $('.select-origin').select2({
        theme: 'bootstrap'    
    });

    $('.select-destination').select2({
        theme: 'bootstrap'    
    });

    $('#scrolldate').on('click', function() {
        $('.input-date').focus();
    });

    $('#scrolldest').on('click', function() {
        $('.select-destination').focus();
        $('.select-destination').select2('open');
    });

    $('#scrolldepart').on('click', function() {
        $('.select-origin').focus();
        $('.select-origin').select2('open');
    });

    if ($('#schedules').attr('class')) {
    location.href = "#";
    location.href = "#schedules";
    }
});
