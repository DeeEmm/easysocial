EasySocial.require()
<?php if ($files) { ?>
.script("site/explorer/explorer", "site/explorer/popup")
<?php } ?>
.library("markitup", "expanding")
.done(function($){

	window.insertVideoCode = function(videoURL , caretPosition , elementId )
	{
		if (videoURL.length == 0) {
			return false;
		}

		var textarea = $('textarea[name=' + elementId + ']');
		var tag = '[video]' + videoURL + '[/video]';

		// If this is at the first position, we don't want to do anything here.
		if (caretPosition == 0) {
			$(textarea).val(tag);
			
			EasySocial.dialog().close();
			
			return true;
		}

		var contents = $(textarea).val();

		$(textarea).val(contents.substring(0, caretPosition) + tag + contents.substring(caretPosition, contents.length));

		EasySocial.dialog().close();
	};

	var settings = {
		onTab: {
			keepDefault: false,
			replaceWith: '    '
		},

		previewParserVar: 'data',

		markupSet: [{
				name: "<?php echo JText::_('COM_EASYSOCIAL_BBCODE_BOLD', true); ?>",
				key: 'B',
				openWith: '[b]',
				closeWith: '[/b]',
				className: 'markitup-bold'
			}, {
				name: "<?php echo JText::_('COM_EASYSOCIAL_BBCODE_ITALIC', true); ?>",
				key: 'I',
				openWith: '[i]',
				closeWith: '[/i]',
				className: 'markitup-italic'
			}, {
				name: "<?php echo JText::_('COM_EASYSOCIAL_BBCODE_UNDERLINE', true); ?>",
				key: 'U',
				openWith: '[u]',
				closeWith: '[/u]',
				className: 'markitup-underline'
			}, {
				name: "<?php echo JText::_('COM_EASYSOCIAL_BBCODE_CODE', true); ?>",
				openWith: '[code type="markup"]',
				closeWith: '[/code]',
				className: 'markitup-code'
			}
			
			<?php if ($files) { ?>
			,{
				className: 'markitup-files',
				name: "<?php echo JText::_('COM_EASYSOCIAL_BBCODE_INSERT_FILE', true);?>",
				replaceWith: function(h) {
					// Get the current position of the caret
					var caretPosition = h.caretPosition.toString();

					EasySocial.explorer
						.open({
							<?php if (isset($controllerName)) { ?>
							controllerName: '<?php echo $controllerName; ?>',
							<?php } ?>
							type: "<?php echo $type;?>",
							uid: <?php echo $uid; ?>,
							url: "site/controllers/explorer/hook"
						})
						.done(function(explorer, popup){

							explorer.element
								.off("fileUse.discussion")
								.on("fileUse.discussion", function(event, ids, files, data){

									if (!data) {
										return false;
									}

									// Get the current textarea
									var textarea = $('[data-<?php echo $uniqueId;?>]');
									var text = '';

									$.each(data, function(){
										text = text + '[file id="' + this.id + '"]' + this.name + '[/file]\n';
									});

									// If the caret is at the first position just update the value.
									if (caretPosition==0) {
										textarea.val(text);
										popup.hide();
										
										return;
									}

									var contents = textarea.val();

									textarea.val(contents.substring(0, caretPosition) + text + contents.substring(caretPosition, contents.length) );

									popup.hide();
								});
							});
				},
				beforeInsert: function(h ) {
				},
				afterInsert: function(h ) {
				},
			}
			<?php } ?>
			,{
				name: "<?php echo JText::_('COM_EASYSOCIAL_BBCODE_VIDEO', true); ?>",
				replaceWith: function(h) {

					EasySocial.dialog({
						"content": EasySocial.ajax('site/views/discussions/showVideoDialog', { "editorName" : '<?php echo $nameAttribute; ?>' , "caretPosition" : h.caretPosition.toString()})
					});

				},
				beforeInsert: function(h ) {
				},
				afterInsert: function(h ) {
				},
				className: 'markitup-video'
			},
		]
	};

	$('[data-<?php echo $uniqueId;?>]').markItUp(settings).expandingTextarea();
});
