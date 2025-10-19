<?php

/**
 * UserFeedback Task class.
 *
 * Provides a flexible interface for creating different types of async tasks
 * using Action Scheduler. Supports single, recurring, cron, and async tasks.
 *
 * @since 1.8.0
 *
 * @package UserFeedback
 * @subpackage Task
 * @author  Fakhrul
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UserFeedback Task class.
 *
 * @since 1.8.0
 */
class UserFeedback_Task {
	/**
	 * Task type for async execution.
	 *
	 * @since 1.8.0
	 *
	 * @var string
	 */
	const TYPE_ASYNC = 'async';

	/**
	 * Task type for recurring execution.
	 *
	 * @since 1.8.0
	 *
	 * @var string
	 */
	const TYPE_RECURRING = 'scheduled';

	/**
	 * Task type for single execution.
	 *
	 * @since 1.8.0
	 *
	 * @var string
	 */
	const TYPE_ONCE = 'once';

	/**
	 * Default task group.
	 *
	 * @since 1.8.0
	 *
	 * @var string
	 */
	const GROUP = 'userfeedback';

	/**
	 * Current task type for fluent interface.
	 *
	 * @since 1.8.0
	 *
	 * @var string
	 */
	private $type = '';

	/**
	 * The action hook to trigger.
	 *
	 * @since 1.8.0
	 *
	 * @var string
	 */
	private $action = '';

	/**
	 * Unix timestamp when the task should run.
	 *
	 * @since 1.8.0
	 *
	 * @var int
	 */
	private $timestamp;

	/**
	 * Interval between recurring task executions in seconds.
	 *
	 * @since 1.8.0
	 *
	 * @var int
	 */
	private $interval;

	/**
	 * Parameters to pass to the action hook.
	 *
	 * @since 1.8.0
	 *
	 * @var array
	 */
	private $params;

	/**
	 * Create a new task with the specified action.
	 *
	 * @since 1.8.0
	 *
	 * @param string $action The action to trigger.
	 * @return UserFeedback_Task
	 */
	public static function create( $action ) {
		$task = new self();
		$task->action = $action;
		return $task;
	}

	/**
	 * Set task as async (runs immediately in background).
	 *
	 * @since 1.8.0
	 *
	 * @return UserFeedback_Task
	 */
	public function async() {
		$this->type = self::TYPE_ASYNC;

		return $this;
	}

	/**
	 * Set task as single (runs once at specific time).
	 *
	 * @since 1.8.0
	 *
	 * @param int $timestamp When the task will run (Unix timestamp).
	 * @return UserFeedback_Task
	 */
	public function once( $timestamp ) {
		$this->type      = self::TYPE_ONCE;
		$this->timestamp = (int) $timestamp;

		return $this;
	}

	/**
	 * Set task as recurring (runs at intervals).
	 *
	 * @since 1.8.0
	 *
	 * @param int $timestamp When the first instance will run (Unix timestamp).
	 * @param int $interval How long to wait between runs (in seconds).
	 * @return UserFeedback_Task
	 */
	public function recurring( $timestamp, $interval ) {
		$this->type      = self::TYPE_RECURRING;
		$this->timestamp = (int) $timestamp;
		$this->interval  = (int) $interval;

		return $this;
	}

	/**
	 * Set parameters to pass to the action hook.
	 *
	 * @since 1.8.0
	 *
	 * @param array $params Parameters to pass to the action hook.
	 * @return UserFeedback_Task
	 */
	public function params( $params ) {
		$this->params = $params;
		return $this;
	}

	/**
	 * Register the current task.
	 *
	 * @since 1.8.0
	 *
	 * @return int|false The action ID on success, false on failure.
	 */
	public function register() {
		if ( empty( $this->type ) ) {
			return false;
		}

		try {
			switch ( $this->type ) {
				case self::TYPE_ASYNC:
					return as_enqueue_async_action( $this->action, $this->params, self::GROUP );
				case self::TYPE_ONCE:
					return as_schedule_single_action( $this->timestamp, $this->action, $this->params, self::GROUP );
				case self::TYPE_RECURRING:
					return as_schedule_recurring_action( $this->timestamp, $this->interval, $this->action, $this->params, self::GROUP );
				default:
					return false;
			}
		} catch ( Exception $e ) {
			error_log( $e->getMessage() );
			return false;
		}
	}
} 