<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-photo-response">
	<?php echo $this->includeTemplate('site/photos/exif'); ?>
	<div class="es-actions" data-stream-actions>
		<div class="es-actions__item es-actions__item-action">
			<div class="es-actions-wrapper">
				<ul class="es-actions-list">
					<?php if ($likes->hasReactions()) { ?>
					<li>
						<?php echo $likes->button();?>
					</li>
					<?php } ?>

					<?php if ($this->config->get('repost.enabled')) { ?>
					<li>
						<?php echo $shares->button();?>
					</li>
					<?php } ?>

					<?php if ($lib->hasPrivacy()) { ?>
					<li>
						<?php echo $privacy->form($photo->id, SOCIAL_TYPE_PHOTO, $photo->uid, 'photos.view', false, null, array(), array('iconOnly' => true));?>
					</li>
					<?php } ?>

					<?php if ($this->config->get('repost.enabled')) { ?>
					<li class="">
						<?php echo $shares->counter(); ?>
					</li>
					<?php } ?>
				</ul>
			</div>
		</div>
		<div class="es-actions__item es-actions__item-stats">
			<?php echo $likes->html(); ?>
		</div>
		<div class="es-actions__item es-actions__item-comment">
			<?php echo $comments->getHTML(['hideEmpty' => false, 'deleteable' => $lib->isMine()]);?>
		</div>
	</div>
</div>
