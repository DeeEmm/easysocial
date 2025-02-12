<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div id="es" class="mod-es mod-es-pages <?php echo $lib->getSuffix();?>">
<form method="get" action="<?php echo JRoute::_('index.php'); ?>" name="frmSearch" class="mod-es-page-search-form <?php echo $lib->isMobile() ? 'is-mobile' : '';?>">

	<div class="t-fs--sm t-lg-pb--lg">
		<?php echo JText::_('MOD_EASYSOCIAL_PAGE_SEARCH_TITLE_DESCRIPTION'); ?>
	</div>

	<div class="o-form-group">
		<label class="o-control-label" for="pagecategory"><?php echo JText::_('MOD_EASYSOCIAL_PAGE_SEARCH_CATEGORY_LABEL'); ?></label>
		<div class="o-control-input">
			<div class="">
				<div class="o-grid">
					<div class="o-grid__cell">
						<div class="o-select-group">
							<select name="pagecategory" id="pagecategory" data-page-category class="o-form-control">
							<?php if (count($categories) == 1) { ?>
								<option value="<?php echo $categories[0]->id; ?>"><?php echo JText::_($categories[0]->title); ?></option>
							<?php } else { ?>
								<option value="<?php echo $allCategoryIds; ?>"<?php echo ($pagecategory == $allCategoryIds) ? ' selected="selected"' : ''; ?>><?php echo JText::_('MOD_EASYSOCIAL_PAGE_SEARCH_ALL_OPTION'); ?></option>
								<?php foreach ($categories as $cat) { ?>
									<option value="<?php echo $cat->id; ?>"<?php echo ($pagecategory == $cat->id) ? ' selected="selected"' : ''; ?>><?php echo JText::_($cat->title); ?></option>
								<?php } ?>
							<?php } ?>
							</select>
							<label for="pagecategory" class="o-select-group__drop"></label>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php if ($params->get('display_page_author', true)) { ?>
	<div class="o-form-group">
		<label class="o-control-label" for="pagecreator"><?php echo JText::_('MOD_EASYSOCIAL_PAGE_SEARCH_AUTHOR_LABEL'); ?></label>
		<div class="o-control-input">
			<div class="">
				<div class="o-grid">
					<div class="o-grid__cell">
						<div class="o-select-group">
							<select name="pagecreator" id="pagecreator" data-page-author class="o-form-control">
								<?php if (count($authors) == 1) { ?>
									<option value="<?php echo $authors[0]->id; ?>"<?php echo ($pagecreator == $authors[0]->id) ? ' selected="selected"' : ''; ?>><?php echo $authors[0]->name; ?></option>
								<?php } else { ?>
									<option value="<?php echo $allAuthorsIds; ?>"<?php echo ($pagecreator == $allAuthorsIds) ? ' selected="selected"' : ''; ?>><?php echo JText::_('MOD_EASYSOCIAL_PAGE_SEARCH_ALL_OPTION'); ?></option>
									<?php foreach ($authors as $author) { ?>
										<option value="<?php echo $author->id; ?>"<?php echo ($pagecreator == $author->id) ? ' selected="selected"' : ''; ?>><?php echo $author->name; ?></option>
									<?php } ?>
								<?php } ?>
							</select>
							<label for="pagecreator" class="o-select-group__drop"></label>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>

	<div class="o-form-group">
		<label class="o-control-label" for=""><?php echo JText::_('MOD_EASYSOCIAL_PAGE_SEARCH_DAY_LABEL'); ?></label>
		<div class="o-control-input">
			<div class="">
				<div class="es-form-working-hour" data-days-container>

					<div class="es-form-working-hour__day" data-day-wrapper>

						<div class="o-select-group">
							<select name="day[]" data-day multiple="multiple">
								<option value="all"<?php echo (in_array('all', $data->day)) ? ' selected="selected"' : ''; ?>><?php echo JText::_('MOD_EASYSOCIAL_PAGE_SEARCH_ALL_OPTION'); ?></option>
								<?php foreach ($days as $day) { ?>
									<option value="<?php echo $day->id; ?>"<?php echo (in_array($day->id, $data->day)) ? ' selected="selected"' : ''; ?>><?php echo $day->title; ?></option>
								<?php } ?>
							</select>
							<label for="" class="o-select-group__drop"></label>
						</div>

						<div class="es-form-working-hour__grid es-form-working-hour__grid--justify t-lg-mt--sm" hour-startend-wrapper>
							<div class="es-form-working-hour__cell">
									<div class="o-select-group">
										<select class="o-form-control" name="hourstart" data-start>
											<?php foreach ($hours as $hour) { ?>
												<option value="<?php echo $hour->value; ?>"<?php echo ($data->start == $hour->value) ? ' selected="selected"' : ''; ?>><?php echo $hour->title; ?></option>
											<?php } ?>
										</select>
										<label for="" class="o-select-group__drop"></label>
									</div>
							</div>
							<div class="es-form-working-hour__cell es-form-working-hour__cell--divider">
								&#8211;
							</div>
							<div class="es-form-working-hour__cell">
									<div class="o-select-group">
										<select class="o-form-control" name="hourend" data-end>
											<?php foreach ($hours as $hour) { ?>
												<option value="<?php echo $hour->value; ?>"<?php echo ($data->end == $hour->value) ? ' selected="selected"' : ''; ?>><?php echo $hour->title; ?></option>
											<?php } ?>
										</select>
										<label for="" class="o-select-group__drop"></label>
									</div>
							</div>

						</div>



					</div>

				</div>
			</div>
		</div>
	</div>

	<button class="btn btn-es-primary btn-block t-lg-mt--lg" type="submit" data-pagesearch-button>
		<?php echo JText::_('MOD_EASYSOCIAL_PAGE_SEARCH_BUTTON_SEARCH');?>
	</button>

	<div class="alert alert-warning t-lg-mt--sm t-fs--sm t-hidden" data-pagesearch-notice></div>

	<?php echo $lib->html('form.token'); ?>
	<?php echo $lib->html('form.itemid'); ?>
	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="view" value="pages" />
	<input type="hidden" name="layout" value="search" />
	<input type="hidden" name="limitstart" value="0" />
	<input type="hidden" name="ordering" value="<?php echo $ordering; ?>" />
</form>
	<div style="display:none;" data-MOD_EASYSOCIAL_PAGE_SEARCH_NOTICE_INVALID_TIME><?php echo JText::_('MOD_EASYSOCIAL_PAGE_SEARCH_NOTICE_INVALID_TIME');?></div>
</div>
