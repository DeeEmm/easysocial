EasySocial.require()
.script("site/story/links")
.done(function($) {
	var plugin = story.addPlugin("links", {
					validateUrl: <?php echo $this->config->get('links.parser.validate') ? 'true' : 'false';?>,
					"urlParser": /(((http|https):\/{2})+(([0-9a-z_-]+\.)+(<?php echo ES::links()->getSupportedTld();?>)(:[0-9]+)?((\/([~0-9a-zA-Z\u00C0-\u00FF\u0100-\u017F\u0180-\u024F\u0400-\u04FF,:;\(\)\[\]#!=+%@&|:;.\/_-]+))?)?((\/\?|\?)[0-9a-zA-Z\u00C0-\u00FF\u0100-\u017F\u0180-\u024F\u0400-\u04FF,:;\(\)\[\]#!=+%@&|:;.\/_-]+)?))/giu,
					"link": {
						<?php if ($link->url) { ?>
						"title": "<?php echo ES::string()->escape($link->title);?>",
						"description": "<?php echo ES::string()->escape($link->description);?>",
						"url": "<?php echo ES::string()->escape($link->url);?>",
						"image": "<?php echo isset($link->images[0]) ? $link->images[0] : ''; ?>"
						<?php } ?>
					},
					"isEdit": <?php echo $isEdit ? 'true' : 'false'; ?>,
					"errors": {
						"messages": {
							"insert": "<?php echo JText::_('COM_ES_LINKS_STORY_PLEASE_INSERT_LINK', true);?>",
							"tlds": "<?php echo JText::_('COM_ES_LINKS_STORY_NOT_SUPPORTED_TLDS', true);?>"
						}
					}
				});
});
