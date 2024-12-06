<?php

class OsSupportTopicsHelper {
	public static function get_title_for_topic(string $topic): string {
		$topic_titles = [
			'payment_request' => __('Payment Request', 'latepoint'),
		];

		return $topic_titles[$topic] ?? __('Information', 'latepoint');
	}
}