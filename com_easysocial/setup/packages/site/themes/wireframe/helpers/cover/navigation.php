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
<?php if ($this->isMobile()) { ?>
<div class="es-profile-header-nav-slider is-end-left" data-mobile-swiper-nav="<?php echo $uniqid; ?>">
	<div class="es-profile-header-nav swiper-container" data-mobile-swiper-container>
		<div class="swiper-wrapper">

			<div class="es-profile-header-nav__item swiper-slide<?php echo $active == 'timeline' ? ' is-active' : '';?>" data-es-swiper-item>
				<a href="<?php echo $timelinePermalink;?>" class="es-profile-header-nav__link">
					<span><?php echo JText::_('COM_ES_TIMELINE');?></span>
					<span class="es-profile-header-nav__link-bubble"></span>
				</a>
			</div>

			<div class="es-profile-header-nav__item swiper-slide<?php echo $active == 'info' || $active == 'about' ? ' is-active' : '';?>" data-es-swiper-item>
				<a href="<?php echo $aboutPermalink;?>" class="es-profile-header-nav__link">
					<span><?php echo JText::_('COM_ES_ABOUT');?></span>
					<span class="es-profile-header-nav__link-bubble"></span>
				</a>
			</div>

			<?php foreach ($appsHeader as $app) { ?>
				<?php if (!$app->isMore) { ?>
				<div class="es-profile-header-nav__item swiper-slide<?php echo $active == $app->active ? ' is-active' : '';?><?php echo $app->hasNotice ? ' has-notice' : '';?>" data-es-swiper-item>
					<a href="<?php echo $app->permalink;?>" class="es-profile-header-nav__link">
						<span><?php echo $app->title;?></span>
						<span class="es-profile-header-nav__link-bubble"></span>
					</a>
				</div>
				<?php } ?>
			<?php } ?>

			<?php if ($showBrowseApps) { ?>
				<div class="es-profile-header-nav__item swiper-slide" data-es-swiper-item>
					<a href="<?php echo ESR::apps();?>" class="es-profile-header-nav__link">
						<span>&nbsp;<?php echo JText::_('COM_EASYSOCIAL_BROWSE'); ?></span>
						<span class="es-profile-header-nav__link-bubble"></span>
					</a>
				</div>
			<?php } ?>

		</div>
	</div>
</div>
<?php } else { ?>

<div class="es-profile-header-nav">

	<div class="es-profile-header-nav__item <?php echo $active == 'timeline' ? 'is-active' : '';?>" data-es-nav-item>
		<a href="<?php echo $timelinePermalink;?>" class="es-profile-header-nav__link"><span><?php echo JText::_('COM_ES_TIMELINE');?></span></a>
	</div>

	<div class="es-profile-header-nav__item <?php echo $active == 'info' || $active == 'about' ? 'is-active' : '';?>" data-es-nav-item>
		<a href="<?php echo $aboutPermalink;?>" class="es-profile-header-nav__link"><span><?php echo JText::_('COM_ES_ABOUT');?></span></a>
	</div>

	<?php foreach ($appsHeader as $app) { ?>
		<?php if (!$app->isMore) { ?>
		<div class="es-profile-header-nav__item<?php echo $active == $app->active ? ' is-active' : '';?><?php echo $app->hasNotice ? ' has-notice' : '';?>" data-es-nav-item>
			<a href="<?php echo $app->permalink;?>" class="es-profile-header-nav__link">
				<span><?php echo $app->title;?></span>
				<span class="es-profile-header-nav__link-bubble"></span>
			</a>
		</div>
		<?php } else if ($showDropdown) { ?>
		<div class="es-profile-header-nav__item<?php echo $active == 'apps' || $isMoreActive ? ' is-active' : '';?>">
			<div class="o-btn-group">
				<a href="javascript:void(0);" class="es-profile-header-nav__link dropdown-toggle_<?php echo $isMoreHasNotice ? ' has-notice' : ''; ?>" data-es-toggle="dropdown" data-button="">
				<span data-text=""><?php echo JText::_('COM_ES_MORE');?></span>
				&nbsp;<i class="i-chevron i-chevron--down"></i>
				&nbsp;<span class="es-profile-header-nav__link-bubble"></span>
				</a>
				<ul class="dropdown-menu dropdown-menu-right es-profile-header-nav__dropdown-menu">
				<?php if ($appsDropdown) { ?>
					<?php foreach ($appsDropdown as $app) { ?>
					<li class="<?php echo $active == $app->active ? 'is-active' : '';?><?php echo $app->hasNotice ? ' has-notice' : '';?>">
						<a href="<?php echo $app->permalink;?>" class="es-profile-header-nav__dropdown-link" title="<?php echo $app->pageTitle; ?>">
							<?php echo $app->title; ?>
						</a>
						<span class="es-profile-header-nav__link-bubble"></span>
					</li>
					<?php } ?>
					<?php if ($showBrowseApps) { ?>
					<li class="divider"></li>
					<li>
						<a href="<?php echo ESR::apps();?>"><?php echo JText::_('COM_EASYSOCIAL_BROWSE'); ?></a>
					</li>
					<?php } ?>
				<?php } ?>
				</ul>
			</div>
		</div>
		<?php } ?>
	<?php } ?>

	<?php if (!$showDropdown && $showBrowseApps) { ?>
	<div class="es-profile-header-nav__item" data-es-nav-item>
		<a href="<?php echo ESR::apps();?>" class="es-profile-header-nav__link">
			<span>&nbsp;<?php echo JText::_('COM_EASYSOCIAL_BROWSE'); ?></span>
		</a>
	</div>
	<?php } ?>
</div>
<?php } ?>
