<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
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
		<div class="es-profile-header__cover es-flyout no-cover" style="background-image: url(<?php echo $cluster->getCover();?>); background-position: <?php echo $cluster->getCoverPosition();?>;">
			<div class="es-cover-container">

			</div>
		</div>
		<div class="es-profile-header__avatar-wrap es-flyout" data-profile-avatar>
			<?php echo $this->html('avatar.mini', $cluster->getName(), $cluster->getPermalink(), $cluster->getAvatar(), 'lg'); ?>
		</div>
	</div>

	<div class="es-profile-header__bd">
		<div class="t-lg-pull-left">
			<h2 class="es-profile-header__title">
				<a href="<?php echo $cluster->getPermalink();?>"><?php echo $cluster->getName();?></a>
			</h2>

			<ul class="g-list-inline g-list-inline--dashed es-profile-header__meta t-lg-mt--md">
				<li class="">
					<a href="<?php echo $cluster->getCategory()->getFilterPermalink();?>">
						<i class="fa fa-folder"></i>&nbsp; <?php echo $cluster->getCategory()->getTitle(); ?>
					</a>
				</li>

				<li class="">
					<?php echo $this->html($type . '.type', $cluster); ?>
				</li>
			</ul>

			<div class="es-teaser-about t-lg-mt--md">
				<div class="">
					<?php echo $this->html('string.truncate', $cluster->getDescription(), 300, '', true); ?>
				</div>
			</div>

			<div class="es-teaser-date">
				<i class="fa fa-calendar"></i>
				<?php echo $cluster->getStartEndDisplay(); ?>
			</div>
		</div>
	</div>

	<div class="es-profile-header__ft">
		<nav class="o-nav es-nav-pills">
			<span class="o-nav__item">
				<a href="<?php echo ESR::albums( array( 'uid' => $cluster->id , 'type' => $type));?>" class="o-nav__link">
					<i class="far fa-images"></i>&nbsp;
					<?php if ($this->isMobile()) { ?>
						<?php echo $cluster->getTotalAlbums(); ?>
					<?php } else { ?>
						<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_' . strtoupper($type) . 'S_TOTAL_ALBUMS', $cluster->getTotalAlbums()), $cluster->getTotalAlbums()); ?>
					<?php } ?>
				</a>
			</span>
			<span class="o-nav__item">
				<a href="<?php echo $cluster->getAppPermalink('guests');?>" class="o-nav__link">
					<i class="fa fa-users"></i>&nbsp;
					<?php if ($this->isMobile()) { ?>
						<?php echo $cluster->getTotalMembers(); ?>
					<?php } else { ?>
						<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_' . strtoupper($type) . 'S_TOTAL_MEMBERS', $cluster->getTotalMembers()), $cluster->getTotalMembers()); ?>
					<?php } ?>
				</a>
			</span>

			<?php if ($this->config->get($type . 's.layout.hits')) { ?>
			<span class="o-nav__item">
				<span class="o-nav__link">
					<i class="fa fa-eye"></i>&nbsp;
					<?php if ($this->isMobile()) { ?>
						<?php echo $cluster->hits; ?>
					<?php } else { ?>
						<?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_' . strtoupper($type) . 'S_TOTAL_VIEWS', $cluster->hits), $cluster->hits); ?>
					<?php } ?>
				</span>
			</span>
			<?php } ?>

			<div role="toolbar" class="btn-toolbar t-pull-right">
				<div class="o-btn-group">
					<?php echo $this->html($type . '.action', $cluster); ?>
				</div>
			</div>
		</nav>
	</div>
</div>
