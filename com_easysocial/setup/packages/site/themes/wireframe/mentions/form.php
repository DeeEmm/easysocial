<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-story-text">
	<div class="es-story-textbox mentions-textfield" data-story-textbox>
		<div class="mentions">
			<div data-mentions-overlay data-default="<?php echo $this->html('string.escape', $defaultOverlay); ?>"><?php echo $story->overlay; ?></div>
			<textarea class="es-story-textfield" name="content" autocomplete="off"
				data-story-textField
				data-mentions-textarea
				data-default="<?php echo $this->html('string.escape', $defaultContent); ?>"
				data-initial="0"
				placeholder="<?php echo JText::_( 'COM_EASYSOCIAL_STORY_PLACEHOLDER' ); ?>"><?php echo $story->content; ?></textarea>
		</div>
	</div>
</div>
<div class="es-stream-editor-actions clearfix">
	<a href="javascript:void(0);" class="btn btn-es-default btn-sm pull-left" data-edit-cancel><?php echo JText::_('COM_ES_CANCEL');?></a>
	<a href="javascript:void(0);" class="btn btn-es-primary btn-sm pull-right" data-edit-update>
		<?php echo JText::_('COM_ES_UPDATE');?>
	</a>
</div>
