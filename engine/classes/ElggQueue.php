<?php
/**
 * FIFO queue interface
 */
interface ElggQueue {

	/**
	 * Add an item to the queue
	 *
	 * @param mixed $item
	 * @return bool
	 */
	public function enqueue($item);

	/**
	 * Remove the oldest item from the queue
	 *
	 * @return mixed
	 */
	public function dequeue();
}
