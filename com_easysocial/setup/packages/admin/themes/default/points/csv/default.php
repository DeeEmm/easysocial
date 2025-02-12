<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
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
				<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_POINTS_INSTALL_UPLOAD_CSV', 'COM_EASYSOCIAL_POINTS_INSTALL_UPLOAD_CSV_DESC'); ?>

				<div class="panel-body clearfix">
					<div class="t-lg-mb--xl">
						<code>"USER_ID"</code> , <code>"POINTS"</code> , <code>"CUSTOM_MESSAGE"</code>
					</div>

					<div>
						<ul class="g-list-unstyled">
							<li>
								<code>USER_ID</code> - <?php echo JText::_('COM_EASYSOCIAL_POINTS_CSV_USER_ID_DESC'); ?>
							</li>
							<li class="mt-5">
								<code>POINTS</code> - <?php echo JText::_('COM_EASYSOCIAL_POINTS_CSV_POINTS_DESC'); ?>
							</li>
							<li class="mt-5">
								<code>CUSTOM_MESSAGE</code> (<?php echo JText::_('COM_EASYSOCIAL_OPTIONAL');?>) - <?php echo JText::_('COM_EASYSOCIAL_POINTS_CSV_CUSTOM_MSG'); ?>
							</li>
						</ul>
					</div>

					<div>
						<input type="file" name="package" id="package" class="input" style="width:265px;" data-uniform />
					</div>
				</div>
			</div>
		</div>

	</div>
	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="controller" value="points" />
	<input type="hidden" name="task" value="massAssign" />
	<?php echo JHTML::_( 'form.token' );?>
</form>
