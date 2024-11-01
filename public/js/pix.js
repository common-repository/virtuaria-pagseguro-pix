jQuery(document).ready(function($) {
    $(document).on('click', '#pagseguro-pix-button', function(e) {
        e.preventDefault();
        $(this).prop('disabled', true);
        $(this).parent().find('.loading').removeClass('hidden');

        $.post(
            ajaxurl,
            {
                'action': 'do_new_payment_pix'
            },
            function(response) {
		    	$('#pagseguro-pix-payment-form').append( response );
                $('#pagseguro-pix-button').remove();
                $('#pagseguro-pix-payment-form .loading').addClass( 'hidden' );
		    }
        );
    });
});