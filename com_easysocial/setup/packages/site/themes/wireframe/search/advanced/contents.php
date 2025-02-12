<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div>
	<?php if ($this->my->id) { ?>
		<?php if ($activeFilter) { ?>
			<?php echo $this->html('html.snackbar', $activeFilter->title); ?>
		<?php } else if ($results) { ?>
			<?php echo $this->html('html.snackbar', 'COM_EASYSOCIAL_ADVANCED_SEARCH_RESULTS'); ?>
		<?php } else { ?>
			<?php echo $this->html('html.snackbar', 'COM_EASYSOCIAL_ADVANCED_SEARCH_SEARCH_CRITERIA'); ?>
		<?php } ?>
	<?php } ?>

	<div class="es-adv-search2 es-island <?php echo $results || $activeFilter ? 't-hidden' : '';?>" data-search-form>
		<form name="frmAdvSearch" method="post" action="<?php echo ESR::search($routerSegment); ?>" data-form class="es-forms">
			<div class="es-search-criteria t-lg-mb--xl" data-criterias>
				<?php echo $criteriaHTML; ?>
			</div>

			<div class="es-search-add-criteria">
				<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm" data-insert-criteria><?php echo JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_NEW_CRITERIA'); ?></a>
			</div>

			<div class="es-adv-search2__options es-bleed--middle">

				<div class="o-grid">
					<div class="o-grid__cell">
						<div>
							<b><?php echo JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_SEARCH_OPTIONS');?></b>
						</div>

						<div class="es-adv-search2__options-group">
							<div class="o-radio o-radio--inline">
								<input type="radio" name="matchType" id="match-all" value="all" <?php echo $match == 'all' ? ' checked="checked"' : '' ?> />

								<label for="match-all">
									<?php echo JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_MATCH_ALL'); ?>
								</label>
							</div>

							<div class="o-radio o-radio--inline">
								<input type="radio" name="matchType" id="match-any" value="any" <?php echo $match == 'any' ? ' checked="checked"' : '' ?> />

								<label for="match-any">
									<?php echo JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_MATCH_ANY');?>
								</label>
							</div>
						</div>

						<?php if ($lib->getType() == SOCIAL_TYPE_USER && $this->config->get('photos.enabled')) { ?>
						<div class="es-adv-search2__options-group">
							<div class="o-checkbox">
								<input id="avatarOnly" autocomplete="off" type="checkbox" name="avatarOnly" value="1" <?php echo ( $avatarOnly ) ? ' checked="checked"' : '' ?> />
								<label for="avatarOnly">
									<?php echo JText::_("COM_EASYSOCIAL_ADVANCED_SEARCH_WITH_AVATAR"); ?>
								</label>
							</div>
						</div>
						<?php } ?>
					</div>

					<?php if ($lib->getType() == SOCIAL_TYPE_USER) { ?>
					<div class="o-grid__cell">
						<div>
							<b><?php echo JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_SORTING');?></b>
						</div>

						<div class="es-adv-search2__options-group">
							<select class="o-form-control" name="sort" id="sort">
								<option value="default" <?php echo $sort == 'default' ? 'selected' : ''; ?>><?php echo JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_SORT_DEFAULT'); ?></option>
								<option value="registerDate" <?php echo $sort == 'registerDate' ? 'selected' : ''; ?>><?php echo JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_SORT_LATEST'); ?></option>
								<option value="lastvisitDate" <?php echo $sort == 'lastvisitDate' ? 'selected' : ''; ?>><?php echo JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_SORT_LOGIN'); ?></option>
								<option value="alphabetical" <?php echo $sort == 'alphabetical' ? 'selected' : ''; ?>><?php echo JText::_('COM_ES_ADVANCED_SEARCH_SORT_ALPHABETICAL'); ?></option>
							</select>
						</div>
					</div>
					<?php } ?>
				</div>
			</div>

			<div class="o-form-actions es-bleed--bottom">
				<?php if ($activeFilter) { ?>
				<a href="javascript:void(0);" class="btn btn-es-danger-o t-lg-pull-left" data-delete-filter data-id="<?php echo $activeFilter->id;?>">
					<?php echo JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_DELETE_FILTER'); ?>
				</a>
				<?php } ?>

				<button class="btn btn-es-primary t-lg-pull-right" type="submit" data-advsearch-button>
					<?php echo JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_SEARCH_BUTTON');?>
				</button>

				<?php if ($this->my->id && $activeFilter && $hasAccessCreateFilter) { ?>
				<a href="javascript:void(0);" class="btn btn-es-default-o t-lg-pull-right t-lg-mr--md" data-update-filter data-id="<?php echo $activeFilter->id;?>">
					<?php echo JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_UPDATE_FILTER'); ?>
				</a>
				<?php } ?>
			</div>

			<?php echo $this->html('form.token'); ?>
			<?php echo $this->html('form.itemid'); ?>
			<input type="hidden" name="option" value="com_easysocial" />
			<input type="hidden" name="view" value="search" />
			<input type="hidden" name="layout" value="advanced" />
			<input type="hidden" name="type" value="<?php echo $type;?>" />
		</form>

		<!-- Template for criteria -->
		<?php echo $criteriaTemplate; ?>
	</div>
</div>

<div class="t-lg-mt--md <?php echo !$results ? 'is-empty' : '';?>">
	<div class="es-search-result">
		<?php if ($results || $activeFilter) { ?>
		<div class="es-island t-lg-mb--xl">
			<div class="o-grid">
				<div class="o-grid__cell">
					<?php echo JText::sprintf('COM_EASYSOCIAL_ADVANCED_SEARCH_NUMBER_ITEM_FOUND', '<b>' . $total . '</b>'); ?>
				</div>

				<div class="o-grid__cell--auto-size">
					<?php if ($results && !$activeFilter) { ?>
						<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm" data-es-provide="tooltip" data-title="<?php echo JText::_('COM_EASYSOCIAL_EDIT_SEARCH_CRITERIA'); ?>" data-edit-criteria>
							<i class="far fa-edit"></i>
						</a>

						<?php if ($this->access->allowed('search.create.filter')) { ?>
						<a href="javascript:void(0);" class="btn btn-es-primary btn-sm" data-save-filter>
							<?php echo JText::_('COM_EASYSOCIAL_ADVANCED_SEARCH_CREATE_FILTER'); ?>
						</a>
						<?php } ?>
					<?php } ?>

					<?php if ($activeFilter && $activeFilter->canEdit()) { ?>
						<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm" data-edit-criteria><?php echo JText::_('COM_ES_EDIT'); ?></a>
					<?php } ?>
				</div>
			</div>
		</div>
		<?php } ?>

		<?php echo $this->render('module', 'es-advancedsearch-before-results'); ?>

		<?php echo $this->html('html.emptyBlock', 'COM_EASYSOCIAL_SEARCH_NO_RECORDS_FOUND', 'fa-search'); ?>

		<?php if ($results) { ?>
			<?php echo $this->includeTemplate('site/search/advanced/results', array( 'nextlimit' => $nextlimit, 'total' => $total, 'results' => $results, 'displayOptions' => $displayOptions ) ); ?>
		<?php } ?>
	</div>
</div>
