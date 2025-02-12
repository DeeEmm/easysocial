<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialGdprProfile extends SocialGdprAbstract
{
	public $type = 'profile';
	public $tab = null;

	/**
	 * Process user profile data downloads in accordance to GDPR rules
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function execute(SocialGdprSection &$section)
	{
		$this->tab = $section->createTab($this);

		$profileDataSteps = $this->getProfileDataSteps();

		// Nothing else to process, finalize it now.
		if (!$profileDataSteps) {
			return $this->tab->finalize();
		}

		foreach ($profileDataSteps as $step) {
			$item = $this->getTemplate($step->id, $this->type);

			$item->view = false;
			$item->title = '';
			$item->created = $step->created;
			$item->intro = $this->getIntro($step);

			$this->tab->addItem($item);
		}
	}

	/**
	 * Display each of the item title on the first page 
	 *
	 * @since  2.2
	 * @access public
	 */
	public function getTitle($profile)
	{
	}

	/**
	 * Display the intro content on the first page 
	 *
	 * @since  2.2
	 * @access public
	 */
	public function getIntro($step)
	{
		ob_start();
		?>

			<?php if (!empty($step->fields)) { ?>
				<table class="gdpr-table" style="width:520px;">
					<thead>
					   <th colspan="2" style="float:left;">
							<?php echo $step->_('title');?>
					   </th>
					</thead>
					<tbody>
					<?php foreach ($step->fields as $field) { ?>
						<tr>
							<?php if (!empty($field->data)) { ?>
								<td width="180"><?php echo JText::_($field->title) . ' : ';?></td>

								<?php if ($field->data) {

									// In case some other 3rd party field doesn't have follow the standard
									// we need to do some extra checking here
									if (is_array($field->data)) {
										$field->data = implode(' ', $field->data);
									}
								?>
								
							 	<td style="text-align:left;"><?php echo $field->data; ?></td>
								<?php } ?>
							<?php } ?>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			<?php } else { ?>
				<table class="gdpr-table" style="width:520px;">
					<thead>
					   <th colspan="2" style="float:left;">
							<?php echo $step->_('title');?>
					   </th>
					</thead>
					<tbody>
						<tr>
							<td colspan="2">
								<?php echo JText::_('COM_ES_ABOUT_NO_DETAILS_HERE');?>
							</td>
						</tr>
					</tbody>
				</table>
			<?php } ?>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}

	/**
	 * Display the content on the sub page 
	 *
	 * @since  2.2
	 * @access public
	 */
	public function getContent($profile)
	{
	}

	/**
	 * Retrieves user profile custom field data that needs to be processed
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getProfileDataSteps()
	{
		$ids = $this->tab->getProcessedIds();

		$options = array();
		$options['userid'] = $this->user->id;
		$options['exclusion'] = $ids;
		$options['limit'] = $this->getLimit();

		$usersModel = ES::model('Users');
		$steps = $usersModel->getProfileDataGDPR($this->user, $options);

		return $steps;
	}
}	