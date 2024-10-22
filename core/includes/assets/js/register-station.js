jQuery(document).ready(function($) {
    $('#register-station-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            url: registerStationSettings.ajax_url,
            type: 'POST',
            data: formData + '&action=register_station',
            success: function(response) {
                if (response.success) {
                    $('#registration-result').html('<p style="color: green;">' + response.data.message + '</p>');
                    $('#register-station-form')[0].reset();
                } else {
                    $('#registration-result').html('<p style="color: red;">' + response.data.message + '</p>');
                }
            },
            error: function(xhr, status, error) {
                $('#registration-result').html('<p style="color: red;">An error occurred: ' + error + '</p>');
            }
        });
    });
});