jQuery(document).ready(function($) {
    
    $('#sms-form-select').change(function() {
        console.log('hello');
        var content = $('#sms-form-select').find(":selected").val();
        $('#show_mobilesms').attr('data-type', 'sms:?body=" href=sms:?body="'+content+'"');
        $('#show_mobile_iossms').attr('data-type', 'sms:?body=" href=sms:?body="'+content+'"');
     });

});