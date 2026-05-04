/* global jQuery, document, window */
jQuery(document).ready(function($) {
    var $container = $('#aquarium-size');
    window.console.log($container);
    $('input', $container).on( 'keyup keypress blur change', function() {
        var $capacity = $('input[name="_iw_aquarium-size_capacity"]', $container);
        if ( !$capacity.val()  ) {
            $capacity.data('autocalc', true);
        }
        if ( $capacity.data('autocalc') ) {
            $capacity.val(
                parseInt(
                    parseInt($('input[name="_iw_aquarium-size_length"]', $container).val()) *
                    parseInt($('input[name="_iw_aquarium-size_width"]', $container).val()) *
                    parseInt($('input[name="_iw_aquarium-size_height"]', $container).val()) /
                    1000
                )
            );
        }
    });
});