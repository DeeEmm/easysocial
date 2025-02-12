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
<div class="es-mobile-info">
	<div class="es-side-widget">
		<?php echo $this->html('widget.title', 'COM_ES_STATISTICS'); ?>

		<div class="es-side-widget__bd">
			<ul class="o-nav o-nav--stacked">
				<li class="o-nav__item t-lg-mb--sm">
					<span class="o-nav__link t-text--muted">
						<i class="es-side-widget__icon fa fa-rss t-lg-mr--md"></i>
						<?php echo JText::sprintf('APP_FEEDS_TOTAL_FEEDS', $total); ?>
					</span>
				</li>
			</ul>
		</div>
	</div>
</div>

<div class="es-mobile-filter">
	<div class="es-mobile-filter__hd">
	<?php if ($user->isViewer()) { ?>
		<?php echo $this->html('mobile.filterActions',
				array(
					$this->html('mobile.filterAction', 'APP_FEEDS_NEW_FEED', 'javascript:void(0);', array('data-feeds-create'))
				)
		); ?>
	<?php } ?>
	</div>
</div>
