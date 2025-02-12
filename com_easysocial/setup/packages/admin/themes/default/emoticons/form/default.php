<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
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
	<div class="col-md-7">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_EMOTICONS_FORM_GENERAL'); ?>

			<div class="panel-body">

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_EMOTICONS_KEYWORD', true, '', 5, true); ?>

					<div class="col-md-7">
						<input type="text" class="o-form-control" value="<?php echo $emoticon->title;?>" name="title" id="title" placeholder="<?php echo JText::_('COM_ES_EMOTICONS_KEYWORD', true);?>" />
					</div>
				</div>

				<?php if (ES::db()->hasUTF8mb4Support()) { ?>
					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_ES_EMOTICONS_FORM_TYPE'); ?>

						<div class="col-md-7">
							<?php echo $this->html('grid.selectlist', 'type', $emoticon->type, array(
									array('value' => 'image', 'text' => 'COM_ES_EMOTICON_TYPE_IMAGE'),
									array('value' => 'unicode', 'text' => 'COM_ES_EMOTICON_TYPE_EMOJI')
								), '', 'data-emoticon-type'); ?>
						</div>
					</div>
				<?php } ?>
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_EMOTICON_IMAGE', true, '', 5, true); ?>

					<div class="col-md-7">
						<div class="es-backend-emoji-form">
							
							<div class="t-lg-mb--md">
								<div class="es-emoji-img-holder">
									<div class="es-emoji-size">
										<i class="fa fa-image <?php echo $emoticon->icon ? 't-hidden' : '';?>"></i>
										<?php if ($emoticon->icon) { ?>
											<?php echo $emoticon->getIcon(); ?>
										<?php } ?>
									</div>
								</div>
							</div>

							<div class="o-grid-sm o-grid-sm--center">
								<div class="o-grid-sm__cell o-grid-sm__cell--auto-size <?php echo $emoticon->type != 'unicode' ? '' : 't-hidden'; ?>" data-image-uploader>
									<input type="file" name="image" id="image" class="input" style="width:265px;" data-image-input />
								</div>

								<?php if (ES::db()->hasUTF8mb4Support()) { ?>
									<div class="o-grid-sm__cell <?php echo $emoticon->type == 'unicode' ? '' : 't-hidden'; ?>" data-emoji-browser>
										<div class="o-input-group o-input-group--sm">
											<span class="o-input-group__addon" id="basic-addon1">
												<i data-emoji-empty class="far fa-smile es-emoji-size"></i>
												<div data-preview-emoji class="es-emoji-size">
													
												</div>
											</span>
											<span class="o-input-group__btn">
												<a href="javascript:void(0);" class="t-lg-pull-right btn btn-sm btn-es-default-o" data-insert-emoji><?php echo JText::_('COM_ES_SELECT_EMOJI');?></a>
											</span>
										</div>
									</div>
								<?php } ?>
							</div>
						</div>
						
						<input data-emoji-input type="hidden" name="emoji" id="emoji" class="input" style="width:265px;"/>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_EMOTICONS_FORM_STATE'); ?>
					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'state', $emoticon->state); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php echo $this->html('form.action', 'emoticons', ''); ?>
<input type="hidden" name="id" value="<?php echo $emoticon->id; ?>" />
</form>
