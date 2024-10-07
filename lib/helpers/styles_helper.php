<?php

class OsStylesHelper {
	public static function generate_css_variables(): string {
		$css                     = '';
		$color_scheme            = OsSettingsHelper::get_booking_form_color_scheme();
		$default_css_variables = [
			'--latepoint-brand-primary'                => '#1d7bff',
			'--latepoint-body-color'                   => '#1f222b',
			'--latepoint-headings-color'               => '#14161d',
			'--latepoint-color-text-faded'             => '#7c85a3',
			'--latepoint-timeslot-selected-color'      => 'var(--latepoint-brand-primary)',
			'--latepoint-calendar-weekday-label-color' => 'var(--latepoint-headings-color)',
			'--latepoint-calendar-weekday-label-bg'    => '#fff',
			'--latepoint-side-panel-bg'                => '#fff',
			'--latepoint-summary-panel-bg'             => '#fff',
		];
		$override_css_variables = [];
		switch ( $color_scheme ) {
			case 'blue':
				$override_css_variables = [
					'--latepoint-brand-primary'                => '#1d7bff',
					'--latepoint-body-color'                   => '#1f222b',
					'--latepoint-headings-color'               => '#14161d',
					'--latepoint-color-text-faded'             => '#7c85a3',
					'--latepoint-side-panel-bg'                => '#fff',
					'--latepoint-summary-panel-bg'             => '#fff',
				];
				break;
			case 'purple':
				$override_css_variables = [
					'--latepoint-brand-primary'                => '#a32f96',
					'--latepoint-headings-color'               => '#14161d',
					'--latepoint-color-text-faded'             => '#a58eb3',
					'--latepoint-timeslot-selected-color'      => '--latepoint-brand-primary',
					'--latepoint-side-panel-bg'                => '#fcf1fd',
					'--latepoint-summary-panel-bg'             => '#fcf1fd',
				];
				break;

			case 'green':
				$override_css_variables = [
					'--latepoint-brand-primary'                => '#1ca00f',
					'--latepoint-headings-color'               => '#14161d',
					'--latepoint-color-text-faded'             => 'desaturate(lighten(--latepoint-brand-primary, 15%), 70%)',
					'--latepoint-timeslot-selected-color'      => '--latepoint-brand-primary',
					'--latepoint-side-panel-bg'                => '#f0fff4',
					'--latepoint-summary-panel-bg'             => '#f0fff4',
				];
				break;

			case 'red':
				$override_css_variables = [
					'--latepoint-brand-primary'                => '#F34747',
					'--latepoint-headings-color'               => '#14161d',
					'--latepoint-color-text-faded'             => 'desaturate(lighten(--latepoint-brand-primary, 15%), 70%)',
					'--latepoint-timeslot-selected-color'      => '#1449ff',
					'--latepoint-side-panel-bg'                => '#fdf1f1',
					'--latepoint-summary-panel-bg'             => '#fdf1f1',
				];
				break;

			case 'black':
				$override_css_variables = [
					'--latepoint-brand-primary'                => '#222',
					'--latepoint-headings-color'               => '#14161d',
					'--latepoint-color-text-faded'             => '#999',
					'--latepoint-timeslot-selected-color'      => '--latepoint-brand-primary',
					'--latepoint-side-panel-bg'                => '#fff',
					'--latepoint-summary-panel-bg'             => '#fff',
				];
				break;

			case 'teal':
				$override_css_variables = [
					'--latepoint-brand-primary'                => '#0f8c77',
					'--latepoint-headings-color'               => '#14161d',
					'--latepoint-color-text-faded'             => 'desaturate(lighten(--latepoint-brand-primary, 15%), 70%)',
					'--latepoint-timeslot-selected-color'      => '--latepoint-brand-primary',
					'--latepoint-side-panel-bg'                => '#edf8f9',
					'--latepoint-summary-panel-bg'             => '#edf8f9',
				];
				break;

			case 'orange':
				$override_css_variables = [
					'--latepoint-brand-primary'                => '#cc7424',
					'--latepoint-headings-color'               => '#14161d',
					'--latepoint-color-text-faded'             => 'desaturate(lighten(--latepoint-brand-primary, 15%), 70%)',
					'--latepoint-timeslot-selected-color'      => '--latepoint-brand-primary',
					'--latepoint-side-panel-bg'                => '#fffbf3',
					'--latepoint-summary-panel-bg'             => '#fffbf3',
				];
				break;
			case 'custom':
				$custom_primary_color = OsSettingsHelper::get_settings_value('custom_brand_primary_color', '#000000');

				$override_css_variables = [
					'--latepoint-brand-primary'                => $custom_primary_color,
					'--latepoint-timeslot-selected-color'      => '--latepoint-brand-primary',
					'--latepoint-side-panel-bg'                => '#fff',
					'--latepoint-summary-panel-bg'             => '#fff',
				];
				break;

		}
		$css_variables = array_merge($default_css_variables, $override_css_variables);
		$css .= ':root {';
		foreach ( $css_variables as $variable_name => $variable_value ) {
			$css .= $variable_name . ':' . $variable_value . ';';
		}
		$css .= '}';

		return $css;

	}
}