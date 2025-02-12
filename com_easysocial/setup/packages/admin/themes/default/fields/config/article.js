EasySocial.require()
.script('admin/articles/suggest')
.done(function($) {
	$('[data-article-suggest]').addController(EasySocial.Controller.Articles.Suggest, {
		"max": 1,
		"name": 'config_<?php echo $name;?>'
	});
});