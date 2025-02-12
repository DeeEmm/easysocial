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
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_MARKETPLACES_SETTINGS_LAYOUT'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'marketplaces.category.header', 'COM_ES_MARKETPLACES_CATEGORY_HEADERS'); ?>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_MARKETPLACES_SETTINGS_DISPLAY_RECENT_LISTINGS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'marketplaces.layout.item.recent', $this->config->get('marketplaces.layout.item.recent'), array(
								array('value' => SOCIAL_MARKETPLACE_OTHER_NONE, 'text' => 'COM_ES_MARKETPLACES_SETTINGS_DISPLAY_OTHER_LISTING_NONE'),
								array('value' => SOCIAL_MARKETPLACE_OTHER_RECENT, 'text' => 'COM_ES_MARKETPLACES_SETTINGS_DISPLAY_OTHER_LISTING_RECENT'),
								array('value' => SOCIAL_MARKETPLACE_OTHER_CATEGORY, 'text' => 'COM_ES_MARKETPLACES_SETTINGS_DISPLAY_OTHER_LISTING_CATEGORY'),
							)); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_MARKETPLACES_SETTINGS_LISTINGS'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'marketplaces.layout.address', 'COM_ES_MARKETPLACES_SHOW_LOCATION'); ?>
			</div>
		</div>
	</div>
</div>
