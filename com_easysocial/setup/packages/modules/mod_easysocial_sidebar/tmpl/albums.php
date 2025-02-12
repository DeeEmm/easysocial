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
<div id="es" class="mod-es mod-es-sidebar-albums <?php echo $this->lib->getSuffix();?>">
	<div class="es-sidebar">

		<?php echo $this->lib->render('module', 'es-albums-all-sidebar-top', 'site/dashboard/sidebar.module.wrapper'); ?>

		<?php if ($lib->canCreateAlbums() && !$lib->exceededLimits()) { ?>
			<a href="<?php echo $lib->getCreateLink();?>" class="btn btn-es-primary btn-create btn-block t-lg-mb--xl"><?php echo JText::_('COM_EASYSOCIAL_ALBUMS_CREATE_ALBUM'); ?></a>
		<?php } ?>

		<div class="es-side-widget">
			<?php echo $this->lib->html('widget.title', JText::_('COM_EASYSOCIAL_PAGE_TITLE_ALBUMS')); ?>

			<div class="es-side-widget__bd">
				<ul class="o-tabs o-tabs--stacked">
					<li class="o-tabs__item has-notice <?php echo $filter == 'all' ? ' active' : ''; ?>">
						<a href="<?php echo ESR::albums();?>" title="<?php echo JText::_('COM_EASYSOCIAL_ALBUMS_FILTER_ALL_ALBUMS', true);?>" class="o-tabs__link">
							<?php echo JText::_('COM_EASYSOCIAL_ALBUMS_FILTER_ALL_ALBUMS');?>
						</a>
						<span class="o-tabs__bubble" data-counter><?php echo $total; ?></span>
						<div class="o-loader o-loader--sm"></div>
					</li>
					<?php if ($this->lib->my->id) { ?>
						<li class="o-tabs__item has-notice">
							<a href="<?php echo ESR::albums(array('layout' => 'mine'));?>" title="<?php echo JText::_('COM_EASYSOCIAL_ALBUMS_FILTER_MY_ALBUMS', true);?>" class="o-tabs__link">
								<?php echo JText::_('COM_EASYSOCIAL_ALBUMS_FILTER_MY_ALBUMS');?>
							</a>
							<span class="o-tabs__bubble" data-counter><?php echo $myTotal; ?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>
						<li class="o-tabs__item has-notice <?php echo $filter == 'favourite' ? ' active' : ''; ?>">
							<a href="<?php echo ESR::albums(array('layout' => 'favourite'));?>" title="<?php echo JText::_('COM_EASYSOCIAL_ALBUMS_FILTER_FAVOURITE_ALBUMS', true);?>" class="o-tabs__link">
								<?php echo JText::_('COM_EASYSOCIAL_ALBUMS_FILTER_FAVOURITE_ALBUMS');?>
							</a>
							<span class="o-tabs__bubble" data-counter><?php echo $myFavouriteTotal; ?></span>
							<div class="o-loader o-loader--sm"></div>
						</li>
					<?php } ?>
				</ul>
			</div>
		</div>

		<?php echo $this->lib->render('module', 'es-albums-all-sidebar-bottom', 'site/dashboard/sidebar.module.wrapper'); ?>
	</div>
</div>
