EasySocial.ready(function($) {

    $('[data-article-insert]').on('click', function(event) {
        
        event.preventDefault();

        // Supply all the necessary info to the caller
        var element = $(this);
        var data = {
                    "id": element.data('id'),
                    "title" : element.data('title'),
                    "alias" : element.data('alias')
                };

        window.parent["<?php echo $jscallback;?>" ](data);
    });

});