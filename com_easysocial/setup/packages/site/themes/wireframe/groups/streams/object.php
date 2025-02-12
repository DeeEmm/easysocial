<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-profile-header es-profile-header--mini">
	<div class="es-profile-header__hd with-cover">
		<div class="es-profile-header__cover es-flyout no-cover" style="background-image: url(<?php echo $group->getCover();?>); background-position: <?php echo $group->getCoverPosition();?>;">
			<div class="es-cover-container">

			</div>
		</div>
		<div class="es-profile-header__avatar-wrap es-flyout" data-profile-avatar>
			<?php echo $this->html('avatar.mini', $group->getName(), $group->getPermalink(), $group->getAvatar(), 'lg'); ?>
		</div>
	</div>

	<div class="es-profile-header__bd">
		<div class="t-lg-pull-left">
			<h2 class="es-profile-header__title">
				<a href="<?php echo $group->getPermalink();?>"><?php echo $group->getName();?></a>
			</h2>

			<ul class="g-list-inline g-list-inline--dashed es-profile-header__meta t-lg-mt--md">
				<li class="">
					<a href="<?php echo $group->getCategory()->getPermalink();?>">
						<i class="fa fa-folder"></i>&nbsp; <?php echo $group->getCategory()->getTitle(); ?>
					</a>
				</li>

				<li class="">
					<?php echo $this->html('group.type', $group); ?>
				</li>
			</ul>

			<div class="es-teaser-about t-lg-mt--md">
				<div class="">
					<?php echo $this->html('string.truncate', $group->getDescription(), 300); ?>
				</div>
			</div>
		</div>
	</div>

	<div class="es-profile-header__ft">
		<nav class="o-nav es-nav-pills">
			<span class="o-nav__item">
				<a href="<?php echo ESR::albums( array( 'uid' => $group->id , 'type' => SOCIAL_TYPE_GROUP ) );?>" class="o-nav__link">
					<i class="far fa-images"></i>&nbsp;
					<?php if ($this->isMobile()) { ?>
						<?php echo $group->getTotalAlbums(); ?>
					<?php } else { ?>
						<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_GROUPS_ALBUMS', $group->getTotalAlbums()), $group->getTotalAlbums()); ?>
					<?php } ?>
				</a>
			</span>
			<span class="o-nav__item">
				<a href="<?php echo $group->getAppPermalink('members');?>" class="o-nav__link">
					<i class="fa fa-users"></i>&nbsp;
					<?php if ($this->isMobile()) { ?>
						<?php echo $group->getTotalMembers(); ?>
					<?php } else { ?>
						<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_GROUPS_MEMBERS', $group->getTotalMembers()), $group->getTotalMembers()); ?>
					<?php } ?>
				</a>
			</span>

			<?php if ($this->config->get('groups.layout.hits')) { ?>
			<span class="o-nav__item">
				<span class="o-nav__link">
					<i class="fa fa-eye"></i>&nbsp;
					<?php if ($this->isMobile()) { ?>
						<?php echo $group->hits; ?>
					<?php } else { ?>
						<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_GROUPS_VIEWS', $group->hits), $group->hits); ?>
					<?php } ?>
				</span>
			</span>
			<?php } ?>

			<div role="toolbar" class="btn-toolbar t-pull-right">
				<div class="o-btn-group">
					<?php echo $this->html('group.action', $group); ?>
				</div>
			</div>

		</nav>


	</div>
</div>
