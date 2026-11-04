<?php
function dbDelta( $sql ) {
 global $wpdb;
 $wpdb->schema_changes[] = $sql;
 return array();
}
