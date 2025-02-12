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
<div class="es-container" data-es-container data-es-tasks>

	<?php echo $this->html('html.sidebar'); ?>

	<?php if ($this->isMobile()) { ?>
		<?php echo $this->output('site/tasks/default/mobile.filters'); ?>
	<?php } ?>

	<div class="es-content">
		<div class="app-tasks">
			<div class="app-contents<?php echo !$tasks ? ' is-empty' : '';?>" data-app-contents>
				<div class="app-contents-data">
					<div class="form-item t-hidden" data-form>
						<div class="o-form-group es-island">
							<div class="o-input-group">
								<input type="text" class="o-form-control" value="" placeholder="<?php echo JText::_('APP_USER_TASKS_PLACEHOLDER', true);?>" data-form-title />

								<span class="o-input-group__btn">
									<a href="javascript:void(0);" class="btn btn-es-default-o" data-form-save>
										<i class="fa fa-check"></i>
									</a>

									<a href="javascript:void(0);" class="btn btn-es-danger-o" data-form-cancel>
										<i class="fa fa-times"></i>
									</a>
								</span>
							</div>
						</div>
					</div>

					<div data-lists>
						<?php if ($tasks) { ?>
							<?php foreach ($tasks as $task) { ?>
								<?php echo $this->loadTemplate('themes:/apps/user/tasks/profile/item', array('task' => $task, 'user' => $user)); ?>
							<?php } ?>
						<?php } ?>
					</div>
				</div>

				<?php echo $this->html('html.emptyBlock', 'APP_USER_TASKS_NO_TASKS_YET', 'fa-checkbox'); ?>
			</div>
		</div>
	</div>
</div>

<script>

EasySocial.require()
.script('site/apps/tasks/filter')
.done(function($) {
	$('body').implement(EasySocial.Controller.Apps.Tasks);
});

</script>
