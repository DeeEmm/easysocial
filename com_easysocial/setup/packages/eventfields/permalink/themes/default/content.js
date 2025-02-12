EasySocial
.require()
.script('apps/fields/event/permalink/content')
.done(function($) {


var element = $('[data-field-<?php echo $field->id; ?>]');

element.addController(EasySocial.Controller.Field.Event.Permalink, {
	required: "<?php echo $field->required ? 1 : 0; ?>",
	id: "<?php echo $field->id; ?>",
	clusterid: "<?php echo $clusterid; ?>"
});

$('[data-field-item="title"]').on('keyup', $.debounce(function(event) {

	var input = $(this).find('[data-field-textbox-input]');
	var value = input.val();


	element.trigger('onTitleKeyup', [value]);
}, 300));

});
