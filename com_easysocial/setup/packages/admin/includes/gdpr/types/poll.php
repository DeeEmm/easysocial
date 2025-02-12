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

class SocialGdprPoll extends SocialGdprAbstract
{
	public $type = 'poll';

	/**
	 * Process user poll downloads in accordance to GDPR rules
	 *
	 * @since  2.2
	 * @access public
	 */
	public function execute(SocialGdprSection &$section)
	{
		$this->tab = $section->createTab($this);

		$polls = $this->getPolls();

		// Nothing else to process, finalize it now.
		if (!$polls) {
			return $this->tab->finalize();
		}

		foreach ($polls as $poll) {
			$item = $this->getTemplate($poll->id, $this->type);

			$item->created = $poll->created;
			$item->title = $poll->title;
			$item->intro = $this->getIntro($poll);
			$item->view = true;
			$item->content = $this->getContent($poll);

			$this->tab->addItem($item);
		}
	}

	/**
	 * Display the intro content on the first page 
	 *
	 * @since  2.2
	 * @access public
	 */
	public function getIntro($poll)
	{
		$date = ES::date($poll->created);
		ob_start();
		?>
		<div class="gdpr-item__meta">
			<?php echo $date->format($this->getDateFormat());?>
		</div>
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
	public function getContent($poll)
	{
		$model = ES::model('Polls');
		$date = ES::date($poll->created);
		$contents = '';

		// retrieve the polls options valkue
		$options = $model->getItems($poll->id);

		if (!$options) {
			return $contents;
		}

		foreach ($options as $option) {
			$contents .= $option->value . ' - ' . $option->count . ' ' . JText::_('COM_EASYSOCIAL_POLLS_VOTES_COUNT') . '<br />';
		}

		ob_start();
		?>
		<div class="gdpr-item__desc">
			<?php echo $contents;?>
		</div>
		<div class="gdpr-item__meta">
			<?php echo $date->format($this->getDateFormat());?>
		</div>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}

	/**
	 * Retrieves user polls that needs to be processed
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getPolls()
	{
		$ids = $this->tab->getProcessedIds();

		$options = array();
		$options['userid'] = $this->user->id;
		$options['exclusion'] = $ids;
		$options['limit'] = $this->getLimit(20);

		$model = ES::model('Polls');
		$polls = $model->getPollsGDPR($options);

		return $polls;
	}
}
