
EasySocial
.require()
.script('apps/fields/user/terms/content')
.done(function($) {

	$('[data-field-<?php echo $field->id; ?>]').addController('EasySocial.Controller.Field.Terms', {
		required: <?php echo $field->required ? 1 : 0; ?>,
		event: '<?php echo $event; ?>'
    });

    $('[data-field-terms-dialog]').on('click', function(){
        EasySocial.dialog({
            content: EasySocial.ajax('fields/user/terms/getTerms', {"id": "<?php echo $field->id;?>"})
        });
    });
});
