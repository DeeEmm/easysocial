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
<div class="es-stepbar es-island t-lg-mb--lg">
	<div class="o-grid">
		<div class="o-grid__cell o-grid__cell--auto-size">
			<ul class="es-stepbar__lists">
				<li class="stepItem <?php echo $currentStep == SOCIAL_REGISTER_SELECTPROFILE_STEP ? ' active' : '';?><?php echo $currentStep > SOCIAL_REGISTER_SELECTPROFILE_STEP ||  $currentStep == SOCIAL_REGISTER_COMPLETED_STEP ? ' active past' : '';?><?php echo $firstStepLink == 'hidden' ? ' t-hidden' : '';?>"
					data-es-provide="tooltip"
					data-placement="bottom"
					data-original-title="<?php echo $firstStepTooltip;?>"
				>
					<a href="<?php echo $firstStepLink == 'hidden' ? 'javascript:void(0);' : $firstStepLink;?>">
						<i class="fa fa-check"></i>
						<span class="step-number">0</span>
					</a>
				</li>

				<?php $counter = 1; ?>
				<?php foreach ($steps as $step) { ?>
					<?php
						$customClass = $step->sequence == $currentStep || $currentStep > $step->sequence || $currentStep == SOCIAL_REGISTER_COMPLETED_STEP ? ' active' : '';
						$customClass .= $step->sequence < $currentStep || $currentStep == SOCIAL_REGISTER_COMPLETED_STEP ? $customClass . ' past' : '';
					?>
					<li class="divider-vertical <?php echo $customClass;?><?php echo ($firstStepLink == 'hidden' && $counter == 1) ? ' t-hidden' : '';?>"></li>
					<li class="stepItem <?php echo $step->css;?>">
						<a href="<?php echo $step->permalink;?>"
							data-es-provide="tooltip"
							data-placement="bottom"
							data-original-title="<?php echo $step->_('title'); ?>"
						>
							<i class="fa fa-check"></i>
							<span class="step-number"><?php echo $counter;?></span>
						</a>
					</li>
					<?php $counter++; ?>
				<?php } ?>

				<li class="divider-vertical"></li>

				<li class="stepItem last <?php echo $currentStep == SOCIAL_REGISTER_COMPLETED_STEP ? ' active past' : '';?>"
					data-es-provide="tooltip"
					data-placement="bottom"
					data-original-title="<?php echo $lastStepTooltip;?>"
				>
					<a href="<?php echo $lastStepLink;?>"><i class="fa fa-flag"></i></a>
				</li>
			</ul>
		</div>
		<div class="o-grid__cell">
			<div class="divider-vertical-last"></div>
		</div>
	</div>
</div>
