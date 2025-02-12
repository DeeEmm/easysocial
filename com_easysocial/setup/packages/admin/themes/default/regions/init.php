<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="row" data-base>
    <div class="col-md-12">
        <div class="panel">
            <div class="panel-head">
                <b><?php echo JText::_('COM_EASYSOCIAL_REGIONS_INITIALISE_REGIONS'); ?></b>
            </div>

            <div class="panel-body">
                <p class="alert alert-warning"><?php echo JText::_('COM_EASYSOCIAL_REGIONS_INITIALISE_REGIONS_DESC'); ?></p>
                <p class="alert alert-danger"><?php echo JText::_('COM_EASYSOCIAL_REGIONS_INITIALISE_REGIONS_CONFIRM'); ?></p>
                <a href="javascript:void(0);" class="btn btn-success" data-start><?php echo JText::_('COM_EASYSOCIAL_REGIONS_INITIALISE_REGIONS_START'); ?></a>

                <table class="table table-striped table-noborder" style="display: none;" data-table>
                    <thead>
                        <tr>
                            <th><?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_TITLE'); ?></td>
                            <th width="10%" class="center"><?php echo JText::_('COM_EASYSOCIAL_TABLE_COLUMN_STATUS'); ?></td>
                        </tr>
                    </thead>
                    <tbody data-table-body>
                        <tr data-table-row data-key="clear" style="display: none;">
                            <td>
                                <?php echo JText::_('COM_EASYSOCIAL_REGIONS_CLEARING_DATABASE'); ?>
                            </td>
                            <td class="center">
                                <span class="label label-warning" data-row-status><i data-row-icon class="fa fa-wrench"></i></span>
                            </td>
                        </tr>
                        <tr data-table-row data-key="country" style="display: none;">
                            <td>
                                <?php echo JText::_('COM_EASYSOCIAL_REGIONS_INITIALISING_COUNTRIES'); ?>
                            </td>
                            <td class="center">
                                <span class="label label-warning" data-row-status><i data-row-icon class="fa fa-wrench"></i></span>
                            </td>
                        </tr>
                        <tr data-table-row data-key="state" style="display: none;">
                            <td>
                                <?php echo JText::_('COM_EASYSOCIAL_REGIONS_INITIALISING_STATES'); ?>
                            </td>
                            <td class="center">
                                <span class="label label-warning" data-row-status><i data-row-icon class="fa fa-wrench"></i></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </th>
        </div>
    </div>
</div>
