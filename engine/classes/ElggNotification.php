<?php
/**
 * Notification class that notification deliverers receive and process
 * 
 * @since 1.9
 */
class ElggNotification {
	protected $from;
	protected $to;

	/**
	 * Create a notification
	 *
	 * @param ElggEntity $from
	 * @param ElggEntity $to
	 * @param string     $subject
	 * @param string     $body
	 * @param array      $params
	 */
	public function __construct($from, $to, $subject, $body, $params) {
		
	}
}
