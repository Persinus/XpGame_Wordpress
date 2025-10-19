<?php

/**
 * Post Rating DB class.
 *
 * @see UserFeedback_DB
 * @since 1.0.0
 *
 * @package UserFeedback
 * @subpackage DB
 */
class UserFeedback_Post_Rating extends UserFeedback_DB {

    /**
     * @inheritdoc
     */
    protected $table_name = 'userfeedback_post_ratings';

    /**
     * @inheritdoc
     */
    public function get_columns() {
        return array('id', 'post_id', 'rating', 'created_at');
    }

    /**
     * Create the table
     *
     * @return void
     */
    public function create_table() {
        global $wpdb;

        if ( self::table_exists() ) {
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = self::get_table();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) UNSIGNED NOT NULL,
            rating tinyint UNSIGNED NOT NULL,
            user_id bigint(20) UNSIGNED,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY post_id (post_id)
        ) $charset_collate;";

        dbDelta($sql);
    }

    /**
     * Store a new post rating
     * 
     * @param int $post_id The post ID
     * @param int $rating The rating value (1-5)
     * @param int|null $user_id Optional user ID
     * @return bool|int False on failure, rating ID on success
     */
    public function store_rating($post_id, $rating, $user_id = null) {
        // Validate rating value
        if ($rating < 1 || $rating > 5) {
            return false;
        }
        
        // Store the new rating
        return self::create([
            'post_id' => $post_id,
            'rating' => $rating,
            'user_id' => $user_id
        ]);
    }

    /**
     * Get post rating statistics
     * 
     * @param int $post_id The post ID
     * @return array|false Rating stats or false if no ratings
     */
    public function get_post_rating($post_id) {
        global $wpdb;
        $table_name = $this->get_table();
        
        $sql = $wpdb->prepare(
            "SELECT 
                COUNT(rating) as total_ratings,
                ROUND(AVG(rating), 2) as average_rating,
                MIN(rating) as min_rating,
                MAX(rating) as max_rating
            FROM {$table_name}
            WHERE post_id = %d
            GROUP BY post_id",
            $post_id
        );
        
        $stats = $wpdb->get_row($sql);

        if ($stats) {
            return [
                'total_ratings' => (int) $stats->total_ratings,
                'average_rating' => (float) $stats->average_rating,
                'min_rating' => (int) $stats->min_rating,
                'max_rating' => (int) $stats->max_rating
            ];
        }

        return false;
    }

    /**
     * Get relationships config
     *
     * @param string $name Relationship name
     * @return array|null Relationship configuration
     */
    public function get_relationship_config($name) {
        return null;
    }
}