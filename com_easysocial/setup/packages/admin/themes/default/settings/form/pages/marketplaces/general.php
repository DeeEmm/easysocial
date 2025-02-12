<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="row">
	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_GENERAL_SETTINGS', '', '/administrators/marketplaces/marketplaces'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'marketplaces.enabled', 'COM_ES_GENERAL_SETTINGS_ENABLE_MARKETPLACES'); ?>
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_GENERAL_SETTINGS_MARKETPLACE_CURRENCY'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'marketplaces.currency', $this->config->get('marketplaces.currency'), ES::getCurrencyOptions()); ?>
					</div>
				</div>

				<?php echo $this->html('settings.toggle', 'marketplaces.multicurrency', 'COM_ES_GENERAL_SETTINGS_ALLOW_MULTICURRENCY'); ?>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_MARKETPLACES_SETTINGS_NEARBY_MARKETPLACES_RADIUS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'marketplaces.nearby.radius', $this->config->get('marketplaces.nearby.radius'), ES::getNearbyRadiusOptions()); ?>
					</div>
				</div>

				<?php echo $this->html('settings.toggle', 'marketplaces.editmoderation', 'COM_ES_MARKETPLACES_MODERATE_EDITED_LISTINGS'); ?>
			</div>
		</div>
	</div>
</div>
