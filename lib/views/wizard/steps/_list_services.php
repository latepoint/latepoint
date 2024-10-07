<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="service-boxes">
	<?php
	foreach ( $services as $service ) { ?>
        <div class="service-box-w wizard-add-edit-item-trigger" data-id="<?php echo esc_attr($service->id); ?>"
             data-route="<?php echo esc_attr(OsRouterHelper::build_route_name( 'wizard', 'add_or_edit_service' )); ?>">
			<?php if ( $service->selection_image_id ) { ?>
                <div class="service-image"
                     style="background-image: url(<?php echo esc_url($service->selection_image_url); ?>)"></div>
			<?php } else { ?>
                <div class="service-image-placeholder"></div>
			<?php } ?>
            <div class="service-name"><?php echo esc_html($service->name); ?></div>
            <div class="service-agents">
				<?php if ( count( $agents ) > 1 ){ ?>
                <div class="agents-avatars">
					<?php foreach ( $service->agents as $index => $agent ) {
						if ( $index > 2 ) {
							break;
						} ?>
                        <div class="agent-avatar"
                             style="z-index: <?php echo esc_attr(3 - $index); ?>;background-image: url(<?php echo esc_url( $agent->avatar_url ); ?>)"></div>
					<?php } ?>
					<?php if ( count( $service->agents ) > 3 ) {
						echo '<div class="agents-more">+' . esc_html(( count( $service->agents ) - 3 ) . ' ' . __( 'more', 'latepoint' )) . '</div>';
					} ?>
                </div>
                <?php } ?>
            </div>

            <div class="service-remove-trigger"
                 data-os-pass-this="yes"
                 data-os-prompt="<?php echo esc_attr__( 'Are you sure you want to remove this service?', 'latepoint' ); ?>"
                 data-os-params="<?php echo esc_attr(OsUtilHelper::build_os_params( [ 'id' => $service->id ], 'destroy_service_' . $service->id )); ?>"
                 data-os-after-call="latepoint_remove_service_box"
                 data-os-action="<?php echo esc_attr(OsRouterHelper::build_route_name( 'services', 'destroy' )); ?>">
                <i class="latepoint-icon latepoint-icon-cross2"></i>
            </div>
        </div>
		<?php
	} ?>
    <div class="add-service-box wizard-add-edit-item-trigger"
         data-route="<?php echo esc_attr(OsRouterHelper::build_route_name( 'wizard', 'add_or_edit_service' )); ?>">
        <div class="add-service-graphic-w">
            <div class="add-service-plus"><i class="latepoint-icon latepoint-icon-plus4"></i></div>
        </div>
        <div class="add-service-label"><?php esc_html_e( 'Add Another Service', 'latepoint' ); ?></div>
    </div>
</div>