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
<?php if ($activeGenre) { ?>
<div class="t-lg-mb--xl">
	<?php echo $this->html('miniheader.audioGenre', $activeGenre); ?>
</div>
<?php } ?>

<?php if ($featuredAudios && isset($featuredOutput) && $featuredOutput) { ?>
	<div class="es-snackbar2">
		<div class="es-snackbar2__context">
			<div class="es-snackbar2__title">
				<?php echo JText::_("COM_ES_AUDIO_FEATURED_AUDIOS");?>
			</div>
		</div>

		<div class="es-snackbar2__actions">
			<a href="<?php echo ESR::audios(array('filter' => 'featured')); ?>" class="btn btn-sm btn-es-default-o">
				<?php echo JText::_('COM_ES_VIEW_ALL'); ?>
			</a>
		</div>
	</div>

	<?php echo $featuredOutput; ?>
<?php } ?>

<?php echo $this->output('site/audios/default/items.header'); ?>

<div class="es-list-result">
	<?php echo $this->html('html.loading'); ?>

	<div data-result-list>
		<?php echo $this->loadTemplate('site/audios/default/item.list', array('audios' => $audios, 'pagination' => $pagination, 'uid' => $uid, 'type' => $type, 'browseView' => $browseView, 'from' => $from, 'returnUrl' => $returnUrl, 'lists' => $lists)); ?>
	</div>
</div>
