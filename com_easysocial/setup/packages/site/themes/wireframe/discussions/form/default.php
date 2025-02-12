<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

$editor = isset($editor) ? $editor : 'bbcode';
?>
<div class="es-container">
	<div class="es-content">
		<form action="<?php echo JRoute::_('index.php');?>" method="post" class="es-forms">
			<div class="es-forms__group">

				<div class="es-forms__content">
					<div class="o-form-group">
						<input type="text" name="title" value="<?php echo $this->html('string.escape', $discussion->title);?>"
							placeholder="<?php echo JText::_('APP_GROUP_DISCUSSIONS_TITLE_PLACEHOLDER', true);?>" class="o-form-control"
						/>
					</div>

					<div class="o-form-group">
						<?php if ($editor == 'bbcode') { ?>
						<?php echo ES::bbcode()->editor('discuss_content', $discussion->content, array('controllerName' => $cluster->getTypePlural(), 'files' => $files, 'uid' => $cluster->id, 'type' => $cluster->getType())); ?>
						<?php } else { ?>
							<?php echo ES::editor()->getEditor($editor)->display('discuss_content', $discussion->content, '100%', '350', '10', '10', false, null, 'com_easysocial'); ?>
						<?php } ?>
					</div>
				</div>
			</div>

			<div class="es-forms__actions">
				<div class="o-form-actions">
					<a href="<?php echo $cluster->getAppPermalink('discussions'); ?>" class="pull-left btn btn-es-default-o"><?php echo JText::_('COM_ES_CANCEL'); ?></a>

					<button type="submit" class="pull-right btn btn-es-primary"><?php echo JText::_('COM_EASYSOCIAL_SUBMIT_BUTTON'); ?></button>
				</div>
			</div>

			<?php echo $this->html('form.action', 'discussions', 'save'); ?>
			<input type="hidden" name="appId" value="<?php echo $app->id;?>" />
			<input type="hidden" name="uid" value="<?php echo $cluster->id; ?>" />
			<input type="hidden" name="type" value="<?php echo $cluster->getType(); ?>" />
			<input type="hidden" name="id" value="<?php echo $discussion->id;?>" />
			<input type="hidden" name="content_type" value="<?php echo $editor;?>" />
		</form>
	</div>
</div>
