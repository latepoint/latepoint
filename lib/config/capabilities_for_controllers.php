<?php
return [
	'OsDashboardController' => [
		'default' => ['booking__view'],
	],
	'OsActivitiesController' => [
		'default' => ['activity__view'],
		'per_action' => [
			'destroy' => ['activity__delete']
		]
	],
	'OsAddonsController' => [
	],
	'OsDefaultAgentController' => [
	],
	'OsAgentsController' => [
		'default' => ['agent__edit'],
		'per_action' => [
			'edit_form' => ['agent__view'],
			'index' => ['agent__view'],
			'mini_profile' => ['agent__view'],
			'new_form' => ['agent__create'],
			'create' => ['agent__create'],
			'destroy' => ['agent__delete'],
		]
	],
	'OsAuthController' => [
	],
	'OsBookingsController' => [
		'default' => ['booking__view'],
		'per_action' => [
			'view_booking_log' => ['activity__view'],
			'change_status' => ['booking__edit'],
			'update' => ['booking__edit'],
			'create' => ['booking__create'],
			'destroy' => ['booking__delete'],
		]
	],
	'OsOrdersController' => [
		'default' => ['booking__view'],
		'per_action' => [
			'view_order_log' => ['activity__view'],
			'change_status' => ['booking__edit'],
			'update' => ['booking__edit'],
			'create' => ['booking__create'],
			'destroy' => ['booking__delete'],
		]
	],
	'OsCalendarsController' => [
		'default' => ['booking__view'],
	],
	'OsCustomerCabinetController' => [
	],
	'OsCustomersController' => [
		'default' => ['customer__edit'],
		'per_action' => [
			'set_as_guest' => ['customer__edit'],
			'destroy' => ['customer__delete'],
			'new_form' => ['customer__create'],
			'create' => ['customer__create'],
			'query_for_booking_form' => ['customer__view'],
			'edit_form' => ['customer__view'],
			'mini_profile' => ['customer__view'],
			'index' => ['customer__view'],
			'inline_edit_form' => ['customer__view'],
		]
	],
	'OsDebugController' => [
	],
	'OsIntegrationsController' => [
	],
	'OsNotificationsController' => [
	],
	'OsProcessJobsController' => [
	],
	'OsProcessesController' => [
	],
	'OsSearchController' => [
		'default' => ['booking__view'],
	],
	'OsServiceCategoriesController' => [
		'default' => ['service__edit'],
		'per_action' => [
			'list_for_select' => ['service__view'],
			'index' => ['service__view'],
			'destroy' => ['service__delete'],
			'create' => ['service__create']
		]
	],
	'OsServicesController' => [
		'default' => ['service__edit'],
		'per_action' => [
			'index' => ['service__view'],
			'create' => ['service__create'],
			'destroy' => ['service__delete'],
		]
	],
	'OsBundlesController' => [
		'default' => ['bundle__edit'],
		'per_action' => [
			'index' => ['bundle__view'],
			'create' => ['bundle__create'],
			'destroy' => ['bundle__delete'],
		]
	],
	'OsSettingsController' => [
		'per_action' => [
			'load_work_period_form' => ['agent__edit'],
			'remove_chain_schedule' => ['agent__edit'],
			'remove_custom_day_schedule' => ['agent__edit'],
			'save_custom_day_schedule' => ['agent__edit'],
			'custom_day_schedule_form' => ['agent__edit'],
			'update_work_periods' => ['agent__edit'],
		]
	],
	'OsStepsController' => [
	],
	'OsTransactionsController' => [
		'default' => ['transaction__edit'],
		'per_action' => [
			'destroy' => ['transaction__delete'],
			'index' => ['transaction__view']
		]
	],
	'OsUpdatesController' => [
	],
	'OsWizardController' => [
	],
	'OsMessagesController' => [
		'default' => ['chat__edit']
	]
];