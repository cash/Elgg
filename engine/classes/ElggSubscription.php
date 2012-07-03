<?php
/**
 * Subscription
 *
 * This is a notification subscription of a user. It describes what events should
 * result in a notification being sent to a user. The schema is based on Activity
 * Streams (http://activitystrea.ms/). When an event that leads to notifications
 * occurs, the parameters of that event are compared with the subscriptions to
 * identify which users are notified.
 *
 * Definitions:
 * Actor:  the user causing the event.
 * Event:  the identifier of the Elgg event plus type information (subtype for entities,
 *         names for relationships and anotations)
 * Object: the entity acted on.
 * Target: the container for the object.
 *
 * Some example subscriptions:
 *  1. I want to be notified when John does something
 *     actor: John
 *     event: null
 *     object: null
 *     target: null
 *
 * 2. I want to be notified when Susan likes something
 *    actor: Susan
 *    event: create:annotation:like
 *    object: null
 *    target: null
 *
 * 3. I want to be notified when something happens in the Cycling group
 *    actor: null
 *    event: null
 *    object: null
 *    target: Cycling group
 *
 * 4. I want to be notified when someone joins the Cycling group
 *    actor: null
 *    event: create:relationship:member
 *    object: Cycling group
 *    target: null
 *
 * 5. I want to be notified when Ed uploads a file to his personal files
 *    actor: Ed
 *    event: create:object:file
 *    object: null
 *    target: Ed
 */
class ElggSubscription {

	/* @var ElggUser The user doing the subscribing */
	protected $subscriber_guid;

	/* @var string The method for sending the notification */
	protected $method;

	/* @var string The name of the action/event */
	protected $event;

	/* @var int The GUID of the user who triggered the event */
	protected $actor_guid;

	/* @var int The GUID of the object of the event */
	protected $object_guid;

	/* @var int The GUID of the target of the event (usually the container for the object) */
	protected $target_guid;


	/**
	 * Create a subscription object
	 * 
	 * @param ElggUser   $subscriber The user to be notified
	 * @param string     $method     How the user should be notified: email, site, sms, etc.
	 * @param string     $event      Description of the event
	 * @param ElggUser   $actor      The user of the subscription
	 * @param ElggEntity $object     The entity acted on
	 * @param ElggEntity $target     Usually the container
	 */
	public function __construct(ElggUser $subscriber, $method, $event, ElggUser $actor = null, ElggEntity $object = null, ElggEntity $target = null) {
		if (!elgg_instanceof($subscriber, 'user')) {
			// throw exception
		}

		$this->subscriber_guid = $subscriber->getGUID();
		$this->method = $method;
		$this->event = $event;
		$this->actor_guid = $actor ? $actor->getGUID() : null;
		$this->object_guid = $object ? $object->getGUID() : null;
		$this->target_guid = $target ? $target->getGUID() : null;
	}

	/**
	 * Get the subscription as a set of key value pairs for non-null values
	 *
	 * @return array
	 */
	public function getData() {
		$data = array();
		foreach ($this as $key => $value) {
			if (!is_null($value)) {
				$data[$key] = $value;
			}
		}
		return $data;
	}
}
