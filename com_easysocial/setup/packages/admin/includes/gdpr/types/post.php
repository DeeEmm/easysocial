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

class SocialGdprPost extends SocialGdprAbstract
{
	public $type = 'post';
	public $media = array('photos', 'videos', 'audios', 'files');

	/**
	 * Main function to process user post data for GDPR download.
	 *
	 * @since 5.2
	 * @access public
	 */
	public function execute(SocialGdprSection &$section)
	{
		$this->tab = $section->createTab($this);

		// Get a list of ids that are already processed
		$ids = $this->tab->getProcessedIds();

		$model = ES::model('stream');
		$items = $model->getActivityStreamGDPR($this->userId, $ids);

		if (!$items) {
			return $this->tab->finalize();
		}

		$config = ES::config();
		$dateSort = $config->get('stream.pagination.ordering');

		if ($items) {

			// lets format the data
			$lib = ES::stream();
			$streamOption = array('commentLink' => false, 'commentForm' => false, 'perspective' => 'dashboard', 'truncate' => false, 'isActivity' => false, 'overridePrivacy' => false);

			foreach ($items as $row) {

				$streamId = $row->uid;
				$activityId = $row->id;

				// reassign the ids so that stream formatter know how to process it.
				$row->id = $streamId;
				$data = $lib->format(array($row), 'all', null, false, 'onPrepareStream', $streamOption);

				// if the data is a boolean, this mean we cannot process the items.
				// lets just register these items so that the next cycle can continue.
				if (is_bool($data)) {

					// in raw data, the id is the stream_item.id
					$item = $this->getTemplate($activityId, $this->type);
					$this->tab->markItemProcessed($item);

					continue;
				}

				$streamItem = $data[0];
				$streamItem->activityId = $activityId;

				$item = $this->getTemplate($activityId, $this->type);

				// check if we need to skip this item or not.
				if ($this->skip($streamItem)) {
					$this->tab->markItemProcessed($item);
					continue;
				}

				$item->created =  $streamItem->$dateSort;
				$item->title = $this->getTitle($streamItem, $dateSort);
				$item->intro = $this->getIntro($streamItem, $dateSort);
				$item->source = false;

				$item->relation = $this->getRelation($streamItem);
				$item->view = false;
				$item->content = '';

				if ($item->relation) {
					$item->view = true;
					$item->content = '';
				}

				if (!$item->relation) {
					$tmpContent = $this->getContent($streamItem);

					if ($tmpContent) {
						$item->view = true;
						$item->content = $tmpContent;
					}
				}

				$this->tab->addItem($item);
			}

		}
	}

	/**
	 * Determine if we need to skip this stream item or not.
	 *
	 * @since 5.2
	 * @access public
	 */
	public function skip(SocialStreamItem $item)
	{
		// we need to skip some of the items
		if ($item->context == 'tasks' && $item->cluster_id) {
			return true;
		}

		return false;
	}

	/**
	 * Function to get activity title.
	 *
	 * @since 5.2
	 * @access public
	 */
	public function getIntro(SocialStreamItem $item, $dateSort)
	{
		$title = JText::sprintf('Activity stream #%1$s', $item->activityId);
		$date = ES::date($item->$dateSort);
		$relation = $this->getRelation($item);

		$intro = '';

		if ($relation && $item->content) {
			$intro = $item->content;
		}

		ob_start();
		?>
		<?php if ($intro) { ?>
		<div class="gdpr-item__desc">
			<?php echo $intro; ?>
		</div>
		<?php } ?>
		<div class="gdpr-item__meta">
			<?php echo $date->format($this->getDateFormat());?>
		</div>
		<div class="gdpr-item__label">
			<span class="gdpr-label"><?php echo strtoupper($item->context);?></span>
		</div>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}

	/**
	 * Function to get activity title.
	 *
	 * @since 5.2
	 * @access public
	 */
	public function getTitle(SocialStreamItem $item, $dateSort)
	{
		$title = JText::sprintf('COM_ES_GDPR_ACTIVITY_TITLE', $item->activityId);
		return $title;
	}


	/**
	 * Function to get activity title.
	 *
	 * @since 5.2
	 * @access public
	 */
	public function getContent(SocialStreamItem $item)
	{
		$tmpContent = $item->content;

		// handling links
		if ($item->context == 'links' ||$item->context == 'tasks') {
			$tmpContent .= $item->preview;
		}

		if (!$tmpContent) {
			$tmpContent = strip_tags($item->title);
		}

		return $tmpContent;
	}

	public function getRelation(SocialStreamItem $item)
	{
		$relations = array('photos' => 'photo',
							'videos' => 'video',
							'audios' => 'audio',
							'polls' => 'poll',
							'notes' => 'note',
							'news' => 'news',
							'files' => 'file',
							'discussions' => 'discussion');

		if (array_key_exists($item->context, $relations)) {
			$path = $relations[$item->context];

			if ($item->context == 'files') {
				$fileParams = ES::registry($item->contextParams[0]);
				$files = $fileParams->get('files', array());
				if ($files) {
					$item->contextId = $files[0];
				}
			}

			if ($item->contextId) {
				return $path . '/' . $item->contextId . '.html';
			}
		}

		return false;
	}
}
