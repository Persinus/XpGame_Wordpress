<?php

/**
 *
 * @see UserFeedback_Notification_Event
 * @since 1.0.0
 *
 * @package UserFeedback
 * @subpackage Notifications
 * @author  David Paternina
 */
class UserFeedback_Notification_Upgrade_After_7_Days extends UserFeedback_Notification_Event {

	public $id            = 'userfeedback_upgrade_after_7_days';
	public $license_types = array( 'lite' );
	public $interval      = 7;

	public function prepare() {
		$this->title   = __( 'Get More UserFeedback', 'userfeedback-lite' );
		$this->content =
			__( 'Ask more types of questions, customize displays,  and unlock better insights with UserFeedback Pro.', 'userfeedback-lite' );

		$this->buttons[] = array(
			'text'        => __( 'Upgrade to Pro', 'userfeedback-lite' ),
			'url'         => userfeedback_get_upgrade_link(),
			'is_external' => true,
		);

		return parent::prepare();
	}
}

new UserFeedback_Notification_Upgrade_After_7_Days();
