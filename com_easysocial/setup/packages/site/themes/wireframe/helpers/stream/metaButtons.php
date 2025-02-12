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
<?php if ($displayMention || $displayLocation || $displayMoods || $displayGiphy || $displayBackgrounds) { ?>
	<div class="es-story-meta-buttons">
		<?php if ($displayMention) { ?>
		<div class="btn btn-es-default-o es-story-meta-button" data-story-meta-button="friends" data-es-provide="tooltip" data-title="<?php echo JText::_('COM_EASYSOCIAL_STORY_META_PEOPLE');?>">
			<i class="fa fa-user-friends"></i>
		</div>
		<?php } ?>

		<?php if ($displayLocation) { ?>
		<div class="btn btn-es-default-o es-story-meta-button" data-story-meta-button="location" data-es-provide="tooltip" data-title="<?php echo JText::_('COM_EASYSOCIAL_STORY_META_LOCATION');?>">
			<i class="fa fa-map-marker-alt"></i>
		</div>
		<?php } ?>

		<?php if ($displayMoods) { ?>
		<div class="btn btn-es-default-o es-story-meta-button" data-story-meta-button="mood" data-es-provide="tooltip" data-title="<?php echo JText::_('COM_EASYSOCIAL_STORY_META_MOOD');?>">
			<i class="fa fa-smile"></i>
		</div>
		<?php } ?>

		<?php if ($displayGiphy) { ?>
		<div class="btn btn-es-default-o es-story-meta-button" data-story-giphy-meta-button data-story-meta-button="giphy" data-es-provide="tooltip" data-title="<?php echo JText::_('COM_ES_STORY_META_GIPHY');?>">
			<span class="" for="input-gif" data-story-giphy-button>
				GIF
			</span>
		</div>
		<?php } ?>

		<?php if ($displayBackgrounds) { ?>
		<div class="btn btn-es-default-o es-story-meta-button" data-story-meta-button="bg-select" data-es-provide="tooltip" data-title="<?php echo JText::_('COM_EASYSOCIAL_STORY_META_BACKGROUND');?>">
			<i class="fa fa-fill-drip"></i>
		</div>
		<?php } ?>
	</div>
<?php } ?>