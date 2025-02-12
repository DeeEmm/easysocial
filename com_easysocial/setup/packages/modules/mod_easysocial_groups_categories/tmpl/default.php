<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div id="es" class="mod-es mod-es-groups-categories <?php echo $lib->getSuffix();?> <?php echo $lib->isMobile() ? 'is-mobile' : '';?>">
	<div class="o-flag-list">
		<?php foreach ($categories as $category) { ?>
		<div class="o-flag t-lg-mt--xl">

			<?php if ($params->get('display_avatar', true)) { ?>
			<div class="o-flag__image o-flag--top">
				<?php echo ES::template()->html('avatar.mini', $category->_('title'), $category->getPermalink(), $category->getAvatar(), 'md'); ?>
			</div>
			<?php } ?>

			<div class="o-flag__body">
				<a href="<?php echo $category->getPermalink();?>" class="category-title"><?php echo $category->_('title');?></a>

				<?php if ($params->get('display_counter', true)) { ?>
				<div class="t-text--muted t-fs--sm">
					<span class="hit-counter">
						<i class="fa fa-users"></i> <?php echo $category->getTotalCluster(SOCIAL_TYPE_GROUP);?>
					</span>
				</div>
				<?php } ?>
			</div>
		</div>
		<?php } ?>
	</div>
</div>
