jQuery(document).ready(function($) {
    $('#rnompa-commerceyar-send').on('click', function() {
        let token = $('#rnompa-commerceyar-token').val().trim();
        $('#rnompa-message').text('');
        if (!token) {
            $('#rnompa-message').text('لطفا توکن کامرس یار را وارد کنید.');
            return;
        }
        $(this).prop('disabled', true).text('در حال ارسال...');
        $.ajax({
            type: 'POST',
            url: rnompa_settings.ajax_url,
            data: {
                action: 'rnompa_save_commerceyar_token',
                security: rnompa_settings.ajax_nonce,
                token: token
            },
            dataType: 'json',
            success: function(response) {
                $('#rnompa-commerceyar-send').prop('disabled', false).text('ارسال توکن به سرور');
                if (response.success) {
                    if (response.data.token) {
                        $('#rnompa-server-token').val(response.data.token);
                        $('#rnompa-token-result').show();
                    }
                    $('#rnompa-message').css('color', '#2ecc71').text(response.data.message);
                } else {
                    $('#rnompa-message').css('color', '#e74c3c').text(response.data.message);
                }
            },
            error: function() {
                $('#rnompa-commerceyar-send').prop('disabled', false).text('ارسال توکن به سرور');
                $('#rnompa-message').css('color', '#e74c3c').text('خطا در برقراری ارتباط با سرور!');
            }
        });
    });

    $('#rnompa-copy-token').on('click', function() {
        let $input = $('#rnompa-server-token');
        $input.select();
        $input[0].setSelectionRange(0, 99999);
        document.execCommand('copy');
        $(this).text('کپی شد!');
        setTimeout(() => $(this).text('کپی'), 1200);
    });
});