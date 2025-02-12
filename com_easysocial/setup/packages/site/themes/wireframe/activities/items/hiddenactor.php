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
<?php if ($items){ ?>
	<?php foreach ($items as $actor) { ?>
		<li class="es-island" data-id="<?php echo $actor->id;?>" data-actor="<?php echo $actor->actor_id;?>" data-hidden-actor-item>
			<div class="o-grid">
				<div class="o-grid__cell" data-hidden-actor-content>
					<?php echo JText::sprintf('COM_EASYSOCIAL_ACTIVITY_HIDDEN_ACTORS_NOTICE', ES::user($actor->actor_id)->getName()); ?>
				</div>

				<div class="o-grid__cell--auto-size">						
					<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm" data-hidden-actor-unhide><?php echo JText::_('COM_ES_UNHIDE'); ?></a>
				</div>
			</div>
		</li>
	<?php } ?>
<?php } ?>
