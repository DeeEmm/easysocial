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
$i = 0;
?>
<div class="es-wf-content" data-content-apps-header>
	<div class="es-wf-content__hd">
		<div>
			<div class="es-wf-content-step-title" data-step-title><?php echo JText::_('Apps Menu Positioning'); ?></div>
			<div class="es-wf-content-step-desc" data-step-description><?php echo JText::_('Configure apps menu in the user profile header here'); ?></div>
		</div>
	</div>

	<div class="es-wf-content__bd" data-apps-wrapper>
		<div>
			<div class="es-wf-field es-wf-field--header es-wf-field--header">
				<div>
					<span><?php echo JText::_('All of the apps menu listed below will be display on the profile header'); ?></span>
				</div>
			</div>
		</div>
		<div data-apps>
			<?php foreach ($apps as $app) { ?>

				<?php if (!$app->isMore) { ?>
				<div class="es-wf-field" data-app-item data-ordering="<?php echo $i; ?>" data-element="<?php echo $app->element; ?>" data-raw-title="<?php echo $app->rawTitle; ?>">
					<div>
						<a href="javascript:void(0);" class="es-wf-field__drag-icon">
							<i class="fa fa-bars"></i>
						</a>
						<span data-app-item-title data-app-item-edit><?php echo JText::_($app->title); ?></span>
					</div>
					<div class="es-wf-field__action">
						<div class="es-wf-action">
							<span class="o-label o-label--primary t-lg-mr--md" data-app-item-element><?php echo strtoupper(str_ireplace('_', ' ', $app->element)); ?></span>
						</div>
					</div>
				</div>
				<?php } ?>

				<?php if ($app->isMore || (!$selected && $i == 4)) { ?>
				<div class="es-wf-field es-wf-field--header es-wf-field--header do-not-move" data-app-item data-element="es-more-section">
					<div>
						<span><?php echo JText::_('Everything below this section will be displayed under "More" dropdown in the profile header'); ?></span>
					</div>
				</div>
				<?php } ?>

			<?php $i++; ?>
			<?php } ?>
		</div>
	</div>
</div>

<input type="hidden" name="<?php echo $name; ?>" value="<?php echo $this->html('string.escape', $selected); ?>" data-app-ordering-value />
