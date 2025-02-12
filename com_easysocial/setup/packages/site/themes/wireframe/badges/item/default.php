<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-container">
	<div class="es-content">
		
		<div class="es-stage es-island" data-badge data-id="<?php echo $badge->id; ?>" data-total-achievers="<?php echo $totalAchievers; ?>">

			<div class="es-stage__curtain es-bleed--top">

				<h3 class="es-stage__title">
					<?php echo $badge->_('title'); ?>
				</h3>
				<div class="es-stage__desc">
					<?php echo $badge->_('description'); ?>
				</div>
				<div class="es-stage__actor">
					
					<div class="es-stage__actor-img es-stage__actor-img--rounded">
						<img class="es-badges-icon" alt="<?php echo $this->html('string.escape', $badge->_('title') );?>" src="<?php echo $badge->getAvatar();?>" />
					</div>
					<h5 class="es-stage__actor-title">
						<?php echo JText::_('COM_EASYSOCIAL_BADGES_TO_UNLOCK');?>
					</h5>
					<div class="es-stage__actor-desc">
						<?php echo $badge->_('howto'); ?>
					</div>
				</div>

			</div>

			<div class="es-stage__audience">
				
				<div class="es-stage__audience-title">
					<b><?php echo JText::_('COM_EASYSOCIAL_BADGES_ACHIEVERS');?>:</b>
					<span><?php echo $totalAchievers;?></span>
				</div>

				<div class="es-stage__audience-result">
					<div class="t-text--center <?php echo !$achievers ? 'is-empty' : '';?>">
						<?php if ($achievers) { ?>
							<ul data-badge-achievers-list class="g-list-inline t-text--center">
								<?php foreach ($achievers as $user) { ?>  
									<?php echo $this->loadTemplate('site/badges/item/achiever', array('user' => $user)); ?>
								<?php } ?>
							</ul>

							<span class=""><?php echo JText::_('COM_EASYSOCIAL_BADGES_PRIVACY_NOTE'); ?></span>

							<?php if ($totalAchievers > 0 && $totalAchievers > ES::getLimit('achieverslimit')) { ?>
								<div>
									<a data-badge-achievers-load data-nextlimit="<?php echo ES::getLimit('achieverslimit'); ?>" href="javascript:void(0);" class="btn btn-es-default btn-sm t-lg-mt--lg"><?php echo JText::_('COM_EASYSOCIAL_BADGES_LOAD_MORE_ACHIEVERS'); ?></a>

									<div class="o-loader"></div>
								</div>
							<?php } ?>
						<?php } ?>

						<?php echo $this->html('html.emptyBlock', 'COM_EASYSOCIAL_BADGES_EMPTY_ACHIEVERS', 'fa-users'); ?>
					</div>
				</div>

			</div>
		</div>

	</div>
	
</div>
