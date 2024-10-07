<?php

class OsCSVHelper {
	public static function array_to_csv( $data ) {
		$output = fopen( "php://output", "wb" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen
		foreach ( $data as $row ) {
			fputcsv( $output, $row );
		}
		fclose( $output ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
	}
}