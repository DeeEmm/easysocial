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
<div class="es-container">
	<div class="es-content">
		<form method="post" action="<?php echo JRoute::_('index.php');?>" enctype="multipart/form-data">
			<div class="es-forms__group">
				<div class="es-forms__title">
					<div class="es-snackbar2">
						<div class="es-snackbar2__context">
							<div class="es-snackbar2__title">
								<?php echo JText::_('COM_ES_ADS_TITLE');?>
							</div>
						</div>

						<div class="es-snackbar2__actions">
							<a href="<?php echo ESR::ads(['layout' => 'form']);?>" class="btn btn-es btn-sm btn-es-primary">
								<?php echo JText::_('COM_ES_CREATE_NEW_AD'); ?>
							</a>
						</div>
					</div>
				</div>

				<div class="es-forms__content">
					<p class="t-lg-mb--lg">
						<?php echo JText::_('COM_ES_ADS_INFO');?>
					</p>

					<div class="">
						<?php if ($ads) { ?>
							<div class="es-cards es-cards--2">
								<?php foreach($ads as $ad){ ?>
									<?php echo $this->html('listing.ads', $ad, ['style' => $this->isMobile() ? 'listing' : 'card']); ?>
								<?php } ?>
							</div>

						<?php } ?>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
