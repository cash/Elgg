<?php
/**
 * ElggDatabaseQueue tests
 *
 * @package Elgg
 * @subpackage Test
 */
class ElggCoreDatabaseQueueTest extends ElggCoreUnitTest {

	public function testEnqueueAndDequeue() {
		$queue = new ElggDatabaseQueue('unit:test');
		$first = array(1, 2, 3);
		$second = array(4, 5, 6);

		$result = $queue->enqueue($first);
		$this->assertIdentical($result, true);
		$result = $queue->enqueue($second);
		$this->assertIdentical($result, true);

		$data = $queue->dequeue();
		$this->assertIdentical($data, $first);
		$data = $queue->dequeue();
		$this->assertIdentical($data, $second);

		$data = $queue->dequeue();
		$this->assertIdentical($data, null);
	}

	public function testMultipleQueues() {
		$queue1 = new ElggDatabaseQueue('unit:test1');
		$queue2 = new ElggDatabaseQueue('unit:test2');
		$first = array(1, 2, 3);
		$second = array(4, 5, 6);

		$result = $queue1->enqueue($first);
		$this->assertIdentical($result, true);
		$result = $queue2->enqueue($second);
		$this->assertIdentical($result, true);

		$data = $queue2->dequeue();
		$this->assertIdentical($data, $second);
		$data = $queue1->dequeue();
		$this->assertIdentical($data, $first);
	}
}