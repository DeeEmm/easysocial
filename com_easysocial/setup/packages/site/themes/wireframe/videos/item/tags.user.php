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
<?php if ($usersTags) { ?>
	<?php foreach ($usersTags as $tag) { ?>
	<li data-tags-item data-id="<?php echo $tag->id;?>" class="t-lg-mb--md t-lg-mr--lg">
		<div class="o-avatar-v2 o-avatar-v2--rounded o-avatar-v2--md" data-user-id="<?php echo $tag->getEntity()->id; ?>">
			<div class="o-avatar-v2__content">
				<img src="<?php echo $tag->getEntity()->getAvatar(SOCIAL_AVATAR_MEDIUM); ?>"/>
			</div>
			<?php if ($video->canRemoveTag($tag)) { ?>
			<div class="o-avatar-v2__action">
				<div class="o-avatar-v2__remove-tag">
					<a href="javascript:void(0);" data-placement="top" data-es-provide="tooltip" data-original-title="<?php echo JText::_('COM_ES_REMOVE_TAG');?>" data-remove-tag>
						<i class="fa fa-times"></i>
					</a>
				</div>
			</div>
			<?php } ?>
		</div>
	</li>
	<?php } ?>
<?php } ?>
