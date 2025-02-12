<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="o-box" data-id="<?php echo $relation->id; ?>" data-rs-relations>
	<div class="o-flag">
		<div class="o-flag__image o-flag--top">
			<?php echo $this->html('avatar.user', $targetUser, 'sm'); ?>
		</div>

		<div class="o-flag__body">
			<a href="javascript:void(0);" class="btn btn-es-danger-o btn-xs t-lg-pull-right" data-rs-delete>
				<i class="fa fa-times"></i>
			</a>

			<div data-rs-sentence><?php echo $relation->getSentence();?></div>
		</div>
	</div>
</div>
<?php if (in_array($relation->type, array('single', 'widowed', 'separated', 'divorced'))) { ?>
	<input type="hidden" name="<?php echo $inputName; ?>[typeRelation]" value="<?php echo $relation->type; ?>">
<?php } else { ?>
	<input type="hidden" name="<?php echo $inputName; ?>[targetRelation][]" value="<?php echo $relation->getTargetUser()->id; ?>">
<?php } ?>
