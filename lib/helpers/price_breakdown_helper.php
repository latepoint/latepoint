<?php
/*
 * Copyright (c) 2023 LatePoint LLC. All rights reserved.
 */

class OsPriceBreakdownHelper {

	public static function output_price_breakdown($rows, $inline_styles = false) {
		foreach ($rows['before_subtotal'] as $row) {
			self::output_price_breakdown_row($row, $inline_styles);
		}
		// if there is nothing between subtotal and total - don't show subtotal as it will be identical to total
		if (!empty($rows['after_subtotal'])) {
			if (!empty($rows['subtotal'])) {
				echo '<div class="subtotal-separator"></div>';
				self::output_price_breakdown_row($rows['subtotal'], $inline_styles);
			}
			foreach ($rows['after_subtotal'] as $row) {
				self::output_price_breakdown_row($row, $inline_styles);
			}
		}
		if (!empty($rows['total'])) {
			self::output_price_breakdown_row($rows['total'], $inline_styles);
		}
		if (!empty($rows['payments'])) {
			foreach ($rows['payments'] as $row) {
				self::output_price_breakdown_row($row, $inline_styles);
			}
		}
		if (!empty($rows['balance'])) {
			self::output_price_breakdown_row($rows['balance'], $inline_styles);
		}
	}

	public static function output_price_breakdown_row($row, $inline_styles = false) {
		if (!empty($row['items'])) {
            if($inline_styles){
                if (!empty($row['heading'])) echo '<div style="display: flex; align-items: center; margin-bottom: 5px; margin-top: 10px;"><div style="color: #788291;position: relative;font-size: 11px;text-transform: uppercase;letter-spacing: 1px;font-weight: 600;">' . esc_html($row['heading']) . '</div><div style="height: 1px;background-color: #f1f1f1;flex: 1;margin-left: 10px;"></div></div>';
            }else{
                if (!empty($row['heading'])) echo '<div class="summary-box-heading"><div class="sbh-item">' . esc_html($row['heading']) . '</div><div class="sbh-line"></div></div>';
            }
			foreach ($row['items'] as $row_item) {
				self::output_price_breakdown_row($row_item);
			}
		} else {
			$extra_class = '';
            $extra_css = '';
			if (isset($row['style']) && $row['style'] == 'strong') $extra_class .= ' spi-strong';
			if (isset($row['style']) && $row['style'] == 'total'){
                $extra_class .= ' spi-total';
                if($inline_styles) $extra_css = 'border-top: 3px solid #41444b;padding-top: 10px;margin-top: 10px;font-size: 16px;';
			}
			if (isset($row['type']) && $row['type'] == 'credit') $extra_class .= ' spi-positive';
			if (isset($row['style']) && $row['style'] == 'sub') $extra_class .= ' spi-sub';
			?>
			<div class="summary-price-item-w <?php echo esc_attr($extra_class); ?>" <?php if($inline_styles) echo 'style="display: flex;justify-content: space-between;margin-bottom: 7px;'.esc_attr($extra_css).'"'; ?>>
				<div class="spi-name">
					<?php echo esc_html($row['label']); ?>
					<?php if (!empty($row['note'])) echo '<span class="pi-note">' . esc_html($row['note']) . '</span>'; ?>
					<?php if (!empty($row['badge'])) echo '<span class="pi-badge">' . esc_html($row['badge']) . '</span>'; ?>
				</div>
				<div class="spi-price"><?php echo esc_html($row['value']); ?></div>
			</div>
			<?php
		}
		if (!empty($row['sub_items'])) {
            if($inline_styles){
                if (!empty($row['sub_items_heading'])) echo '<div style="display: flex; align-items: center; margin-bottom: 5px; margin-top: 10px;"><div style="color: #788291;position: relative;font-size: 11px;text-transform: uppercase;letter-spacing: 1px;font-weight: 600;">' . esc_html($row['sub_items_heading']) . '</div><div style="height: 1px;background-color: #f1f1f1;flex: 1;margin-left: 10px;"></div></div>';
            }else{
                if (!empty($row['sub_items_heading'])) echo '<div class="summary-box-heading"><div class="sbh-item">' . esc_html($row['sub_items_heading']) . '</div><div class="sbh-line"></div></div>';
            }
			foreach ($row['sub_items'] as $row_item) {
				self::output_price_breakdown_row($row_item);
			}
		}
    }

	public static function is_zero(array $price_breakdown_rows) :bool {
		$subtotal = (float) $price_breakdown_rows['subtotal']['raw_value'];
		return ($subtotal == 0);
	}
}