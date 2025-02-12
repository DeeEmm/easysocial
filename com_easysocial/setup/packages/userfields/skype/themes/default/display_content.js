
<?php if ($type) { ?>
EasySocial.require()
.script('https://secure.skypeassets.com/i/scom/js/skype-uri.js')
.done(function($) {

	// Check the element exist on that page or not
	var skypeDivLen = $('#SkypeButton-<?php echo $user->id;?>').length;

	if (skypeDivLen > 0) {
	    Skype.ui({
	        "name": "<?php echo $type;?>",
	        "element": "SkypeButton-<?php echo $user->id;?>",
	        "participants": ["<?php echo $value;?>"],
	        "imageSize": 16,
	        <?php if ($params->get('theme', 'blue') != 'blue') { ?>
	        "imageColor": "<?php echo $params->get('theme');?>"
	        <?php } ?>
	    });
	}    
});
<?php } ?>