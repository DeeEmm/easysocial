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
<form method="post" action="<?php echo JRoute::_('index.php');?>">
	<div id="es">
		<div class="es-sharer">
			<div class="es-sharer__title">
				<img src="<?php echo ES::getLogo();?>" alt="" width="120" />
			</div>

			<div class="es-sharer__editor">
				<textarea name="content" class="o-form-control" placeholder="<?php echo JText::_('COM_ES_SHARER_SHARE_SOMETHING_ABOUT_THIS');?>"></textarea>
			</div>


			<div class="es-sharer__stream">
				<?php if (!$meta) { ?>
				<div class="es-repost-content">
					<div class="es-stream-embed is-link">
						<div class="es-stream-embed__cover">
							<div class="es-stream-embed__cover-img" style="background-image: url('');"></div>
						</div>

						<div class="es-stream-embed__title es-stream-embed--border">
							<?php echo JText::_('Unable to obtain information'); ?>
						</div>

						<div class="es-stream-embed__meta t-text--muted t-fs--sm"><?php echo $url;?></div>

						<div class="es-stream-embed__desc">
							<?php echo JText::_('Unable to obtain information'); ?>
						</div>
					</div>
				</div>
				<?php } ?>

				<?php if ($meta) { ?>
				<div class="es-repost-content">
					<div class="es-stream-embed is-link">
						<div class="es-stream-embed__cover">
							<div class="es-stream-embed__cover-img" style="background-image: url('<?php echo $meta->image;?>');"></div>
						</div>

						<div class="es-stream-embed__title es-stream-embed--border">
							<?php echo $meta->title;?>
						</div>

						<div class="es-stream-embed__meta t-text--muted t-fs--sm"><?php echo $meta->url;?></div>

						<div class="es-stream-embed__desc">
							<?php echo $meta->desc;?>
						</div>
					</div>
				</div>

				<?php echo $this->html('form.hidden', 'links_title', $meta->title); ?>
				<?php echo $this->html('form.hidden', 'links_image', $meta->image); ?>
				<?php echo $this->html('form.hidden', 'links_description', $meta->desc); ?>
				<?php echo $this->html('form.hidden', 'links_url', $meta->url); ?>
				<?php echo $this->html('form.hidden', 'aff', $this->html('string.escape', $affiliationId)); ?>
				<?php } ?>
			</div>

			<div class="es-sharer__action">
				<div class="es-story-meta-buttons">
					<button class="btn btn-es-default-o" type="button" onclick="closeWindow();">
						<?php echo JText::_('COM_ES_CANCEL');?>
					</button>
				</div>

				<div class="es-story-actions">
					<button class="btn btn-es-primary es-story-submit" type="submit">
						<?php echo JText::_('COM_ES_SHARE_THIS');?>
					</button>
				</div>
			</div>
		</div>
	</div>

	<?php echo $this->html('form.action', 'sharer', 'save'); ?>
</form>

<script>
	window.resizeTo(480, 680);
</script>
