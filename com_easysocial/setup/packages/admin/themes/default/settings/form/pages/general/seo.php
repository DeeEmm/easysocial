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
			<?php echo $this->html('panel.heading', 'COM_ES_GENERAL_SETTINGS_SEO_GENERAL'); ?>

			<div class="panel-body">
				<?php echo $this->html('settings.toggle', 'seo.clusters.allowduplicatetitle', 'COM_ES_CLUSTERS_ALLOW_DUPLICATE_TITLE'); ?>

				<?php echo $this->html('settings.toggle', 'seo.useid', 'COM_ES_SEO_USEID_TITLE'); ?>


				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_SEO_MEDIASEF_TITLE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'seo.mediasef', $this->config->get('seo.mediasef'), array(
								array('value' => SOCIAL_MEDIA_SEF_DEFAULT, 'text' => 'COM_ES_SEO_MEDIASEF_DEFAULT'),
								array('value' => SOCIAL_MEDIA_SEF_WITHUSER, 'text' => 'COM_ES_SEO_MEDIASEF_WITHUSER')
							), ''); ?>

						<div class="t-lg-mt--md">
							<?php echo JText::_('COM_ES_SEO_MEDIASEF_NOTICE_DEFAULT'); ?>
							<br /><br />
							<?php echo JText::_('COM_ES_SEO_MEDIASEF_NOTICE_WITHUSER'); ?>
						</div>
					</div>
				</div>

				<?php echo $this->html('settings.toggle', 'seo.cachefile.enabled', 'COM_ES_SEO_CACHEFILE_TITLE', '', '', 'COM_ES_SEO_CACHEFILE_NOTICE'); ?>

			</div>
		</div>
	</div>
</div>
