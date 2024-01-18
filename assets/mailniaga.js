jQuery(document).ready(function($) {
    // Stock management
    $('input#woocommerce_enable_sendy, input#woocommerce_enable_acelle')
        .on('change', function() {
            var checkboxId = $(this).attr('id');
            var disableClass = checkboxId === 'woocommerce_enable_sendy' ? '.disable_mailniaga_sendy' : '.disable_mailniaga_acelle';

            if ($(this).is(':checked')) {
                $(this)
                    .closest('tbody')
                    .find(disableClass)
                    .closest('tr')
                    .show();
            } else {
                $(this)
                    .closest('tbody')
                    .find(disableClass)
                    .closest('tr')
                    .hide();
            }
        })
        .trigger('change');
});
