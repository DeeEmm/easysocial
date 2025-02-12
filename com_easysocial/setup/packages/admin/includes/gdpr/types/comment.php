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

class SocialGdprComment extends SocialGdprAbstract
{
	public $type = 'comment';

	/**
	 * Main function to process user comment data for GDPR download.
	 *
	 * @since 2.1
	 * @access public
	 */
	public function execute(SocialGdprSection &$section)
	{
		$this->tab = $section->createTab($this);

		$comments = $this->getItems();

		if (!$comments) {
			return $this->tab->finalize();
		}

		foreach ($comments as $comment) {
			$item = $this->getTemplate($comment->id, $this->type);
			$item->title = $this->getTitle($comment);
			$item->preview = $this->getIntro($comment);
			$item->intro = $item->preview;
			$item->created = $comment->created;

			$this->tab->addItem($item);
		}
	}

	public function getItems()
	{
		// Get a list of ids that are already processed
		$ids = $this->tab->getProcessedIds();

		// data from db.
		$model = ES::model('comments');
		$options = array();
		$options['exclusion'] = $ids;
		$comments = $model->getCommentGDPR($this->user->id, $options);

		return $comments;
	}

	public function getTitle($comment)
	{
		$actor = ES::user($comment->actor_id);
		$actor = $actor->getName();

		if ($comment->actor_id == $this->user->id) {
			// You're the commentor.
			$actor = $this->user->getGenderTerm();
		}

		$title = JText::sprintf('COM_ES_GDPR_COMMENTED_ON', $this->user->getName(), $actor, $comment->type, '');

		if (isset($comment->actor) && $comment->actor) {
			$title = JText::sprintf('COM_ES_GDPR_COMMENTED_ON', $this->user->getName(), $actor, $comment->type, $comment->actor);
		}

		if (isset($comment->cluster_id) && $comment->cluster_id) {
			$cluster = ES::cluster($comment->cluster_id);
			$clusterTitle = $cluster->getTitle();

			$title = JText::sprintf('COM_ES_GDPR_COMMENTED_ON_CLUSTER', $this->user->getName(), $actor, $comment->type, $clusterTitle);
		}

		$title = strip_tags($title);

		return $title;
	}

	public function getIntro($comment) 
	{
		$date = ES::date($comment->created);

		ob_start();
		?>
		<div class="item">
			<div class="item__comment"><?php echo $comment->comment; ?></div>
			<div class="item__date"><?php echo $date->format($this->getDateFormat()); ?></div>
		</div>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}
}
