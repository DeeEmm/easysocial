<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-cat-header">
	<div class="es-cat-header__hd">
		<div class="o-flag">
			<div class="o-flag__image o-flag--top">
				<?php echo $this->html('avatar.clusterCategory', $category); ?>
			</div>
			<div class="o-flag__body">
				<div class="es-cat-header__hd-content-wrap">
					<div class="es-cat-header__hd-content">
						<div class="es-cat-header__title-link"><?php echo $category->_('title'); ?></div>
						<div class="es-cat-header__desc">
							<?php echo $category->_('description'); ?>
						</div>

						<?php if ($childs) { ?>
							<b><?php echo JText::_('COM_ES_CLUSTER_SUBCATEGORIES'); ?>: </b>
							<ul class="g-list-inline g-list-inline--delimited">
								<?php if (count($childs) > 5) { ?>
									<?php for ($i = 0; $i < 4; $i++) { ?>
									<li data-breadcrumb="/">
										<a href="<?php echo $childs[$i]->getPermalink(); ?>"><?php echo $childs[$i]->getTitle(); ?></a>
									</li>
									<?php } ?>

									<li data-breadcrumb="/" class="g-list-inline__last-item">
										<div class="o-btn-group">
											<a href="javascript:void(0);" class=" dropdown-toggle_" data-es-toggle="dropdown">
												<?php echo JText::_('COM_EASYSOCIAL_READMORE'); ?> <i class="i-chevron i-chevron--down"></i>
											</a>

											<ul class="dropdown-menu dropdown-menu-right">
												<?php for ($i=4; $i < count($childs); $i++) { ?>
													<li>
														<a href="<?php echo $childs[$i]->getPermalink(); ?>"><?php echo $childs[$i]->getTitle(); ?></a>
													</li>
												<?php } ?>
											</ul>
										</div>
									</li>
								<?php } else { ?>
									<?php foreach ($childs as $child) { ?>
										<li data-breadcrumb="/">
											<a href="<?php echo $child->getPermalink(); ?>"><?php echo $child->getTitle(); ?></a>
										</li>
									<?php } ?>
								<?php } ?>
							</ul>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="es-cat-header__ft">
		<div class="pull-left">
			<ul class="g-list-inline g-list-inline--space-right">
				<li>
					<i class="fa fa-users"></i> <?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_PAGES_COUNT', $totalPages), $totalPages); ?>
				</li>
				<li>
					<i class="far fa-image"></i> <?php echo JText::sprintf(ES::string()->computeNoun('COM_EASYSOCIAL_PAGES_ALBUMS', $totalAlbums), $totalAlbums); ?>
				</li>
			</ul>
		</div>

		<?php if ($this->my->canCreatePages() && $category->hasAccess('create', $this->my->profile_id) && !$category->container) { ?>
		<a href="<?php echo ESR::pages(array('controller' => 'pages', 'task' => 'selectCategory', 'category_id' => $category->id));?>" class="btn btn-es-primary btn-sm pull-right">
			<?php echo JText::_('COM_EASYSOCIAL_CREATE_PAGE_BUTTON'); ?>
		</a>
		<?php } ?>

	</div>
</div>
