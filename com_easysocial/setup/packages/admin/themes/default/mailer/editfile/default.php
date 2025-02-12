<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<form action="index.php" method="post" name="adminForm" id="adminForm" data-ed-form>
    <div class="row">
        <div class="col-md-5">
            <div class="panel">
                <?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_EMAILS_EDITOR_FILE_INFO'); ?>

                <div class="panel-body">
                    <div class="form-group">
                        <?php echo $this->html('panel.label', 'COM_EASYSOCIAL_EMAILS_EDITOR_FILE_LOCATION'); ?>

                        <div class="col-md-7">
                            <?php echo $this->html('grid.inputbox', 'filepath', $file->path, 'filepath'); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <?php echo $this->html('panel.label', 'COM_EASYSOCIAL_EMAILS_EDITOR_OVERRIDE_FILE_LOCATION'); ?>

                        <div class="col-md-7">
                            <?php echo $this->html('grid.inputbox', 'overridepath', $file->overridePath, 'overridepath'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="panel">
                <?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_EMAILS_EDITOR_GENERAL'); ?>

                <div class="panel-body">
                    <div class="form-group">
                        <?php echo $editor->display('source', $file->contents, '100%', '400px', 80, 20, false, null, null, null, array('syntax' => 'php', 'filter' => 'raw')); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" name="file" value="<?php echo base64_encode($file->relative);?>" />
    <?php echo $this->html('form.action', 'mailer', 'mailer'); ?>
</form>