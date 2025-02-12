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

class SocialTableFriendInvite extends SocialTable
{
	public $id = null;
	public $user_id	= null;
	public $email = null;
	public $created = null;
	public $message = null;
	public $registered_id = null;
	public $utype = null;
	public $uid = null;

	public function __construct($db)
	{
		parent::__construct('#__social_friends_invitations', 'id', $db);
	}

	/**
	 * Automatically add the inviter and the target as friends
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function makeFriends()
	{
		if (!$this->user_id || !$this->registered_id) {
			return false;
		}

		$invites = ES::invites();
		return $invites->registered($this->user_id, $this->registered_id);
	}

	/**
	 * Overrides the parent's implementation of store
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function store($pk = null)
	{
		$isNew = !$this->id;

		// Save this into the table first
		parent::store($pk);

		// Add this into the mail queue
		if ($isNew) {
			$this->send($isNew);
		}
	}

	/**
	 * Send friend invitation
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function send($isNew = false)
	{
		$jconfig = ES::jconfig();
		$mailer = ES::mailer();
		$template = $mailer->getTemplate();

		$joinName = $jconfig->getValue('sitename');
		$mailTemplate = 'site/friends/invite';

		if ($this->isCluster()) {
			$cluster = ES::cluster($this->uid);
			$joinName = $cluster->getTitle();
			$mailTemplate = 'site/clusters/invite';
		}

		$sender = ES::user($this->user_id);

		$params = new stdClass;
		$params->senderName = $sender->getName();
		$params->message = $this->message;
		$params->utype = $this->utype;
		$params->siteName = $joinName;
		$params->manageAlerts = false;
		$params->link = ESR::registration(['invite' => $this->id, 'external' => true]);

		// it seems like some mail server disallow to change the sender name and reply to. we will commment out this for now.
		// $template->setSender($sender->getName(), $sender->email);
		// $template->setReplyTo($sender->email);

		$template->setRecipient('', $this->email);
		$template->setTitle(JText::sprintf('COM_EASYSOCIAL_FRIENDS_INVITE_MAIL_SUBJECT', $joinName));
		$template->setTemplate($mailTemplate, $params);

		$mailer->create($template);

		if ($isNew && !$this->isCluster()) {
			// Assign points to the user that created this invite
			$points = ES::points();
			$points->assign('friends.invite', 'com_easysocial' , $this->user_id);
		} else {
			// Once the invitation resent, we need to update the date
			$this->created = JFactory::getDate()->toSql();
			parent::store();
		}
	}

	/**
	 * Determine if this is cluster type invitation
	 *
	 * @since   3.0
	 * @access  public
	 */
	public function isCluster()
	{
		return in_array($this->utype, array(SOCIAL_TYPE_GROUP, SOCIAL_TYPE_PAGE, SOCIAL_TYPE_EVENT));
	}

	/**
	 * Determine if this is a valid invitation
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function isValidInvitation($email)
	{
		return ($this->email == $email) ? true : false;
	}

}
