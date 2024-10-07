<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="os-service os-service-status-<?php echo esc_attr($service->status); ?>">
  <div class="os-service-header">
    <?php if($service->is_hidden()) echo '<i class="latepoint-icon latepoint-icon-eye-off service-hidden"></i>'; ?>
    <h3 class="service-name"><?php echo esc_html($service->name); ?></h3>
  </div>
  <div class="os-service-body">
    <div class="os-service-agents">
      <div class="label"><?php esc_html_e('Agents:', 'latepoint'); ?></div>
      <div class="agents-avatars">
        <?php foreach($service->agents as $index => $agent){ 
          if ($index > 1) break; ?>
          <div class="agent-avatar" style="background-image: url(<?php echo esc_url($agent->get_avatar_url()); ?>)"></div>
        <?php } ?>
        <?php if(count($service->agents) > 2) echo '<div class="agents-more">+'.(count($service->agents) - 2).' '.esc_html__('more', 'latepoint').'</div>'; ?>
      </div>
    </div>
    <div class="os-service-info">
      <div class="service-info-row">
        <div class="label"><?php esc_html_e('Duration:', 'latepoint'); ?></div>
        <div class="value"><strong><?php echo esc_html($service->duration); ?></strong> <?php esc_html_e('min', 'latepoint'); ?></div>
      </div>
      <div class="service-info-row">
        <div class="label"><?php esc_html_e('Price:', 'latepoint'); ?></div>
        <div class="value"><strong><?php echo esc_html($service->price_min_formatted); ?></strong></div>
      </div>
      <div class="service-info-row">
        <div class="label"><?php esc_html_e('Buffer:', 'latepoint'); ?></div>
        <div class="value"><strong><?php echo esc_html($service->buffer_before.'/'.$service->buffer_after); ?></strong> <?php esc_html_e('min', 'latepoint'); ?></div>
      </div>
      <?php do_action('latepoint_service_tile_info_rows_after', $service); ?>
    </div>
  </div>
  <div class="os-service-foot">
    <a href="<?php echo esc_url(OsRouterHelper::build_link(OsRouterHelper::build_route_name('services', 'edit_form'), array('id' => $service->id) )); ?>" class="latepoint-btn latepoint-btn-block latepoint-btn-secondary">
      <i class="latepoint-icon latepoint-icon-edit-3"></i>
      <span><?php esc_html_e('Edit Service', 'latepoint'); ?></span>
    </a>
  </div>
</div>