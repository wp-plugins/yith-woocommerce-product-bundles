<?php
/**
 * Admin Add Bundled Product markup.
 * @version 4.8.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$item_data = !empty( $item_data ) ? $item_data : array();

?><div class="yith-wcpb-bundled-item wc-metabox <?php echo $open_closed ?>" rel="<?php echo $metabox_id; ?>">
	<h3>
		<button type="button" class="yith-wcpb-remove-bundled-product-item button"><?php echo __( 'Remove', 'woocommerce' ); ?></button>
		<div class="handlediv" title="<?php echo __( 'Click to toggle', 'woocommerce' ); ?>"></div>
		<strong class="item-title"><?php echo $title . ' &ndash; #'. $product_id; ?></strong>
	</h3>
	<div class="yith-wcpb-bundled-item-data wc-metabox-content">
		<div class="yith-wcpb-bundled-item-data-content">
			<input type="hidden" name="_yith_wcpb_bundle_data[<?php echo $metabox_id; ?>][bundle_order]" class="yith-wcpb-bundled-item-position" value="<?php echo $metabox_id; ?>" />
			<input type="hidden" name="_yith_wcpb_bundle_data[<?php echo $metabox_id; ?>][product_id]" class="yith-wcpb-product-id" value="<?php echo $product_id; ?>" />
			<?php do_action( 'yith_wcpb_admin_product_bundle_data', $metabox_id, $product_id, $item_data, $post_id ); ?>
		</div>
	</div>
</div>
