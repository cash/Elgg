<?php
/**
 * This class has to figure out who receives notifications based on subscriptions,
 * creates the notification messages, and then sends them out.
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
			if (elgg_trigger_plugin_hook('send', 'notifications', $params, true)) {
				$this->sendNotifications($event, $subscriptions);
			}
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
		// @todo this is a mock implementation
		$users = elgg_get_entities(array(
			'type' => 'user',
		));
		$result = array();
		foreach ($users as $user) {
			$result[$user->guid] = array('site');
		}
		return $result;
	}

	/**
	 * Sends the notifications based on subscriptions
	 *
	 * @param ElggNotificationEvent $event         Notification event
	 * @param array                 $subscriptions Subscriptions for this event
	 * @return int The number of notifications handled
	 */
	protected function sendNotifications($event, $subscriptions) {
		$count = 0;
		foreach ($subscriptions as $guid => $methods) {
			foreach ($methods as $method) {
				$this->sendNotification($event, $guid, $method);
				$count++;
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
	 */
	protected function sendNotification($event, $guid, $method) {
		$params = array('method' => $method, 'guid' => $guid, 'event' => $event);
		$result = array(
			'from' => $event->getActor(),
			'to' => get_entity($guid),
			'subject' => 'test message',
			'body' => 'test',
			'params' => array(),
		);
		// @todo - need to create this string from event
		$type = "message:entity:object:bookmarks";
		$result = elgg_trigger_plugin_hook($type, 'notifications', $params, $result);
		// @todo notify_user() should probably take an ElggMail object or something like that
		notify_user($result['to']->guid, $result['from']->guid, $result['subject'], $result['body'], $result['params'], $method);
	}
}
