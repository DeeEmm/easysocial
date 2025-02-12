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

class SocialGdprReaction extends SocialGdprAbstract
{
	public $type = 'reaction';

	/**
	 * Process user reaction downloads in accordance to GDPR rules
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function execute(SocialGdprSection &$section)
	{
		$this->tab = $section->createTab($this);

		$reactions = $this->getReactions();

		// Nothing else to process, finalize it now.
		if (!$reactions) {
			return $this->tab->finalize();
		}

		foreach ($reactions as $reaction) {
			$item = $this->getTemplate($reaction->id, $this->type);

			$item->view = false;
			$item->created = $reaction->created;
			$item->title = $this->getTitle($reaction);
			$item->intro = $this->getIntro($reaction);

			$this->tab->addItem($item);
		}
	}

	/**
	 * Display each of the item title on the first page 
	 *
	 * @since  2.2
	 * @access public
	 */
	public function getTitle($reaction)
	{
		// This person who created the stream on the site
		$model = ES::model('stream');
		$target = $model->getStreamActor($reaction->stream_id);

		// This person who reaction the post on the site
		$actor = ES::user($reaction->created_by);
		$content = '';

		// User reacted to their own stream item
		if ($target->id == $actor->id) {
			$content = JText::sprintf('COM_ES_GDPR_USER_REACTED_TO_HIS_OWN', $actor->getName());
		}

		// User reacted to another person's stream item
		if ($target->id != $actor->id) {
			$content = JText::sprintf('COM_ES_GDPR_USER_REACTED_TO', $actor->getName(), $target->getName());
		}

		return $content;

	}

	/**
	 * Display the intro content on the first page 
	 *
	 * @since  2.2
	 * @access public
	 */
	public function getIntro($reaction)
	{
		$date = ES::date($reaction->created);

		ob_start();
		?>
		<div class="gdpr-item__meta">
			<?php echo $date->format($this->getDateFormat());?>
		</div>
		<div class="gdpr-item__label">
			<span class="gdpr-label">
				<?php echo ESJString::strtoupper($reaction->reaction); ?>
			</span>
		</div>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}
	
	/**
	 * Retrieves user reactions that needs to be processed
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getReactions()
	{
		$ids = $this->tab->getProcessedIds();

		$options = array();
		$options['userid'] = $this->user->id;
		$options['exclusion'] = $ids;
		$options['limit'] = $this->getLimit();

		$model = ES::model('likes');
		$reactions = $model->getReactionsGDPR($options);

		return $reactions;
	}	
}
