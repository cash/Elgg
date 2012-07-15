<?php
/**
 * Manage the notification queue, retrieves subscriptions,
 * creates the notification messages, and sends them out.
 *
 * @since 1.9
 */
class ElggNotificationManager {

	const QUEUE_NAME = 'notifications';

	/**
	 * Pull notification events from queue until stop time is reached
	 *
	 * @param int $stopTime The Unix time to stop sending notifications
	 * @return int The number of notification events handled
	 */
	public function run($stopTime) {

		$count = 0;
		$queue = new ElggDatabaseQueue(ElggNotificationManager::QUEUE_NAME);

		// @todo grab mutex

		while (time() < $stopTime) {
			// dequeue notification event
			$event = $queue->dequeue();
			if (!$event) {
				break;
			}

			$subscriptions = $this->getSubscriptions($event);

			// return false to stop the default notification sender
			$params = array('event' => $event, 'subscriptions' => $subscriptions);
			if (elgg_trigger_plugin_hook('send:before', 'notifications', $params, true)) {
				$this->sendNotifications($event, $subscriptions);
			}
			elgg_trigger_plugin_hook('send:after', 'notifications', $params);
			$count++;
		}

		// release mutex

		return $count;
	}

	/**
	 * Adds notification event to the queue
	 *
	 * @param ElggNotificationEvent $event Notification event
	 * @return bool
	 */
	public function enqueueNotificationEvent($event) {
		$queue = new ElggDatabaseQueue(ElggNotificationManager::QUEUE_NAME);
		return $queue->enqueue($event);
	}

	/**
	 * Get the subscriptions for this notification event
	 *
	 * The return array is of the form:
	 *
	 * array(
	 *     <user guid> => array('email', 'sms', 'ajax'),
	 * );
	 *
	 * @param ElggNotificationEvent $event Notification event
	 * @return array
	 */
	protected function getSubscriptions($event) {
		// @todo should we inject the subscription manager in constructor to support different managers?
		$manager = new ElggSubscriptionManager();
		return $manager->getSubscriptions($event);
	}

	/**
	 * Sends the notifications based on subscriptions
	 *
	 * @param ElggNotificationEvent $event         Notification event
	 * @param array                 $subscriptions Subscriptions for this event
	 * @return int The number of notifications handled
	 */
	protected function sendNotifications($event, $subscriptions) {

		$registeredMethods = elgg_get_config('notification_methods');
		$registeredMethods = $registeredMethods ? $registeredMethods : array();

		$count = 0;
		foreach ($subscriptions as $guid => $methods) {
			foreach ($methods as $method) {
				if (in_array($method, $registeredMethods)) {
					if ($this->sendNotification($event, $guid, $method)) {
						$count++;
					}
				}
			}
		}
		return $count;
	}

	/**
	 * Send a notification to a subscriber
	 *
	 * @param ElggNotificationEvent $event  The notification event
	 * @param int                   $guid   The guid of the subscriber
	 * @param string                $method The notification method
	 * @return bool
	 */
	protected function sendNotification($event, $guid, $method) {

		$recipient = get_entity($guid);
		if (!$recipient) {
			return false;
		}
		$language = $recipient->language;
		$params = array(
			'event' => $event,
			'method' => $method,
			'recipient' => $recipient,
			'language' => $language,
		);

		$subject = elgg_echo('notification:subject', array(), $language);
		$body = elgg_echo('notification:body', array(), $language);
		$notification = new ElggNotification($event->getActor(), $recipient, $language, $subject, $body);

		$type = 'notification:' . $event->getDescription();
		$notification = elgg_trigger_plugin_hook('prepare', $type, $params, $notification);

		// return true to indicate the notification has been sent
		$params = array('notification' => $notification);
		return elgg_trigger_plugin_hook('send', "notification:$method", $params, false);
	}
}
