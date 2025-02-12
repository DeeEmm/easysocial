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
<?php foreach ($criterias as $criteria) { ?>
<div class="es-adv-search2__item <?php echo $isTemplate ? 't-hidden' : '';?>" data-item <?php echo $isTemplate ? 'data-item-template' : '';?>>
	<div class="es-adv-search2__condition">
		<div class="es-adv-search2__criteria">
			<select class="o-form-control" name="criterias[]" style="min-width:100px;"  data-field>
				<option value=""><?php echo JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_SELECT_CRITERIA'); ?></option>

				<?php foreach ($criteria->fields as $field) { ?>
					<option value="<?php echo $field->unique_key;?>|<?php echo $field->element;?>"<?php echo !$isTemplate && $criteria->selected == $field->unique_key . '|' . $field->element ? ' selected="selected"' : '';;?>>
						<?php echo JText::_($field->title);?>
					</option>
				<?php } ?>
			</select>
		</div>

		<div class="es-adv-search2__condition-item <?php echo !$criteria->haskeys ? 't-hidden' : '';?>" data-wrapper-keys>
			<?php echo $criteria->datakeys; ?>
		</div>

		<div class="es-adv-search2__condition-item--short" data-wrapper-operator>
			<?php echo $criteria->operator; ?>
		</div>

		<div class="es-adv-search2__condition-item" data-wrapper-condition>
			<?php echo $criteria->condition; ?>
		</div>


		<div class="es-adv-search2__action">
			<a href="javascript:void(0);" class="btn btn-es-danger-o" data-remove-criteria>
				<i class="fa fa-minus-circle"></i>
			</a>
		</div>
	</div>


	<div data-location-label class="o-form-group t-hidden mt-10 full-width">
		<div class="o-input-group">
			<input type="text" class="o-form-control" placeholder="<?php echo JText::_('COM_EASYSOCIAL_ADVANCED_DISTANCE_ENTER_LOCATION'); ?>"
				   autocomplete="off" data-location-textfield  value=""/>

			<?php if ($this->config->get('location.provider') == 'osm') { ?>
				<div id="map" class="t-hidden" data-osm-map></div>
			<?php } ?>

			<span class="o-input-group__btn">
				<button class="btn btn-es-default" type="button" data-location-detect>
					<i class="fa fa-flash" data-loaction-icon></i><?php echo JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_DETECT_LOCATION'); ?>
				</button>
			</span>
		</div>
		<div class="es-location-autocomplete has-shadow is-sticky" data-location-autocomplete>
			<b><b></b></b>
			<div class="es-location-suggestions" data-location-suggestions>
			</div>
		</div>
	</div>

	<span data-criteria-notice class="help-block text-note t-hidden"></span>

</div>
<?php } ?>
