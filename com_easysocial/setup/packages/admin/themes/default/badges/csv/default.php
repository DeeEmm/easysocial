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
<form name="adminForm" id="adminForm" class="pointsForm" method="post" enctype="multipart/form-data">
	<div class="row">
		<div class="col-md-6">
			<div class="panel">
				<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_BADGES_INSTALL_UPLOAD_CSV'); ?>
				<div class="panel-body">
					<blockquote>
						"USER_ID", "BADGE_ID", "ACHIEVED_DATE", "CUSTOM_MESSAGE", "PUBLISH_STREAM"
					</blockquote>

					<div class="mb-20 mt-20">
						<ul class="g-list-unstyled">
							<li class="t-lg-mb--md">
								<code>USER_ID</code> - <?php echo JText::_('COM_EASYSOCIAL_BADGES_CSV_USER_ID_DESC'); ?>
							</li>
							<li class="t-lg-mb--md">
								<code>BADGE_ID</code> - <?php echo JText::_('COM_EASYSOCIAL_BADGES_CSV_ID_DESC'); ?>
							</li>
							<li class="t-lg-mb--md">
								<code>ACHIEVED_DATE</code> (<?php echo JText::_('COM_EASYSOCIAL_OPTIONAL');?>) - <?php echo JText::_('COM_EASYSOCIAL_BADGES_CSV_DATE_DESC'); ?>
							</li>
							<li class="t-lg-mb--md">
								<code>CUSTOM_MESSAGE</code> (<?php echo JText::_('COM_EASYSOCIAL_OPTIONAL');?>) - <?php echo JText::_('COM_EASYSOCIAL_BADGES_CSV_CUSTOM_MSG'); ?>
							</li>
							<li class="t-lg-mb--md">
								<code>PUBLISH_STREAM</code> (<?php echo JText::_('COM_EASYSOCIAL_OPTIONAL');?>) - <?php echo JText::_('COM_EASYSOCIAL_BADGES_CSV_PUBLISH_STREAM'); ?>
							</li>
						</ul>
					</div>
					<div class="t-lg-mt--xl t-lg-mb--xl">
						<input type="file" name="package" id="package" class="input" style="width:265px;" data-uniform />
						<br />
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php echo $this->html('form.action', 'badges', 'massAssign'); ?>
</form>