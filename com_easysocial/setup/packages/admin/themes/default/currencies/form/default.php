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
<form name="adminForm" id="adminForm" class="pointsForm" method="post" enctype="multipart/form-data">
	<div class="row">
		<div class="col-md-7">
			<div class="panel">
				<?php echo $this->html('panel.heading', 'COM_ES_CURRENCIES_FORM_GENERAL'); ?>

				<div class="panel-body">

					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_ES_CURRENCIES_ID', true, '', 5, true); ?>

						<div class="col-md-7">
							<?php echo $this->html('form.text', 'id', 'id', $currency->id, ['placeholder' => 'usd', 'attr' => $currency->id ? 'data-id disabled="disabled"' : 'data-id']); ?>
							<div class="help-block">
								<?php if ($currency->id) { ?>
									<?php echo JText::_('COM_ES_CURRENCIES_ID_EXISTING_NOTICE') ?>
								<?php } else { ?>
									<?php echo JText::_('COM_ES_CURRENCIES_ID_NEW_NOTICE') ?>
								<?php } ?>
							</div>
						</div>
					</div>

					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_ES_CURRENCIES_TITLE'); ?>

						<div class="col-md-7">
							<?php echo $this->html('form.text', 'title', 'title', $currency->title, ['placeholder' => '($) Dollars']); ?>
						</div>
					</div>

					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_ES_CURRENCIES_SYMBOL'); ?>

						<div class="col-md-7">
							<?php echo $this->html('form.text', 'symbol', 'symbol', $currency->symbol, ['placeholder' => '$', 'class' => 'o-form-control input-mini t-text--center']); ?>
						</div>
					</div>

					<div class="form-group">
						<?php echo $this->html('panel.label', 'COM_ES_CURRENCIES_DECIMAL_SEPARATOR'); ?>

						<div class="col-md-7">
							<?php echo $this->html('grid.selectlist', 'separator', $currency->separator, array(
								array('value' => '.', 'text' => 'Dot (.)'),
								array('value' => ',', 'text' => 'Comma (,)')
						)); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php echo $this->html('form.action', 'currencies', ''); ?>
</form>
