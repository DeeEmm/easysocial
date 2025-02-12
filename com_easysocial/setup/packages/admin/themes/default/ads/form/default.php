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
<form name="adminForm" id="adminForm" class="pointsForm" method="post" enctype="multipart/form-data" data-ad-form>
<div class="row">
	<div class="col-md-7">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_ADS_FORM_AD_CONTENT'); ?>

			<div class="panel-body">

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_ADS_FORM_ADVERTISER', true, '', 5, true); ?>

					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'advertiser_id', $ad->advertiser_id, ES::convertToDropdownOptions($advertisers, 'id', 'name', [
								['value' => 0, 'text' => 'COM_ES_ADS_SELECT_ADVERTISER']
						])); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_ADS_FORM_HEADLINE', true, '', 5, true); ?>

					<div class="col-md-7">
						<input type="text" class="o-form-control" value="<?php echo $this->html('string.escape', $ad->title);?>" name="title" id="title" />
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_ADS_COVER', true, '', 5, true); ?>

					<div class="col-md-7">
						<?php if ($ad->cover) { ?>
						<div class="mb-20">
							<div class="es-img-holder">
								<img src="<?php echo $ad->getCover();?>" width="256" />
							</div>
						</div>
						<?php } ?>
						<div style="clear:both;" class="t-lg-mb--xl">
							<input type="file" name="cover" id="cover" class="input" style="width:265px;" data-uniform />
						</div>
						<br />

						<div class="help-block">
							<?php echo JText::_('COM_ES_ADS_COVER_RATIO_NOTICE'); ?>
						</div>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_ADS_FORM_LINK'); ?>
					<div class="col-md-7">
						<input type="text" class="o-form-control" value="<?php echo $this->html('string.escape', $ad->link);?>" name="link" id="link" placeholder="<?php echo JText::_('COM_ES_ADS_FORM_WEBSITE_URL_PLACEHOLDER', true);?>" />
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_ADS_FORM_INTRO'); ?>
					<div class="col-md-7">
						<textarea name="intro" id="intro" class="o-form-control"><?php echo $this->html('string.escape', $ad->intro);?></textarea>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_ADS_FORM_CONTENT'); ?>
					<div class="col-md-7">
						<textarea name="content" class="o-form-control"><?php echo $this->html('string.escape', $ad->content);?></textarea>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_ADS_FORM_STATE'); ?>
					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'state', $ad->state); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_ADS_FORM_PRIORITY'); ?>
					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'priority', $ad->priority, array(
							array('value' => '1', 'text' => 'COM_ES_ADS_FORM_PRIORITY_LOW'),
							array('value' => '2', 'text' => 'COM_ES_ADS_FORM_PRIORITY_MED'),
							array('value' => '3', 'text' => 'COM_ES_ADS_FORM_PRIORITY_HIGH'),
							array('value' => '4', 'text' => 'COM_ES_ADS_FORM_PRIORITY_HIGHEST')
						), 'priority'); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_ADS_FORM_BUTTON_TYPE'); ?>
					<div class="col-md-7">
						<?php echo $this->html('grid.selectlist', 'button_type', $ad->button_type, array(
							array('value' => '0', 'text' => 'COM_ES_ADS_FORM_NO_BUTTON'),
							array('value' => '1', 'text' => 'COM_ES_ADS_FORM_BUTTON_LISTEN_NOW'),
							array('value' => '2', 'text' => 'COM_ES_ADS_FORM_BUTTON_SHOP_NOW'),
							array('value' => '3', 'text' => 'COM_ES_ADS_FORM_BUTTON_SIGN_UP'),
							array('value' => '4', 'text' => 'COM_ES_ADS_FORM_BUTTON_SUBSCRIBE'),
							array('value' => '5', 'text' => 'COM_ES_ADS_FORM_BUTTON_LEARN_MORE')
						), 'priority'); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_ADS_FORM_ENABLE_TIME_LIMIT'); ?>
					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'enable_limit', $showLimit, '', 'data-time-limit'); ?>
					</div>
				</div>

				<div class="form-group <?php echo $showLimit ? '' : 't-hidden'; ?>" data-start-date>
					<?php echo $this->html('panel.label', 'COM_ES_ADS_FORM_START_DATE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.calendar', 'start_date', $ad->start_date, 'start_date', '', true, 'YYYY-MM-DD HH:mm:ss'); ?>
					</div>
				</div>

				<div class="form-group <?php echo $showLimit ? '' : 't-hidden'; ?>" data-end-date>
					<?php echo $this->html('panel.label', 'COM_ES_ADS_FORM_END_DATE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.calendar', 'end_date', $ad->end_date, 'end_date', '', true, 'YYYY-MM-DD HH:mm:ss'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php echo $this->html('form.action', 'ads', ''); ?>
<input type="hidden" name="id" value="<?php echo $ad->id; ?>" />

<?php if ($ad->isUnderModeration()) { ?>
	<?php echo $this->html('form.hidden', 'cid[]', $ad->id); ?>
<?php } ?>

</form>
