<?php

/**
 * @var $bundle OsBundleModel
 * @var $order_item OsOrderItemModel
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

echo '<div class="latepoint-customer-bundle-scheduling-summary">';
	echo OsBundlesHelper::generate_summary_for_bundle($bundle, false, $order_item->id, LATEPOINT_USER_TYPE_CUSTOMER);
echo '</div>';