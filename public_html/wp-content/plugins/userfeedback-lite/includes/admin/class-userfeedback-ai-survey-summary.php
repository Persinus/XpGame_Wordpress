<?php

use WP_Rocket\Engine\License\API\User;

/**
 * AI Survey Summary Controller class.
 *
 * Handles API calls related to AI Survey Summary
 *
 * @since 1.0.0
 *
 * @package UserFeedback
 * @author  Andrei Lupu
 */
class Userfeedback_AI_Survey_Summary {

	/**
	 * The request size limit in bytes(~15Kb). Useful to be compared against strlen.
	 */
	private $request_size_limit = 15000;

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Registers REST routes
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			'userfeedback/v1',
			'/ai-summary/(?P<id>\w+)/',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'rest_ai_summary_callback' ),
				'permission_callback' => array( $this, 'view_results_permission_check' )
			)
		);

		register_rest_route(
			'userfeedback/v1',
			'/preload-ai-summary/(?P<id>\w+)/',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'rest_preload_ai_summary_callback' ),
				'permission_callback' => array( $this, 'view_results_permission_check' )
			)
		);
	}

	/**
	 * Get Survey results data
	 *
	 * @param $survey_id
	 * @return mixed|null
	 */
	public static function get_survey_results_data( $survey_id ) {

		$start_date_7_days  = ( new DateTime() )->modify( '-7 days' );
		$start_date_30_days = ( new DateTime() )->modify( '-30 days' );
		$end_date           = new DateTime();

		$survey = UserFeedback_Survey::where(
			array(
				'id' => $survey_id,
			)
		)->select( array( 'title', 'status', 'impressions', 'questions' ) )
			->with_count_where(
				'responses',
				array(
					array(
						'submitted_at',
						'>=',
						$start_date_7_days->format( 'Y-m-d' ),
					),
					array(
						'submitted_at',
						'<=',
						$end_date->format( 'Y-m-d' ),
					),
				),
				'responses_count_7_days'
			)
			->with_count_where(
				'responses',
				array(
					array(
						'submitted_at',
						'>=',
						$start_date_30_days->format( 'Y-m-d' ),
					),
					array(
						'submitted_at',
						'<=',
						$end_date->format( 'Y-m-d' ),
					),
				),
				'responses_count_30_days'
			)
			->with( array( 'responses' ) )
			->single();

		if ( $survey === null ) {
			return null;
		}

		// Survey total responses
		$total_responses         = sizeof( $survey->responses );
		$survey->responses_count = $total_responses;

		// Survey question stats
		$quantitative_question_types = array( 'radio-button', 'image-radio', 'icon-choice', 'checkbox', 'nps', 'star-rating' );
		$question_stats              = array();

		$questions = $survey->questions;
		$responses = $survey->responses;

		foreach ( $questions as $question ) {
			$id   = $question->id;
			$type = $question->type;

			$is_quantitative = in_array( $type, $quantitative_question_types );

			$question_data = array(
				'id'              => $question->id,
				'title'           => $question->title,
				'type'            => $question->type,
				'total_answers'   => 0,
				'skipped'         => 0,
				'is_quantitative' => $is_quantitative,
			);

			if ( $is_quantitative ) {
				switch ( $type ) {
					case 'radio-button':
					case 'image-radio':
					case 'icon-choice':
					case 'checkbox':
						$question_data['options'] = array_map(
							function ( $option ) {
								return array(
									'value' => $option,
									'count' => 0,
								);
							},
							$question->config->options
						);
						break;
					case 'nps':
						$question_data['options'] = array_map(
							function ( $option ) {
								return array(
									'value' => $option,
									'count' => 0,
								);
							},
							array( 1, 2, 3, 4, 5, 6, 7, 8, 9, 10 )
						);
						break;
					case 'star-rating':
						$question_data['options'] = array_map(
							function ( $option ) {
								return array(
									'value' => $option,
									'count' => 0,
								);
							},
							array( 1, 2, 3, 4, 5 )
						);
						break;
				}
			} else {
				$question_data['answers'] = array();
			}

			foreach ( $responses as $response ) {
				$question_answer_index = array_search( $id, array_column( $response->answers, 'question_id' ) );
				$value                 = $response->answers[ $question_answer_index ]->value;
				$extra                 = isset( $response->answers[ $question_answer_index ]->extra ) ? $response->answers[ $question_answer_index ]->extra : null;

				if ( $question_answer_index === false || $value === null ) {
					$question_data['skipped']++;
					continue;
				} else {
					$question_data['total_answers']++;
				}

				if ( $is_quantitative ) {
					if ( is_array( $value ) ) {
						foreach ( $value as $picked_value ) {
							$option_index = array_search( $picked_value, array_column( $question_data['options'], 'value' ) );
							$question_data['options'][ $option_index ]['count']++;
						}
					} else {
						$option_index = array_search( $value, array_column( $question_data['options'], 'value' ) );
						$question_data['options'][ $option_index ]['count']++;
					}
				}

				$question_data['answers'][] = array(
					'response_id' => $response->id,
					'value'       => $value,
					'date'        => $response->submitted_at,
					'extra'       => $extra,
				);
			}

			$question_stats[] = $question_data;
		}

		$survey->question_stats = $question_stats;

		return $survey;
	}

	/**
	 * Permissions/capabilities check
	 *
	 * @return bool
	 */
	public function view_results_permission_check() {
		return current_user_can( 'userfeedback_view_results' );
	}

	/**
	 * Preload AI Summary data. Returns data only if it's already cached.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function rest_preload_ai_summary_callback( WP_REST_Request $request ) {
		$survey_id   = $request->get_param( 'id' );
		$survey_data = $this->get_survey_results_data( $survey_id );
		$cache_key   = '_userfeedback_ai_summary_' . $survey_id . '_' . $survey_data->responses_count;
		$cache       = get_transient( $cache_key );

		if ( ! empty( $cache ) ) {
			return rest_ensure_response( array(
				'success' => true,
				'summary' => $cache,
			));
		}

		return rest_ensure_response( array(
			'success' => false,
			'summary' => null,
		));
	}

	/**
	 * Get Survey results data and cache it in a transient based on survey id and responses count.
	 *
	 * @param $survey_id
	 * @return mixed|null
	 */
	public function rest_ai_summary_callback( WP_REST_Request $request ) {
		$survey_id   = $request->get_param( 'id' );
		$survey_data = $this->get_survey_results_data( $survey_id );
		$cache_key   = '_userfeedback_ai_summary_' . $survey_id . '_' . $survey_data->responses_count;
		$cache       = get_transient( $cache_key );

		if ( ! empty( $cache ) ) {
			return rest_ensure_response( array(
				'success' => true,
				'summary' => $cache,
			));
		}

		if ( $survey_data === null ){
			return rest_ensure_response(
				array(
					'success' => false,
					'error'   => __( 'We don\'t have enough data to process this survey.', 'userfeedback-lite' ),
				)
			);
		}

		// We only want to get text and long-text questions.
		$questions = [];
		$responses = [];
		$no_valid_answers = true;
		$body_size = 0;
		
		foreach ($survey_data->questions as $key => $question) {
			if ($question->type === 'text' || $question->type === 'long-text') {
				$questions[$key] = $question;
				$body_size += strlen(json_encode($question));
			}
		}

		// We will filter out responses that are not related to text and long-text questions.
		foreach ($survey_data->responses as $key => $response) {
			$filtered_answers = [];

			foreach ($response->answers as $i => $answer) {
				if ( empty($answer->value) ) {
					continue;
				}

				$question_index = array_search($answer->question_id, array_column($survey_data->questions, 'id'));
				$question = $survey_data->questions[$question_index];

				if ($question->type === 'text' || $question->type === 'long-text') {
					$filtered_answers[$i] = $answer;
					$no_valid_answers = false;
				}
			}

			if ( empty($filtered_answers) ) {
				continue;
			}

			$responses[$key] = array(
				'id' => $response->id,
				'answers' => $filtered_answers,
			);

			$body_size += strlen(json_encode($filtered_answers));
		}
		
		if ( empty( $questions ) || $no_valid_answers ){
			return rest_ensure_response(
				array(
					'success' => false,
					'error'   => __( 'We don\'t have enough data to process this survey.', 'userfeedback-lite' ),
				)
			);
		}

		if ( $body_size > $this->request_size_limit ) {
			return rest_ensure_response(
				array(
					'success' => false,
					'error'   => __( 'The survey exceeds our server capacity.', 'userfeedback-lite' ),
				)
			);
		}

		// Survey total responses
		$license_key = UserFeedback()->license->get_site_license_key();
		$api_url     = apply_filters( 'userfeedback_ai_summary_api_url', 'https://userfeedback.com/' );
		$api_route   = 'wp-json/ai-summary/v1/get-summary';

		$post = wp_remote_post( $api_url . $api_route, array(
			'method'  => 'POST',
			'timeout' => 30,
			'body'    => array(
				'license_key' => $license_key,
				'site_url'    => site_url(),
				'questions'   => $questions,
				'responses'   => $responses,
			),
		) );

		if ( is_wp_error( $post ) ) {
			// If we get a server error we won't get a response code, so we return a generic error message.
			return rest_ensure_response(
				array(
					'success' => false,
					'error'   => __( 'An error occurred while trying to get the AI Summary.', 'userfeedback-lite' ),
				)
			);
		}

		$response = wp_remote_retrieve_body( $post );
		$code     = wp_remote_retrieve_response_code( $post );
		$response = json_decode($response, true);

		if ( $code !== 200 ) {
			// If we have a known error code, we return the error message that we trust.
			switch ( $response['code'] ) {
				case 'maintenance_mode':
				case 'empty_conversation':
				case 'length_error':
				case 'license_key':
				case 'site_url':
				case 'quota_exceeded':
				case 'invalid_param':
				case 'empty_param':
				case 'size_limit_exceeded':
				case 'license_expired':
					return rest_ensure_response( array(
						'success' => false,
						'error'   => esc_html($response['message']),
					));
					break;
				// Otherwise, we return a generic error message.
				default:
					return rest_ensure_response( array(
						'success' => false,
						'error'   => __( 'An error occurred while trying to get the AI Summary.', 'userfeedback-lite' ),
					));
					break;
			}
		}

		// We expect a summary and a success key in the response. Otherwise, we return a generic error message.
		if ( empty( $response['summary'] ) || empty( $response['success'] ) ) {
			return rest_ensure_response(
				array(
					'success' => false,
					'error'   => __( 'An error occurred while trying to get the AI Summary.', 'userfeedback-lite' ),
				)
			);
		}

		set_transient( $cache_key, $response['summary'], 1 * HOUR_IN_SECONDS );

		return rest_ensure_response( $response );
	}


}

new Userfeedback_AI_Survey_Summary();
