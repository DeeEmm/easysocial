<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php if ($params->get('use_maps')) { ?>
<div class="es-locations" data-location-base>
	<div class="es-location-help">
		<a href="<?php echo $url; ?>" target="_blank"><?php echo JText::_('FIELDS_USER_ADDRESS_VIEW_IN_' . strtoupper($locationProvider)); ?></a>
	</div>
	<div id="addressfield-map-<?php echo $field->id; ?>" class="es-location-map" data-location-map data-latitude="<?php echo ES::string()->escape($value->latitude); ?>" data-longitude="<?php echo ES::string()->escape($value->longitude); ?>" data-location-provider="<?php echo $this->config->get('location.provider'); ?>">

		<div>
			<img class="es-location-map-image" data-location-map-image />
			<div class="es-location-map-actions">
				<button class="btn btn-es es-location-detect-button" type="button" data-location-detect><i class="fa fa-flash"></i> <?php echo JText::_('COM_EASYSOCIAL_DETECT_MY_LOCATION', true); ?></button>
			</div>
		</div>
	</div>

	<div>
		<?php echo (isset($advancedsearchlink['map']) && $advancedsearchlink['map']) ? '<a href="' . $advancedsearchlink['map'] . '">' : ''; ?>
			<?php echo ES::string()->escape($value->toString()); ?>
		<?php echo (isset($advancedsearchlink['map']) && $advancedsearchlink['map']) ? '</a>' : ''; ?>
	</div>
</div>
<?php } else { ?>

	<?php if ($show['address1'] && !empty($value->address1)) { ?>
	<div><?php echo ES::string()->escape($value->address1); ?></div>
	<?php } ?>

	<?php if ($show['address2'] && !empty($value->address2)) { ?>
	<div><?php echo ES::string()->escape($value->address2); ?></div>
	<?php } ?>

	<?php if ($show['city'] && !empty($value->city)) { ?>
		<?php echo (isset($advancedsearchlink['city']) && $advancedsearchlink['city']) ? '<a href="' . $advancedsearchlink['city'] . '">' : ''; ?>
			<div><?php echo ES::string()->escape($value->city); ?></div>
		<?php echo (isset($advancedsearchlink['city']) && $advancedsearchlink['city']) ? '</a>' : ''; ?>
	<?php } ?>

	<?php if ($show['state'] && !empty($value->state)) { ?>
		<?php echo (isset($advancedsearchlink['state']) && $advancedsearchlink['state']) ? '<a href="' . $advancedsearchlink['state'] . '">' : ''; ?>
			<div><?php echo ES::string()->escape($value->state); ?></div>
		<?php echo (isset($advancedsearchlink['state']) && $advancedsearchlink['state']) ? '</a>' : ''; ?>
	<?php } ?>

	<?php if (!empty($value->zip) || !empty($value->country)) { ?>
	<div>
		<?php if ($show['zip'] && $value->zip) { ?>
			<?php echo (isset($advancedsearchlink['zip']) && $advancedsearchlink['zip']) ? '<a href="' . $advancedsearchlink['zip'] . '">' : ''; ?>
				<?php if (!empty($value->zip)) { echo ES::string()->escape($value->zip); } ?>
			<?php echo (isset($advancedsearchlink['zip']) && $advancedsearchlink['zip']) ? '</a>' : ''; ?>
			<?php if (!empty($value->zip) && !empty($value->country)) { echo ' '; } ?>
		<?php } ?>

		<?php if ($show['country'] && $value->country) { ?>
			<?php echo (isset($advancedsearchlink['country']) && $advancedsearchlink['country']) ? '<a href="' . $advancedsearchlink['country'] . '">' : ''; ?>
			<?php if (!empty($value->country)) { echo ES::string()->escape($value->country); } ?>
			<?php echo (isset($advancedsearchlink['country']) && $advancedsearchlink['country']) ? '</a>' : ''; ?>
		<?php } ?>
	</div>
	<?php } ?>
<?php } ?>
