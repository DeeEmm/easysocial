<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
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
		<form action="<?php echo JRoute::_('index.php');?>" method="post" class="es-forms" data-es-review-form>
			<div class="es-forms__group">
				<div class="es-forms__content">
					<div class="o-form-group" data-es-review-score>
						<?php echo $this->html('form.label', 'APP_REVIEWS_RATINGS_FIELD', 3, false); ?>

						<div class="o-control-input">
							<div class="es-rating o-control-input" data-es-ratings-stars data-score="<?php echo $reviews->value; ?>" data-id="<?php echo $cluster->id; ?>" ></div>
						</div>
					</div>
					<div class="o-form-group" data-es-review-title>
						<?php echo $this->html('form.label', 'APP_REVIEWS_TITLE_FIELD', 3, false); ?>

						<div class="o-control-input">
							<input type="text" id="title" value="<?php echo $this->html('string.escape', $reviews->title);?>" placeholder="<?php echo JText::_('APP_REVIEWS_TITLE_PLACEHOLDER'); ?>" name="title" class="o-form-control">
						</div>
					</div>
					<div class="o-form-group" data-es-review-message>
						<?php echo $this->html('form.label', 'APP_REVIEWS_REVIEW_FIELD', 3, false); ?>

						<div class="o-control-input">
							<textarea placeholder="<?php echo JText::_('APP_REVIEWS_CONTENT_PLACEHOLDER_' . $cluster->getType()); ?>" name="message" class="o-form-control" rows="5" id="message"><?php echo $this->html('string.escape', $reviews->message);?></textarea>
						</div>

					</div>
				</div>
			</div>

			<div class="es-forms__actions">
				<div class="o-form-actions">
					<a class="btn btn-es-default-o pull-left" href="<?php echo $appPermalink;?>"><?php echo JText::_('COM_ES_CANCEL'); ?></a>
					<button type="button" data-reviews-save-button class="btn btn-es-primary-o pull-right"><?php echo $reviews->id ? JText::_('APP_REVIEWS_SAVE_REVIEWS') : JText::_('APP_REVIEWS_SUBMIT_REVIEWS');?></button>
				</div>
			</div>

			<?php echo $this->html('form.action', 'reviews', 'saveReview'); ?>

			<input type="hidden" name="uid" value="<?php echo $cluster->id; ?>" />
			<input type="hidden" name="type" value="<?php echo $cluster->getType(); ?>" />
			<input type="hidden" name="id" value="<?php echo $reviews->id; ?>" />
			<input type="hidden" name="return" value="<?php echo $returnUrl;?>" />
		</form>
	</div>
</div>
