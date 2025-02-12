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
<div class="es-dashboard" data-es-dashboard>
	<div class="es-container <?php echo $this->config->get('users.dashboard.sidebar') == 'right' ? 'es-sidebar-right' : '';?>" data-es-container>
		<?php if (!$this->isMobile() && count($this->getModulesFromPosition('es-dashboard-aside')) > 0) { ?>
			<?php $moduleContents = trim($this->render('module', 'es-dashboard-aside', 'site/dashboard/sidebar.module.wrapper')) ?>

			<?php if ($moduleContents) { ?>
			<div class="es-container__sidebar">
				<?php echo $moduleContents; ?>
			</div>
			<?php } ?>
		<?php } ?>

		<div class="es-content">

			<div class="es-dashboard-filters">
				<?php echo $streamFilter->html();?>
			</div>

			<div class="is-loading" style="position: relative; min-height: 150px;" data-wrapper>
				<?php echo $this->html('listing.loader', 'stream', 8); ?>

				<?php echo $this->render('module', 'es-dashboard-before-contents'); ?>

				<div data-contents data-stream-contents>
				</div>

				<?php echo $this->render('module', 'es-dashboard-after-contents'); ?>
			</div>
		</div>
	</div>

	<?php echo $this->html('form.hidden', 'active', $filter, 'data-stream-filter'); ?>
</div>
