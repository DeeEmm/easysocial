EasySocial.ready(function($)
{
    var tmp = '';

    $('[data-field-joomla-twofactor] [data-es-toggle="radio-buttons"] > input[type="hidden"]').on('change', function() {
        var enabled = $(this).val() == 1;

        if (enabled) {
            $('[data-auth-selection]').removeClass('hide');

            $('[data-auth-selector]').val(tmp);

            if (tmp != '') {
                $('[data-auth-methods]').removeClass('hide');
                $('[data-auth-method="' + tmp + '"]').removeClass('hide');
            }

            return true;
        }

        // Hide the selection
        $('[data-auth-selection]').addClass('hide');

        // Hide the form and reset the selector value as well
        tmp = $('[data-auth-selector]').val();

        $('[data-auth-selector]').val('');
        $('[data-auth-methods]').addClass('hide');
    });

    $('[data-auth-selector]').on('change', function(){

        var type = $(this).val();

        $('[data-auth-method]').addClass('hide');

        if (type != '') {
            $('[data-auth-methods]').removeClass('hide');
            $('[data-auth-method="' + type + '"]').removeClass('hide');
        }

    });
});
