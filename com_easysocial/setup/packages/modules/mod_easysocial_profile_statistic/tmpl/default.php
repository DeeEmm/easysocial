<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div id="es" class="es mod-es-profile-stat module-profile-stat<?php echo $lib->getSuffix();?>">
	<div class="es-side-widget">
		<div class="es-side-widget__bd">
			<div class="t-text--muted">
				<?php echo JText::sprintf('MOD_EASYSOCIAL_PROFILE_STATS_PROFILE_INFO', $my->getProfile()->getTitle()); ?>
			</div>

			<ul class="o-nav o-nav--stacked ">
				<?php foreach ($stat as $key => $item) { ?>
				<li class="o-nav__item ">
					<div class="t-lg-mb--md">
						<b><?php echo JText::_('MOD_EASYSOCIAL_PROFILE_STATS_TITLE_' . strtoupper($key)); ?></b>
						<ol class="g-list-inline g-list-inline--dashed">
							<?php if ($item->text_interval) { ?>
							<li>
								<i class="fa <?php echo $item->icon; ?> t-lg-mr--md"></i>
								<span class="t-text--muted"><?php echo $item->text_interval; ?></span>
							</li>
							<?php } ?>
							<?php if ($item->text_total) { ?>
							<li class="current">
								<?php if (!$item->text_interval) { ?>
								<i class="fa <?php echo $item->icon; ?> t-lg-mr--md"></i>
								<?php } ?>
								<span class="t-text--muted"><?php echo $item->text_total; ?></span>
							</li>
							<?php } ?>
						</ol>
					</div>
				</li>
				<?php } ?>
			</ul>
		</div>
	</div>
</div>
