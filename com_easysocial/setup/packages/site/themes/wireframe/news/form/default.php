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
?>
<div class="es-container">
	<div class="es-content">
		<form action="<?php echo JRoute::_('index.php');?>" method="post" class="es-forms">
			<div class="es-forms__group">
				<div class="es-forms__content">
					<div class="o-form-group">
						<input data-es-news-title type="text" name="title" value="<?php echo $this->html('string.escape', $news->title);?>" placeholder="<?php echo JText::_('APP_GROUP_NEWS_TITLE_PLACEHOLDER', true);?>" class="o-form-control news-title" />
					</div>

					<?php if ($params->get('allow_comments', true)) { ?>
					<div class="o-form-group">
						<div class="o-checkbox">
							<input id="es-comments" type="checkbox" value="1" name="comments"<?php echo $news->comments ? ' checked="checked"' : '';?>/>
							<label for="es-comments" class=""><?php echo JText::_( 'APP_GROUP_NEWS_ALLOW_COMMENTS' ); ?></label>
						</div>
					</div>
					<?php } ?>

					<div class="o-form-group">
						<div class="editor-wrap fd-cf">
							<?php if ($editor == 'bbcode') { ?>
							<?php echo ES::bbcode()->editor('news_content', $news->content, array('controllerName' => $cluster->getTypePlural(), 'files' => false, 'uid' => $cluster->id, 'type' => $cluster->getType())); ?>
							<?php } else { ?>
							<?php echo ES::editor()->getEditor($editor)->display('news_content', $news->content, '100%', '200', '10', '5', false, null, 'com_easysocial'); ?>
							<?php } ?>
						</div>
					</div>
				</div>

				<div class="es-forms__actions">
					<div class="o-form-actions">
						<a href="<?php echo !$news->id ? $cluster->getAppPermalink('news') : $news->getPermalink();?>" class="pull-left btn btn-es-default-o"><?php echo JText::_('COM_ES_CANCEL'); ?></a>
						<button type="submit" class="pull-right btn btn-es-primary" data-news-save-button>
							<?php echo JText::_('COM_EASYSOCIAL_SAVE_BUTTON'); ?>
						</button>
					</div>
				</div>
			</div>

			<?php echo $this->html('form.action', 'news', 'save'); ?>
			<input type="hidden" name="appId" value="<?php echo $app->id;?>" />
			<input type="hidden" name="uid" value="<?php echo $cluster->id;?>" />
			<input type="hidden" name="type" value="<?php echo $cluster->getType();?>" />
			<input type="hidden" name="id" value="<?php echo $news->id;?>" />
			<input type="hidden" name="content_type" value="<?php echo $editor;?>" />
		</form>
	</div>
</div>
