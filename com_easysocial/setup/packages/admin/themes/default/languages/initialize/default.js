EasySocial
.require()
.done(function($){

    EasySocial.ajax('admin/controllers/languages/update', {})
        .done(function() {
            window.location = '<?php echo rtrim(JURI::root(), '/');?>/administrator/index.php?option=com_easysocial&view=languages';
        })
        .fail(function(html, message) {
			$('[data-languages-wrapper]').addClass('has-error');
			$('[data-languages-error]').html(html);
            return;
        });
});
