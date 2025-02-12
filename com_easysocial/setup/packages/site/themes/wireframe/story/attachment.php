<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<div class="es-story-content">
	<div class="es-story-attachment-items" data-story-panel-contents>
		<?php foreach ($story->panels as $panel) { ?>
			<div class="es-story-attachment-item <?php echo $panel->content->classname; ?>" data-story-attachment-item data-story-plugin-name="<?php echo $panel->name; ?>">
				<button type="button" class="close es-story-reset-button" data-story-reset="" style="top: -8px;"><i class="fa fa-times"></i></button>

				<div class="es-story-attachment-content" data-story-attachment-content data-story-plugin-name="<?php echo $panel->name; ?>">
					<?php echo $panel->content->html; ?>
				</div>
			</div>
		<?php } ?>
	</div>
</div>
