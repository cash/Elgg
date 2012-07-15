<?php
/**
 * Notification class that notification deliverers receive and process
 * 
 * @since 1.9
 */
class ElggNotification {

	/** @var ElggEntity The entity causing or creating the notification */
	protected $from;

	/** @var ElggUser The user receiving the notification */
	protected $to;

	/** @var string The subject string */
	public $subject;

	/** @var string The body string */
	public $body;

	/** @var array Additional parameters */
	public $params;

	/**
	 * Create a notification
	 *
	 * @param ElggEntity $from
	 * @param ElggEntity $to
	 * @param string     $subject
	 * @param string     $body
	 * @param array      $params
	 */
	public function __construct($from, $to, $subject, $body, array $params = array()) {
		$this->from = $from;
		$this->to = $to;
		$this->subject = $subject;
		$this->body = $body;
		$this->params = $params;
	}

	/**
	 * Get the sender entity
	 *
	 * @return ElggEntity
	 */
	public function getSender() {
		return $this->from;
	}

	/**
	 * Get the recipient entity
	 *
	 * @return ElggEntity
	 */
	public function getRecipient() {
		return $this->to;
	}

	/**
	 * Get the formatted address for sender
	 *
	 * @return string
	 */
	public function getSenderFormattedEmailAddress() {
		return $this->getEmailAddress($from);
	}

	/**
	 * Get the formatted address for recipient
	 *
	 * @return string
	 */
	public function getRecipientFormattedEmailAddress() {
		return $this->getEmailAddress($to);
	}

	protected function getFormattedEmailAddress($entity) {
		// need to remove special characters
		$name = $entity->name;
		$email = $entity->email;
		return "$name <$email>";
	}
}
