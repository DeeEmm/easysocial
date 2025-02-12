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
<div class="es-story-selection-popbox" data-story-selection>
	<div class="es-story-selection-popbox__hd">
		<div class="o-form-horizontal">
			<div class="o-form-group">
				<label class="o-control-label" for="story-select-search">
					<?php echo JText::_('COM_ES_POST_TO');?>:
				</label>
				<div class="o-control-input">
					<input type="text" id="story-select-search" value="" placeholder="<?php echo JText::_('COM_ES_STORY_SELECTION_SEARCH_PLACEHOLDER');?>" class="o-form-control" data-selection-search />
				</div>
			</div>
		</div>
	</div>
	<div class="es-story-selection-popbox__tabs">
		<ul class="o-tabs o-tabs--horizontal">
			<?php if ($this->config->get('groups.enabled')) { ?>
			<li class="o-tabs__item" data-selection="group">
				<a href="javascript:void(0);" class="o-tabs__link">
					<i class="fa fa-users"></i>&nbsp; <?php echo JText::_('COM_ES_GROUPS');?>
				</a>
			</li>
			<?php } ?>

			<?php if ($this->config->get('pages.enabled')) { ?>			
			<li class="o-tabs__item" data-selection="page">
				<a href="javascript:void(0);" class="o-tabs__link">
					<i class="fa fa-briefcase"></i>&nbsp; <?php echo JText::_('COM_ES_PAGES');?>
				</a>
			</li>
			<?php } ?>

			<?php if ($this->config->get('events.enabled')) { ?>
			<li class="o-tabs__item" data-selection="event">
				<a href="javascript:void(0);" class="o-tabs__link">
					<i class="far fa-calendar-alt"></i>&nbsp; <?php echo JText::_('COM_ES_EVENTS');?>
				</a>
			</li>
			<?php } ?>
		</ul>
	</div>

	<?php if ($this->config->get('groups.enabled')) { ?>
	<div class="es-story-selection-popbox__content t-hidden" data-selection-tab="group">
		<?php echo $this->html('html.loading', 'center'); ?>
		<?php echo $this->output('site/story/selection/result', array('clusters' => $groups, 'emptyText' => 'COM_ES_EMPTY_GROUP_POST_TO', 'emptyIcon' => 'fa-users', 'clusterType' => SOCIAL_TYPE_GROUP)); ?>
	</div>
	<?php } ?>

	<?php if ($this->config->get('pages.enabled')) { ?>	
	<div class="es-story-selection-popbox__content t-hidden" data-selection-tab="page">
		<?php echo $this->html('html.loading', 'center'); ?>
		<?php echo $this->output('site/story/selection/result', array('clusters' => $pages, 'emptyText' => 'COM_ES_EMPTY_PAGE_POST_TO', 'emptyIcon' => 'fa-briefcase', 'clusterType' => SOCIAL_TYPE_PAGE)); ?>
	</div>
	<?php } ?>

	<?php if ($this->config->get('events.enabled')) { ?>
	<div class="es-story-selection-popbox__content t-hidden" data-selection-tab="event">
		<?php echo $this->html('html.loading', 'center'); ?>
		<?php echo $this->output('site/story/selection/result', array('clusters' => $events, 'emptyText' => 'COM_ES_EMPTY_EVENT_POST_TO', 'emptyIcon' => 'far fa-calendar', 'clusterType' => SOCIAL_TYPE_EVENT)); ?>
	</div>
	<?php } ?>

	<div class="es-story-selection-popbox__ft">
		<a href="javascript:void(0);" data-story-selector data-uid="<?php echo $this->my->id;?>" data-type="<?php echo SOCIAL_TYPE_USER;?>">
			<i class="fa fa-user"></i>&nbsp; <?php echo JText::_('COM_ES_POST_ON_OWN_TIMELINE'); ?>
		</a>
	</div>
</div>
