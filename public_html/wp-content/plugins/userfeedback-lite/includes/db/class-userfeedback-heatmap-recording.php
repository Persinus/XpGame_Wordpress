<?php

/**
 * Heatmap Recording class.
 *
 * @see UserFeedback_DB
 * @since 1.5.0
 *
 * @package UserFeedback
 * @subpackage DB
 */
class UserFeedback_Heatmap_Recording extends UserFeedback_DB {

    /**
     * @inheritdoc
     */
    protected $table_name = 'userfeedback_heatmap_recordings';

    /**
     * @inheritdoc
     */
    protected $casts = array(
        'heatmap_data' => 'string',
        'interaction_type' => 'string',
    );

    /**
     * @inheritdoc
     */
    public static function find( $id ) {
        return self::where(
            array(
                'id' => $id,
            )
        )->single();
    }

    /**
     * @inheritdoc
     */
    public function get_columns() {
        return array( 'id', 'heatmap_id', 'heatmap_data', 'created_at', 'interaction_type' );
    }

    /**
     * @inheritdoc
     */
    public function create_table() {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();
        $table_name      = self::get_table();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            heatmap_id bigint(20) NOT NULL,
            heatmap_data longtext NOT NULL,
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            interaction_type varchar(100) NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        dbDelta( $sql );
    }

    /**
     * @inheritdoc
     */
    public function get_relationship_config( $name ) {
        switch ( $name ) {
            case 'heatmap':
                return array(
                    'type'  => 'one',
                    'class' => UserFeedback_Heatmap::class,
                    'key'   => 'heatmap_id',
                );
            default:
                return null;
        }
    }
}
