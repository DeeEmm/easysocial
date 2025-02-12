
EasySocial
	.require()
	.script('apps/fields/user/joomla_password/registermini_content')
	.done(function($) {
		$('[data-field-<?php echo $field->id; ?>]').addController('EasySocial.Controller.Field.Joomla_password.Mini', {
			required: <?php echo $field->required ? 1 : 0; ?>,
			reconfirmPassword: <?php echo $params->get( 'mini_reconfirm_password' ) ? 'true' : 'false'; ?>,
			min: <?php echo $params->get( 'min', 4 ); ?>,
			minInteger: <?php echo $params->get( 'min_integer', 0 ); ?>,
			minSymbol: <?php echo $params->get( 'min_symbols', 0 ); ?>,
			minUpperCase: <?php echo $params->get( 'min_uppercase', 0 ); ?>
		});
	});
