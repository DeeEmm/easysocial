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
<div class="es-container" data-es-apps data-es-container>
	<div class="es-content<?php echo !$apps ? " is-empty " : ''; ?>">

		<div class="es-snackbar2">
			<div class="es-snackbar2__context">
				<div class="es-snackbar2__title">
					<?php echo JText::_('COM_ES_APPS'); ?>
				</div>
			</div>

			<div class="es-snackbar2__actions">
				<div>
					<div class="dropdown_" data-filter-wrapper>
						<button type="button" class="btn btn-sm btn-es-default-o dropdown-toggle_ is-loading" data-es-toggle="dropdown" data-active-filter-button>
							<div class="o-loader o-loader--sm"></div>
							<span data-active-filter-text></span> &nbsp;<i class="fa fa-caret-down"></i>
						</button>

						<ul class="dropdown-menu dropdown-menu-left es-timeline-filter-dropdown">
							<li class="<?php echo $filter == 'browse' ? ' active' : '';?>" data-filter-item="all">
								<a href="<?php echo ESR::apps();?>" title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_BROWSE_APPS', true);?>" data-apps-filter-link>
									<span data-filter-item-text><?php echo JText::_('COM_EASYSOCIAL_APPS_BROWSE_APPS');?></span>
								</a>
							</li>

							<li class="<?php echo $filter == 'mine' ? ' active' : '';?>" data-filter-item="mine">
								<a href="<?php echo ESR::apps(array('filter' => 'mine'));?>" title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_YOUR_APPS', true);?>" data-apps-filter-link>
									<span data-filter-item-text><?php echo JText::_('COM_EASYSOCIAL_APPS_YOUR_APPS');?></span>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>

		<div data-wrapper>
			<?php echo $this->render('module', 'es-apps-before-contents'); ?>

			<?php echo $this->isMobile() ? $this->html('listing.loader', 'listing', 8, 1) : $this->html('listing.loader', 'card', 6, 3); ?>

			<div data-contents>
				<?php echo $this->includeTemplate('site/apps/default/items'); ?>
			</div>

			<?php echo $this->html('html.emptyBlock', 'COM_EASYSOCIAL_APPS_NO_APPS_INSTALLED_YET', 'fa-database'); ?>

			<?php echo $this->render('module', 'es-apps-after-contents'); ?>
		</div>
	</div>
</div>
