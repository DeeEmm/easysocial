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
<div class="panel" data-custom-fields-map style="display: none;">
	<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_MIGRATORS_BIND_CUSTOM_FIELDS'); ?>

	<div class="panel-body">
		<table class="table table-bordered table-hover">
			<thead>
			<tr>
				<td>
					<?php echo JText::_( 'COM_EASYSOCIAL_MIGRATORS_CB_FIELDS' ); ?>
				</td>
				<td>
					<?php echo JText::_( 'COM_EASYSOCIAL_MIGRATORS_EASYSOCIAL_FIELDS' ); ?>
				</td>
			</tr>
			</thead>

			<tbody>
				<?php foreach( $cbFields as $cbField ){ ?>
				<tr data-row-item>
					<td>
						<div>
							<?php

								$cbTitle = JText::_( $cbField->title );
								$cbTitle = str_replace( '_UE_', '', $cbTitle );
								echo $cbTitle;
							?>
						</div>
						<div>
							<span class="label label-warning"><?php echo ucfirst( $cbField->type );?></span>
						</div>
					</td>
					<td>
						<select name="field_<?php echo $cbField->fieldid;?>" data-field-item autocomplete="off" class="o-form-control input-sm">
							<option value=""><?php echo JText::_( 'COM_EASYSOCIAL_FILTER_SELECT_CUSTOM_FIELD' ); ?></option>
							<?php foreach( $fields as $field ){ ?>
							<option value="<?php echo $field->id;?>"<?php echo $cbField->map_id == $field->id ? ' selected="selected"' : '';?>>
								<?php echo $field->get( 'title' );?> <?php echo JText::_( 'Field' );?>
							</option>
							<?php } ?>
						</select>
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>

		<div class="clearfix form-actions">
			<a href="<?php echo JRoute::_( 'index.php?option=com_easysocial&view=migrators&layout=jomsocial' ); ?>" class="btn btn-large btn-es-danger pull-left"><?php echo JText::_( 'COM_EASYSOCIAL_MIGRATOR_CANCEL' );?></a>
			<a href="javascript:void(0);" class="btn btn-large btn-es-primary pull-right" data-start-migration><?php echo JText::_( 'COM_EASYSOCIAL_MIGRATOR_NEXT' );?></a>
		</div>
	</div>
</div>
