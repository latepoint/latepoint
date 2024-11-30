<?php
/*
 * Copyright (c) 2024 LatePoint LLC. All rights reserved.
 */

class OsInvoicesHelper {

	public static function readable_status( string $status ): string {
		$statuses = self::list_of_statuses_for_select();

		return $statuses[ $status ] ?? __( 'n/a', 'latepoint-pro-features' );
	}

    public static function get_invoice_by_key(string $key): OsInvoiceModel {
        if(empty($key)) return new OsInvoiceModel();
        $invoice = new OsInvoiceModel();
        return $invoice->where(['access_key' => $key])->set_limit(1)->get_results_as_models();
    }

	public static function invoice_document_html( OsInvoiceModel $invoice, bool $show_controls = false ) {
		$invoice_data = json_decode( $invoice->data, true );
		?>
        <div class="invoice-document status-<?php echo esc_attr( $invoice->status ); ?>">
			<?php if ( $show_controls ) { ?>
                <div class="invoice-controls">
                    <div class="ic-block">
						<?php echo OsFormHelper::select_field( 'invoice[status]', __( 'Status', 'latepoint-pro-features' ), OsInvoicesHelper::list_of_statuses_for_select(), $invoice->status ); ?>
                    </div>
                    <div class="ic-block">
                        <a target="_blank" href="<?php echo $invoice->get_access_url(); ?>" class="ic-external-link">
                            <span><?php esc_html_e( 'Open', 'latepoint-pro-features' ); ?></span>
                            <i class="latepoint-icon latepoint-icon-external-link"></i>
                        </a>
                    </div>
                    <div class="ic-block make-last">
                        <button type="button" class="latepoint-btn latepoint-btn-sm latepoint-btn-outline">
                            <i class="latepoint-icon latepoint-icon-mail"></i>
                            <span><?php esc_html_e( 'Email Invoice', 'latepoint-pro-features' ); ?></span>
                        </button>
                    </div>
                </div>
			<?php } ?>
            <div class="invoice-document-i">
                <?php switch($invoice->status){
                    case LATEPOINT_INVOICE_STATUS_PAID:
                    echo '<div class="invoice-status-paid-label">'.esc_html(self::readable_status($invoice->status)).'</div>';
                    break;
                    case LATEPOINT_INVOICE_STATUS_VOIDED:
                    echo '<div class="invoice-status-voided-label">'.esc_html(self::readable_status($invoice->status)).'</div>';
                    break;
                }
                ?>
                <div class="invoice-heading">
                    <div class="invoice-info">
                        <div class="invoice-title"><?php esc_html_e( 'Invoice', 'latepoint-pro-features' ); ?></div>
                        <div class="invoice-data">
                            <div class="invoice-row">
                                <div class="id-label"><?php esc_html_e( 'Invoice number', 'latepoint-pro-features' ); ?></div>
                                <div class="id-value"><?php echo esc_html( $invoice->invoice_number ); ?></div>
                            </div>
                            <div class="invoice-row">
                                <div class="id-label"><?php esc_html_e( 'Date of issue', 'latepoint-pro-features' ); ?></div>
                                <div class="id-value"><?php echo esc_html( OsTimeHelper::get_readable_date( new OsWpDateTime( $invoice->created_at ) ) ); ?></div>
                            </div>
                            <div class="invoice-row">
                                <div class="id-label"><?php esc_html_e( 'Date due', 'latepoint-pro-features' ); ?></div>
                                <div class="id-value"><?php echo esc_html( OsTimeHelper::get_readable_date( new OsWpDateTime( $invoice->created_at ) ) ); ?></div>
                            </div>
                            <div class="invoice-row">
                                <div class="id-label"><?php esc_html_e( 'VAT Number', 'latepoint-pro-features' ); ?></div>
                                <div class="id-value"><?php echo esc_html( '82947594' ); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="invoice-logo">
                        <img src="<?php echo esc_attr( LATEPOINT_IMAGES_URL . 'logo.svg' ); ?>" width="50" height="50" alt="LatePoint Dashboard">
                    </div>
                </div>
                <div class="invoice-to-from">
                    <div class="invoice-from">
                        <div class="if-heading"><?php echo esc_html( 'Dental Services LLC' ); ?></div>
                        <div class="if-data-block">
                            527 Madison Ave, ste 104<br>
                            New York, NY 10023<br>
                            United States<br>
                            888.958.3875
                        </div>
                    </div>
                    <div class="invoice-from">
                        <div class="if-heading"><?php echo esc_html( 'Bill to' ); ?></div>
                        <div class="if-data-block">
							<?php echo $invoice_data['to']; ?>
                        </div>
                    </div>
                </div>
                <div class="invoice-due-info">
                    <div class="invoice-due-amount">
						<?php echo esc_html( sprintf( __( '%s due %s', 'latepoint-pro-features' ), OsMoneyHelper::format_price( $invoice->charge_amount, true, false ), OsTimeHelper::get_readable_date_from_string( $invoice->due_at ) ) ); ?>
                    </div>
                    <?php if($invoice->status == LATEPOINT_INVOICE_STATUS_NOT_PAID){ ?>
                    <div class="invoice-due-pay-link-w">
                        <a href="<?php echo $invoice->get_pay_url(); ?>" target="_blank"><?php esc_html_e( 'Pay Online', 'latepoint-pro-features' ); ?></a>
                    </div>
                    <?php } ?>
                    <?php if($invoice->status == LATEPOINT_INVOICE_STATUS_PAID){ ?>
                    <div class="invoice-due-pay-link-w">
                        <a href="#"><?php esc_html_e( 'View Payments', 'latepoint-pro-features' ); ?></a>
                    </div>
                    <?php } ?>
                </div>
                <div class="invoice-items">
                    <div class="invoice-items-table-heading">
                        <div class="it-column"><?php esc_html_e( 'Description', 'latepoint-pro-features' ); ?></div>
                        <div class="it-column"><?php esc_html_e( 'Amount', 'latepoint-pro-features' ); ?></div>
                    </div>
					<?php OsPriceBreakdownHelper::output_price_breakdown( $invoice_data['price_breakdown'] ); ?>
                </div>
                <div class="invoice-totals">
                    <div class="it-row">
                        <div class="it-column"><?php esc_html_e( 'Subtotal', 'latepoint-pro-features' ); ?></div>
                        <div class="it-column"><?php echo esc_html( OsMoneyHelper::format_price( $invoice_data['totals']['subtotal'], true, false ) ); ?></div>
                    </div>
                    <div class="it-row">
                        <div class="it-column"><?php esc_html_e( 'Total', 'latepoint-pro-features' ); ?></div>
                        <div class="it-column"><?php echo esc_html( OsMoneyHelper::format_price( $invoice_data['totals']['total'], true, false ) ); ?></div>
                    </div>
                    <div class="it-row it-row-bold">
                        <div class="it-column"><?php esc_html_e( 'Amount Due', 'latepoint-pro-features' ); ?></div>
                        <div class="it-column"><?php echo esc_html( OsMoneyHelper::format_price( $invoice->charge_amount, true, false ) ); ?></div>
                    </div>
                </div>
				<?php if ( OsSettingsHelper::get_settings_value( 'invoice_terms', '' ) ) { ?>
                    <div class="invoice-terms">
                        <div class="terms-heading"><?php esc_html_e( 'Terms & Conditions', 'latepoint-pro-features' ); ?></div>
                        <div class="terms-content"><?php echo esc_html( OsSettingsHelper::get_settings_value( 'invoice_terms', '' ) ); ?></div>
                    </div>
				<?php } ?>
            </div>
        </div>
		<?php
	}

	public static function list_of_statuses_for_select(): array {
		$statuses = [
			LATEPOINT_INVOICE_STATUS_NOT_PAID       => __( 'Not Paid', 'latepoint-pro-features' ),
			LATEPOINT_INVOICE_STATUS_PAID           => __( 'Paid', 'latepoint-pro-features' ),
			LATEPOINT_INVOICE_STATUS_PARTIALLY_PAID => __( 'Partially Paid', 'latepoint-pro-features' ),
			LATEPOINT_INVOICE_STATUS_DRAFT          => __( 'Draft', 'latepoint-pro-features' ),
			LATEPOINT_INVOICE_STATUS_VOIDED         => __( 'Voided', 'latepoint-pro-features' ),
		];

		/**
		 * Get the list of invoice statuses
		 *
		 * @param {array} $statuses Array of invoice statuses
		 *
		 * @returns {array} Filtered array of invoice statuses
		 * @since 5.0.15
		 * @hook latepoint_invoices_statuses_for_select
		 *
		 */
		return apply_filters( 'latepoint_invoices_statuses_for_select', $statuses );
	}

	public static function list_invoices_for_order( OsOrderModel $order ) {
		$invoices = new OsInvoiceModel();
		$invoices = $invoices->where( [ 'order_id' => $order->id ] )->get_results_as_models();
		if ( OsRolesHelper::can_user( 'invoice__view' ) ) { ?>
            <div class="invoices-info-w">
                <div class="os-form-sub-header">
                    <h3><?php esc_html_e( 'Invoices', 'latepoint-pro-features' ); ?></h3>
                </div>
				<?php if ( $invoices ) {
					foreach ( $invoices as $invoice ) {
						echo '<div class="os-invoice-wrapper" data-route="' . esc_attr( OsRouterHelper::build_route_name( 'invoices', 'view' ) ) . '" data-invoice-id="' . esc_attr( $invoice->id ) . '">';
						echo '<div class="quick-invoice-head">
                                <div class="quick-invoice-icon"><i class="latepoint-icon latepoint-icon-file-text"></i></div>
                                <div class="quick-invoice-amount">' . OsMoneyHelper::format_price( $invoice->charge_amount, true, false ) . '</div>
                                <div class="lp-invoice-status lp-invoice-status-' . $invoice->status . '">' . self::readable_status( $invoice->status ) . '</div>
                              </div>
                              <div class="quick-invoice-sub">
                                <div class="lp-invoice-number"><span>' . esc_html__( 'Invoice Number:', 'latepoint-pro-features' ) . '</span> <strong>' . esc_html( $invoice->invoice_number ) . '</strong></div>
                                <div class="lp-invoice-date">' . esc_html( OsTimeHelper::get_readable_date( new OsWpDateTime( $invoice->created_at ) ) ) . '</div>
                              </div>';
						echo '</div>';
					}
				}
				?>

                  <?php if(OsRolesHelper::can_user( 'invoice__create' ) ) { ?>
                      <div class="quick-add-item-button"
                           data-os-after-call="latepoint_init_quick_invoice_form"
                           data-os-before-after="before"
                           data-os-action="<?php echo esc_attr( OsRouterHelper::build_route_name( 'invoices', 'edit_form' ) ); ?>">
                          <i class="latepoint-icon latepoint-icon-plus2"></i>
                          <span><?php esc_html_e( 'New Invoice', 'latepoint' ); ?></span>
                      </div>
                  <?php } ?>
            </div>
			<?php
		}
	}


	public static function create_invoice_for_new_order( OsOrderModel $order ) {
		$invoice           = new OsInvoiceModel();
		$invoice->order_id = $order->id;

		$data = [
			'company'         => OsSettingsHelper::get_settings_value( 'invoice_company', '' ),
			'from'            => OsSettingsHelper::get_settings_value( 'invoice_from', '' ),
			'to'              => '',
			'price_breakdown' => '',
			'totals'          => ''
		];

		$customer           = $order->get_customer();
		$to                 = [];
		$customer_full_name = $customer->full_name; // need because it's a magic method which will return empty on empty check
		if ( ! empty( $customer_full_name ) ) {
			$to[] = $customer_full_name;
		}
		// TODO replace with actual logic
		if ( true ) {
			$to[] = '18 Mission street<br>New York, NY 10027<br>United States';
		}
		if ( ! empty( $customer->email ) ) {
			$to[] = $customer->email;
		}
		if ( ! empty( $customer->phone ) ) {
			$to[] = $customer->phone;
		}

		$data['to'] = implode( '<br>', $to );

		$data['price_breakdown'] = json_decode( $order->price_breakdown );
		$data['totals']          = [
			'subtotal' => $order->get_subtotal(),
			'total'    => $order->get_total(),
		];

		$invoice->data = json_encode( $data );

        if($order->get_payment_data_value('portion') == LATEPOINT_PAYMENT_PORTION_DEPOSIT){
            $invoice_for_remaining_balance = clone $invoice;
            $invoice->charge_amount = $order->get_payment_data_value('initial_charge_amount');
            $invoice->payment_portion = LATEPOINT_PAYMENT_PORTION_DEPOSIT;
            $invoice_for_remaining_balance->charge_amount = $order->get_total() - $invoice->charge_amount;
            $invoice_for_remaining_balance->payment_portion = LATEPOINT_PAYMENT_PORTION_REMAINING;
        }elseif($order->get_payment_data_value('portion') == LATEPOINT_PAYMENT_PORTION_FULL){
            $invoice->payment_portion = LATEPOINT_PAYMENT_PORTION_FULL;
			$invoice->charge_amount = $order->get_total();
        }

		$total_paid = $order->get_total_amount_paid_from_transactions();
		if ( $total_paid == $invoice->charge_amount ) {
			// since the order has just been created - any transactions that were made - are part of the time of creation, so should be on the invoice
			$invoice->status        = LATEPOINT_INVOICE_STATUS_PAID;
		}

		if ( $invoice->save() ) {
			/**
			 * Invoice was created
			 *
			 * @param {OsInvoiceModel} $invoice instance of invoice model that was created
			 *
			 * @since 5.0.15
			 * @hook latepoint_invoice_created
			 *
			 */
			do_action( 'latepoint_invoice_created', $invoice );
            // TODO add setting field to enable this
            if(OsSettingsHelper::get_settings_value('create_invoice_for_remaining_balance_if_deposit_paid') && isset($invoice_for_remaining_balance)){
                $invoice_for_remaining_balance->save();
                /**
                 * Invoice was created
                 *
                 * @param {OsInvoiceModel} $invoice instance of invoice model that was created
                 *
                 * @since 5.0.15
                 * @hook latepoint_invoice_created
                 *
                 */
                do_action( 'latepoint_invoice_created', $invoice_for_remaining_balance );
            }
		}
	}
}