<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div id="es" class="mod-es mod-es-dating-search <?php echo $lib->getSuffix();?>" data-mod-dating-search data-location-provider="<?php echo $config->get('location.provider'); ?>">
<form method="get" action="<?php echo JRoute::_('index.php'); ?>" name="frmSearch" class="mod-es-dating-search-form <?php echo $lib->isMobile() ? 'is-mobile' : '';?>">
	<?php if ($fieldName) { ?>
	<div data-mod-dating-search-item class="o-form-group">
		<label for="es-dating-search-name"><?php echo JText::_($fieldName->title);?></label>

		<input class="o-form-control" id="es-dating-search-name" type="text" value="<?php echo (isset($userData[$fieldName->element]['condition'])) ? $userData[$fieldName->element]['condition'] : ''?>"
				placeholder="<?php echo JText::_($fieldName->placeholder, true);?>" name="conditions[]" data-condition
			/>
		<input class="o-form-control" type="hidden" value="<?php echo $fieldName->unique_key;?>|<?php echo $fieldName->element;?>" name="criterias[]" data-criterias />
		<input class="o-form-control" type="hidden" value="name" name="datakeys[]" data-datakeys />
		<input class="o-form-control" type="hidden" value="contain" name="operators[]" data-operators />
	</div>
	<?php } ?>

	<?php if ($fieldGender) { ?>
	<div data-mod-dating-search-item class="o-form-group">
		<label><?php echo JText::_('MOD_EASYSOCIAL_DATING_SEARCH_GENDER_TITLE');?></label>
		<div class="o-radio">
			<input type="radio" id="item-radio-gender<?php echo $module->id . '.0' ?>" name="search-gender" value="0" <?php echo ($fieldGender->data == "0") ? 'checked="checked"' : ''; ?> data-gender-radio />
			<label for="item-radio-gender<?php echo $module->id . '.0' ?>">
				<?php echo JText::_('MOD_EASYSOCIAL_DATING_SEARCH_GENDER_ALL'); ?>
			</label>
		</div>

		<?php if ($fieldGenderOptions) { ?>
			<?php foreach ($fieldGenderOptions as $option) { ?>
				<div class="o-radio">
					<input type="radio" id="item-radio-gender<?php echo $module->id . '.' . $option->value; ?>" name="search-gender" value="<?php echo $option->value; ?>" <?php echo ($fieldGender->data == $option->value) ? 'checked="checked"' : ''; ?> data-gender-radio />
					<label for="item-radio-gender<?php echo $module->id . '.' . $option->value; ?>">
						<?php echo JText::_($option->title); ?>
					</label>
				</div>
			<?php } ?>
		<?php } ?>

		<input class="o-form-control" type="hidden" value="<?php echo $fieldGender->unique_key;?>|<?php echo $fieldGender->element;?>" name="criterias[]" data-criterias />
		<input class="o-form-control" type="hidden" value="" name="datakeys[]" data-datakeys />
		<input class="o-form-control" type="hidden" value="equal" name="operators[]" data-operators />
		<input class="o-form-control" type="hidden" value="<?php echo $fieldGender->data;?>" name="conditions[]" data-condition />
	</div>
	<?php } ?>

	<?php if ($fieldRelationship) { ?>
	<div data-mod-dating-search-item class="o-form-group">
		<label><?php echo JText::_('MOD_EASYSOCIAL_DATING_SEARCH_RELATIONSHIP_TITLE');?></label>

		<?php if ($fieldRelationshipeOptions) { ?>
			<?php foreach ($fieldRelationshipeOptions as $option) { ?>
				<div class="o-radio">
					<input type="radio" id="item-radio-relationship<?php echo $module->id . '.' . $option->value; ?>" name="search-relationship" value="<?php echo $option->value; ?>" <?php echo ($fieldRelationship->data == $option->value) ? 'checked="checked"' : ''; ?> data-relationship-radio />
					<label for="item-radio-relationship<?php echo $module->id . '.' . $option->value; ?>">
						<?php echo JText::_($option->title); ?>
					</label>
				</div>
			<?php } ?>
		<?php } ?>

		<input class="o-form-control" type="hidden" value="<?php echo $fieldRelationship->unique_key;?>|<?php echo $fieldRelationship->element;?>" name="criterias[]" data-criterias />
		<input class="o-form-control" type="hidden" value="" name="datakeys[]" data-datakeys />
		<input class="o-form-control" type="hidden" value="equal" name="operators[]" data-operators />
		<input class="o-form-control" type="hidden" value="<?php echo $fieldRelationship->data;?>" name="conditions[]" data-condition />
	</div>
	<?php } ?>

	<?php if ($fieldBirthday) { ?>
	<div data-mod-dating-search-item class="o-grid o-grid--gutters">
		<div class="o-grid__cell">
			<div class="o-form-group">
				<label for="es-dating-search-age-from"><?php echo JText::_('MOD_EASYSOCIAL_DATING_SEARCH_AGE_FROM'); ?></label>
				<input class="o-form-control" id="es-dating-search-age-from" type="number" min="1" max="150" placeholder="" value="<?php echo $fieldBirthday->start;?>" name="frmStart" data-start />
			</div>
		</div>
		<div class="o-grid__cell">
			<div class="o-form-group">
				<label for="es-dating-search-age-to"><?php echo JText::_('MOD_EASYSOCIAL_DATING_SEARCH_AGE_TO'); ?></label>
				<input class="o-form-control" id="es-dating-search-age-to" type="number" min="1" max="150" placeholder="" value="<?php echo $fieldBirthday->end;?>" name="frmEnd" data-end />
			</div>
		</div>
		<input class="o-form-control" type="hidden" value="<?php echo $fieldBirthday->unique_key;?>|<?php echo $fieldBirthday->element;?>" name="criterias[]" data-criterias />
		<input class="o-form-control" type="hidden" value="date" name="datakeys[]" data-datakeys />
		<input class="o-form-control" type="hidden" value="between" name="operators[]" data-operators />
		<input class="o-form-control" type="hidden" value="<?php echo $fieldBirthday->dates;?>" name="conditions[]" data-condition />
	</div>
	<?php } ?>

	<?php if ($fieldAddress) { ?>
	<div data-mod-dating-search-item class="mod-search-distance">

		<div class="o-form-group">
			<label for="es-dating-search-distance"><?php echo JText::_('MOD_EASYSOCIAL_DATING_SEARCH_DISTANCE_WITHIN_' . $searchUnit); ?>:</label>
			<input class="o-form-control" id="es-dating-search-distance" type="number"
				   placeholder="<?php echo JText::_('MOD_EASYSOCIAL_DATING_SEARCH_DISTANCE_ENTER_VALUE');?>"
				   value="<?php echo (isset($userData[$fieldAddress->element]['distance'])) ? $userData[$fieldAddress->element]['distance'] : ''?>"
				   name="frmDistance"
				   data-distance />
			<div id="map" class="t-hidden"></div>
		</div>

		<input class="o-form-control" type="hidden" value="<?php echo $fieldAddress->unique_key;?>|<?php echo $fieldAddress->element;?>" name="criterias[]" data-criterias />
		<input class="o-form-control" type="hidden" value="distance" name="datakeys[]" data-datakeys />
		<input type="hidden" name="operators[]" value="lessequal" data-operators>

		<input class="o-form-control <?php echo (isset($userData[$fieldAddress->element]['address'])) ? '' : ' hide'; ?>"
			   type="hidden" value="<?php echo (isset($userData[$fieldAddress->element]['address'])) ? $userData[$fieldAddress->element]['address'] : ''?>" name="frmAddress" data-address />
		<input class="o-form-control" type="hidden" value="<?php echo (isset($userData[$fieldAddress->element]['latitude'])) ? $userData[$fieldAddress->element]['latitude'] : ''?>" name="frmLatitude" data-latitude />
		<input class="o-form-control" type="hidden" value="<?php echo (isset($userData[$fieldAddress->element]['longitude'])) ? $userData[$fieldAddress->element]['longitude'] : ''?>" name="frmLongitude" data-longitude />

		<?php $tmpCondition = isset($userData[$fieldAddress->element]['condition']) ? $userData[$fieldAddress->element]['condition'] : ''; ?>
		<input data-condition type="hidden" class="o-form-control" name="conditions[]" value="<?php echo $lib->html('string.escape', $tmpCondition);?>" />

		<div data-location-label class="o-input-group">
			<input type="text" class="o-form-control" placeholder="<?php echo JText::_('MOD_EASYSOCIAL_DATING_SEARCH_DISTANCE_ENTER_LOCATION'); ?>" autocomplete="off" data-location-textfield  value=""/>

			<span class="o-input-group__btn">
				<button class="btn btn-es-default" type="button" data-location-detect>
					<i class="fa fa-map-marker-alt" data-loaction-icon></i>
				</button>
			</span>
		</div>

		<div class="es-location-autocomplete has-shadow is-sticky" data-location-autocomplete>
			<b><b></b></b>
			<div class="es-location-suggestions" data-location-suggestions>
			</div>
		</div>

		<span class="t-hidden" data-error-map-permission><?php echo JText::_('COM_EASYSOCIAL_LOCATION_PERMISSION_ERROR', true);?></span>
		<span class="t-hidden" data-error-map-timeout><?php echo JText::_('COM_EASYSOCIAL_LOCATION_TIMEOUT_ERROR', true);?></span>
		<span class="t-hidden" data-error-map-unavailable><?php echo JText::_('COM_EASYSOCIAL_LOCATION_UNAVAILABLE_ERROR', true);?></span>

	</div>
	<?php } ?>

	<button class="btn btn-es-primary btn-block t-lg-mt--lg" type="submit" data-advsearch-button>
		<?php echo JText::_('MOD_EASYSOCIAL_DATING_SEARCH_SEARCH_BUTTON');?>
	</button>

	<input type="hidden" name="sort" value="<?php echo $defaultSort; ?>" />

	<?php echo $lib->html('form.token'); ?>
	<?php echo $lib->html('form.itemid'); ?>
	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="view" value="users" />
	<input type="hidden" name="layout" value="search" />
</form>
</div>
