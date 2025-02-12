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
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<div data-field-joomla-twofactor>
    <?php echo $this->html('grid.boolean', $inputName, $value, $inputName); ?>

    <div class="mb-20 mt-10<?php echo !$value ? ' hide' : '';?>" data-auth-selection>
        <?php if ($methods) { ?>
        <select name="twofactor_method" class="o-form-control input-sm" data-auth-selector>
            <option value=""><?php echo JText::_('PLG_EASYSOCIAL_FIELDS_TWOFACTOR_SELECT_AUTHENTICATION_METHOD');?>

            <?php foreach ($methods as $method) { ?>
            <option value="<?php echo $method->method;?>"<?php echo $user->getOtpConfig()->method == $method->method ? ' selected="selected"' : '';?>><?php echo $method->title;?></option>
            <?php } ?>
        </select>
        <?php } ?>
    </div>

    <div class="<?php echo $user->getOtpConfig()->method != 'none' ? '' : 'hide';?>" data-auth-methods>
        <?php foreach ($methods as $method) { ?>
        <div class="method-<?php echo $method->method;?> <?php echo $user->getOtpConfig()->method != $method->method ? 'hide' : '';?>" data-auth-method="<?php echo $method->method;?>">
            <?php echo $method->form;?>
        </div>
        <?php } ?>

        <fieldset>
            <legend><?php echo JText::_('COM_USERS_USER_OTEPS') ?></legend>

            <div class="alert alert-info"><?php echo JText::_('COM_USERS_USER_OTEPS_DESC') ?></div>

            <?php if ($user->getOtpConfig()->method) { ?>
            <div class="clearfix">
                <?php foreach ($user->getOtpConfig()->otep as $otep) { ?>
                <span class="col-sm-4">
                    <?php echo substr($otep, 0, 4) ?>-<?php echo substr($otep, 4, 4) ?>-<?php echo substr($otep, 8, 4) ?>-<?php echo substr($otep, 12, 4) ?>
                </span>
                <?php } ?>
            </div>
            <?php } ?>
        </fieldset>
    </div>

<div class="es-fields-error-note" data-field-error></div>
</div>
