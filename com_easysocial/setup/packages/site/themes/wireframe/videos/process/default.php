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

<?php if ($cluster) { ?>
	<?php echo $this->html('html.miniheader', $cluster); ?>
<?php } ?>

<form action="<?php echo JRoute::_('index.php');?>" method="post" enctype="multipart/form-data">

	<div class="es-container es-videos es-video-form" data-video-process data-id="<?php echo $video->getItem()->id;?>">
		<div class="es-content">
			<div class="es-videos-content-wrapper">

				<?php echo $this->html('html.snackbar', 'COM_EASYSOCIAL_VIDEOS_PROCESSING_VIDEO', 'div'); ?>

				<div class="es-video-progress-area t-lg-mb--sm">
					<div class="es-progress-wrap">
						<div class="progress">
							<div style="width: 1%" class="progress-bar progress-bar-success" data-video-progress-bar></div>
						</div>
						<div class="progress-result" data-video-progress-result>0%</div>
					</div>

					<div class="t-lg-mt--xl t-text--muted"><?php echo JText::_('COM_EASYSOCIAL_VIDEOS_PROCESSING_VIDEO_DESC');?></div>
				</div>
			</div>
		</div>
	</div>
</form>
