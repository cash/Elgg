<?php
/**
 * Subscription
 *
 * This is a notification subscription of a user. It describes what events should
 * result in a notification being sent to a user. The schema is based on Activity
 * Streams (http://activitystrea.ms/). When an event that leads to notifications
 * occurs, the parameters of that event are compared with the subscriptions to
 * identify what users are notified.
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
 * 3. I want to be notified when something happens with the Cycling group
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
	protected $subscriber;

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
	 * @param string     $method     How the user should be notified
	 * @param ElggUser   $actor      The user of the subscription
	 * @param string     $event      Description of the event
	 * @param ElggEntity $object     The entity acted on
	 * @param ElggEntity $target     Usually the container
	 */
	public function __construct($subscriber, $method, $actor, $event, $object = null, $target = null) {
		;
	}
}
