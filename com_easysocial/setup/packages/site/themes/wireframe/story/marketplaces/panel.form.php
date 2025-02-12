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
<div data-marketplace-view class="es-album-view es-media-group t-lg-mb--lg">
	<div data-marketplace-content class="es-album-content">
		<div data-marketplace-upload-button class="es-album-upload-button">
			<span>
				<b class="add-hint">
					<i class="fa fa-plus"></i>&nbsp; <?php echo JText::_("COM_EASYSOCIAL_STORY_ADD_PHOTO"); ?>
				</b>
				<b class="drop-hint">
					<i class="fa fa-upload"></i>&nbsp; <?php echo ES::getUploadMessage('photos'); ?>
				</b>
				</b>
			</span>
		</div>
		<div data-photo-item-group class="es-photo-item-group">
		</div>
	</div>

	<div class="t-hidden" data-uploader-template>
		<div id="" data-wrapper class="es-photo-upload-item es-photo-item">
			<div>
				<div>
					<table>
						<tr class="upload-status">
							<td>
								<div class="upload-title">
									<span class="upload-title-pending"><?php echo JText::_('COM_EASYSOCIAL_UPLOAD_PENDING'); ?></span>
									<span class="upload-title-preparing"><?php echo JText::_('COM_EASYSOCIAL_UPLOAD_PREPARING'); ?></span>
									<span class="upload-title-uploading"><?php echo JText::_('COM_EASYSOCIAL_UPLOAD_UPLOADING'); ?></span>
									<span class="upload-title-failed"><?php echo JText::_('COM_EASYSOCIAL_UPLOAD_FAILED'); ?> <span class="upload-details-button" data-upload-failed-link>(<?php echo JText::_('COM_EASYSOCIAL_UPLOAD_SEE_DETAILS'); ?>)</span></span>
									<span class="upload-title-done"><?php echo JText::_('COM_EASYSOCIAL_UPLOAD_DONE'); ?></span>
								</div>

								<div class="upload-filename" data-file-name></div>

								<div class="upload-progress progress progress-striped active">
									<div class="upload-progress-bar bar progress-bar-info" style="width: 0%"><span class="upload-percentage"></span></div>
								</div>

								<div class="upload-filesize"><span class="upload-filesize-total"></span> (<span class="upload-filesize-left"></span> <?php echo JText::_('COM_EASYSOCIAL_UPLOAD_LEFT'); ?>)</div>

								<div class="upload-remove-button"><i class="fa fa-times"></i></div>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="o-form-group">
	<input type="text" class="o-form-control" placeholder="<?php echo $titleField->get('title'); ?>" <?php echo $titleReadOnly ? 'disabled="disabled"' : ''; ?> value="<?php echo $titleField->default ? $titleField->default : ''; ?>" data-marketplace-title />
</div>

<?php if (isset($descField)) { ?>
<div class="o-form-group">
	<textarea name="description" id="description" class="o-form-control" placeholder="<?php echo $descField->title; ?>" data-required="<?php echo $descField->required ? 'true' : 'false'; ?>" data-required-message="<?php echo JText::sprintf('PLG_FIELDS_MARKETPLACE_VALIDATION_INPUT_REQUIRED', $descField->title); ?>" data-marketplace-description></textarea>
</div>
<?php } ?>


<div class="o-grid o-grid--gutters">
	<?php if (isset($priceField)) { ?>
		<div class="o-grid__cell o-grid__cell--auto-size o-col--5">
			<div class="o-form-group t-lg-mb--no">
				<div class="o-input-group ">
					<?php if ($this->config->get('marketplaces.multicurrency')) { ?>
						<div class="o-input-group__select">
							<div class="o-select-group">
								<select name="currency" id="currency" class="o-form-control" data-marketplace-currency>
									<?php foreach ($currencyLabel as $currencyOption) { ?>
										<option value="<?php echo $currencyOption['value']; ?>"><?php echo Jtext::_($currencyOption['text']); ?></option>
									<?php } ?>
								</select>
								<label class="o-select-group__drop"></label>
							</div>
						</div>
					<?php } else { ?>
						<span class="o-input-group__addon"><?php echo ES::currency($currencyDefault)->symbol; ?></span>
						<input id="currency" type="hidden" name="currency" data-marketplace-currency value="<?php echo $currencyDefault; ?>"/>
					<?php } ?>
					<input id="price" type="text" class="o-form-control t-text--center" name="price" value="" data-marketplace-price data-required="<?php echo $priceField->required ? 'true' : 'false'; ?>" data-required-message="<?php echo JText::sprintf('PLG_FIELDS_MARKETPLACE_VALIDATION_INPUT_REQUIRED', $priceField->title); ?>"/>
				</div>
			</div>
		</div>
	<?php } ?>
	<?php if (isset($conditionField)) { ?>
		<div class="o-grid__cell" data-marketplace-condition-wrapper>
			<div class="o-form-group t-lg-mb--no">
				<div class="o-select-group xo-select-group--inline">
					<select class="o-form-control"
						name="condition"
						id="condition"
						data-marketplace-condition
						data-marketplace-condition data-required="<?php echo $conditionField->required ? 'true' : 'false'; ?>" data-required-message="<?php echo JText::sprintf('PLG_FIELDS_MARKETPLACE_VALIDATION_INPUT_REQUIRED', $conditionField->title); ?>"
					>
						<option value=""><?php echo JText::_('COM_ES_MARKETPLACES_CONDITION'); ?></option>
						<option value="<?php echo SOCIAL_MARKETPLACE_CONDITION_NEW;?>"><?php echo JText::_('COM_ES_MARKETPLACES_CONDITION_NEW'); ?></option>
						<option value="<?php echo SOCIAL_MARKETPLACE_CONDITION_USED;?>"><?php echo JText::_('COM_ES_MARKETPLACES_CONDITION_USED'); ?></option>
					</select>
					<label class="o-select-group__drop"></label>
				</div>
			</div>
		</div>
	<?php } ?>
	<?php if (isset($stockField)) { ?>
		<div class="o-grid__cell" data-marketplace-stock-wrapper>
			<div class="o-form-group t-lg-mb--no">
				<input type="number" id="stock"
					value="<?php echo $stockField->default; ?>"
					name="stock"
					min="0"
					class="o-form-control"
					data-marketplace-stock
					data-required="<?php echo $stockField->required ? 'true' : 'false'; ?>" data-required-message="<?php echo JText::sprintf('PLG_FIELDS_MARKETPLACE_VALIDATION_INPUT_REQUIRED', $stockField->title); ?>"
					placeholder="<?php echo $stockField->title; ?>"
				/>
			</div>
		</div>
	<?php } ?>

</div>






