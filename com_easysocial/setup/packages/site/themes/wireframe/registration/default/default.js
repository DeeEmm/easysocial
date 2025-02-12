<?php if ($useDropdownList) { ?>
EasySocial.require()
.library('select2')
.done(function($) {
	$('[data-profile-select]').select2({
		placeholder: '<?php echo JText::_('COM_ES_SELECT_PROFILE_TYPE'); ?>',
		width: '100%',
		theme: 'default select2-container--profile-type',
		templateResult: formatOption,
	});

	template = $('[data-option-template]').detach().html();

	function formatOption (option) {
		
		var title = option.text
			desc = $(option.element).data('profile-desc'),
			image = $(option.element).data('profile-image'),
			imageTitle = $(option.element).data('profile-image-title'),
			url = $(option.element).data('profile-url'),
			optTemplate = $(template);

		optTemplate.find('[data-title]').html(title);
		optTemplate.find('[data-desc]').html(desc);
		optTemplate.find('[data-option-link]').attr('href', url);

		<?php if ($this->config->get('registrations.layout.avatar')) { ?>
		optTemplate.find('[data-img]').attr('src', image);
		optTemplate.find('[data-img]').attr('title', imageTitle);
		<?php } ?>

		var $option = $(optTemplate.html());

		return $option;
	};

	$('[data-profile-select]').on('select2:select', function() {
		var select = $(this),
			url = select.val();

		select.attr('disabled', 'disabled');

		if (url) {
			window.location.href = url;
		}
	});
});
<?php } ?>