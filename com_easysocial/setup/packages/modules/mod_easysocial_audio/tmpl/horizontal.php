<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div id="es" class="mod-es mod-es-pages <?php echo $lib->getSuffix();?>">
	<div class="es-cards es-cards--<?php echo $params->get('column_numbers', 3);?>">
		<?php foreach ($audio as $item) {  ?>
			<div class="es-cards__item">
				<div class="es-card">
				<?php if ($item->isUpload()) { ?>
					<?php echo $item->getUploadEmbedCodes(true); ?>
				<?php } else { ?>
				<div class="es-audio-container is-<?php echo strtolower($item->getLinkProvider()); ?> <?php echo $item->isSpotifyPodcast() ? 'is-podcast' : ''; ?>">
					<?php echo $item->getLinkEmbedCodes(); ?>
				</div>
				<?php } ?>
				</div>
			</div>
		<?php } ?>
	</div>

	<?php if ($params->get('display_alllink', true)) { ?>
	<div class="mod-es-action">
		<a href="<?php echo ESR::audios(); ?>" class="btn btn-es-default-o btn-sm btn-block"><?php echo JText::_('MOD_ES_AUDIO_VIEW_ALL_AUDIO'); ?></a>
	</div>
	<?php } ?>
</div>
