<?php

/**
 * Heatmap class.
 *
 * @see UserFeedback_DB
 * @since 1.5.0
 *
 * @package UserFeedback
 * @subpackage DB
 */
class UserFeedback_Heatmap extends UserFeedback_DB {

    /**
     * @inheritdoc
     */
    protected $table_name = 'userfeedback_heatmaps';

    /**
     * @inheritdoc
     */
    protected $casts = array(
        'status' => 'string',
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
     * Change Heatmaps status to draft
     *
     * @param $heatmap_ids
     * @return bool|int
     */
    public static function draft( $heatmap_ids ) {
        return self::update_many(
            $heatmap_ids,
            array(
                'status' => 'draft',
            )
        );
    }

    /**
     * Change Heatmaps status to publish
     *
     * @param $heatmap_ids
     * @return bool|int
     */
    public static function publish( $heatmap_ids ) {
        return self::update_many(
            $heatmap_ids,
            array(
                'status' => 'publish',
            )
        );
    }

    /**
     * Change Heatmaps status to trash
     *
     * @param $heatmap_ids
     * @return bool|int
     */
    public static function trash( $heatmap_ids ) {
        return self::update_many(
            $heatmap_ids,
            array(
                'status' => 'trash',
            )
        );
    }

    /**
     * Change Heatmaps status to draft
     *
     * @param $heatmap_ids
     * @return bool|int
     */
    public static function restore( $heatmap_ids ) {
        return self::update_many(
            $heatmap_ids,
            array(
                'status' => 'draft',
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function get_columns() {
        return array( 'id', 'page_id', 'created_at', 'status' );
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
            page_id bigint(20) NOT NULL,
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            status enum('publish', 'draft', 'trash') DEFAULT 'publish',
            PRIMARY KEY (id)
        ) $charset_collate;";

        dbDelta( $sql );
    }

    /**
     * @inheritdoc
     */
    public function get_relationship_config( $name ) {
        switch ( $name ) {
            case 'recordings':
                return array(
                    'type'  => 'many',
                    'class' => UserFeedback_Heatmap_Recording::class,
                    'key'   => 'heatmap_id',
                );
            default:
                return null;
        }
    }
}
