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
<div class="es-side-widget">
	<?php echo $this->html('widget.title', 'COM_EASYSOCIAL_PAGES_RANDOM_PAGES'); ?>

	<div class="es-side-widget__bd">
		<?php if ($pages) { ?>
		<div class="o-flag-list">
			<?php foreach ($pages as $page) { ?>
			<div class="o-flag">
				<div class="o-flag__image">
					<?php echo $this->html('avatar.mini', $page->getName(), $page->getPermalink(), $page->getAvatar(SOCIAL_AVATAR_SQUARE)); ?>
				</div>
				<div class="o-flag__body">
					<a href="<?php echo $page->getPermalink();?>" class="ed-user-name t-mb--sm"><?php echo $page->getName();?></a>
					<div class="t-text--muted">
						<i class="fa fa-users"></i> <?php echo $page->getTotalMembers(); ?><?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_PAGES_FOLLOWERS', $page->getTotalMembers()));?>
					</div>
				</div>
			</div>
			<?php } ?>
		</div>
		<?php } else { ?>
		<div class="t-text--muted">
			<?php echo JText::_('COM_EASYSOCIAL_PAGES_NO_PAGES_YET'); ?>
		</div>
		<?php } ?>
	</div>
</div>

<div class="es-side-widget">
	
	<?php echo $this->html('widget.title', 'COM_EASYSOCIAL_PAGES_RANDOM_FOLLOWERS'); ?>

	<div class="es-side-widget__bd">
		<?php if ($randomMembers) { ?>
			<?php echo $this->html('widget.users', $randomMembers); ?>
		<?php } else { ?>
			<div class="t-text--muted">
				<?php echo JText::_('COM_EASYSOCIAL_PAGES_NO_FOLLOWERS_HERE'); ?>
			</div>
		<?php } ?>
	</div>
</div>


<div class="es-side-widget">
	<?php echo $this->html('widget.title', 'COM_EASYSOCIAL_PAGES_RANDOM_ALBUMS'); ?>

	<div class="es-side-widget__bd">
		<?php echo $this->html('widget.albums', $randomAlbums, 'COM_EASYSOCIAL_PAGES_NO_ALBUMS_HERE'); ?>
	</div>
</div>
