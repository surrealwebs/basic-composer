<?php
/**
 * Template part
 *
 * @package Surrealwebs\Composer
 */

/**
 * Block arguments passed into the template.
 *
 * @var array $args
 */
$block_attributes = $args['attributes'];
$block_content    = $args['content'];
$block_context    = $args['block'];
$class            = 'c-block-accordion-item';

if ( isset( $block_context->context['surrealwebs-composer-blocks/accordion/openFirstItem'] ) && 1 === intval( $block_context->context['surrealwebs-composer-blocks/accordion/openFirstItem'] ) && 0 === $args['attributes']['id'] ) {
	$class .= ' c-block-accordion-item--active';
}
?>
<div class="<?php echo esc_attr( $class ); ?>">
	<div class="c-block-accordion-item__header">
		<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="c-block-accordion-item__arrow">
			<path d="M13.8983 5.64407L13.3831 5.10169C13.2475 4.9661 13.0305 4.9661 12.922 5.10169L8.01356 10.0102L3.07797 5.10169C2.96949 4.9661 2.75254 4.9661 2.61695 5.10169L2.10169 5.64407C1.9661 5.75254 1.9661 5.96949 2.10169 6.10508L7.76949 11.7729C7.90508 11.9085 8.09492 11.9085 8.23051 11.7729L13.8983 6.10508C14.0339 5.96949 14.0339 5.75254 13.8983 5.64407Z" fill="#005FB9"/>
		</svg>
		<div class="c-block-accordion-item__title"><?php echo esc_html( $args['attributes']['title'] ); ?></div>
		<p class="c-block-accordion-item__sub-title"><?php echo esc_html( $args['attributes']['subTitle'] ); ?></p>
	</div>
	<div class="c-block-accordion-item__content">
		<?php echo wp_kses_post( $block_content ); ?>
	</div>
</div>
<?php
