jQuery(document).ready(function($) {
    $('#pix-code').on('click', function(e) {
        e.preventDefault();
        navigator.clipboard.writeText($(this).find('.pix').html());
        $('.pix-copied').html( 'Código copiado!' );
    });
    $('.copy-pix').on('click', function(e) {
        e.preventDefault();
        navigator.clipboard.writeText($('#pix-code .pix').html());
        $('.pix-copied').html( 'Código copiado!' );
    });
});