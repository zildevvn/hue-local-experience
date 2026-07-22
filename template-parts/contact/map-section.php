<?php
$address = get_field('address', 'option');

if ( ! empty( $address ) ) :
    $map_url = add_query_arg(
        array(
            'q' => urlencode( $address ),
            'output' => 'embed',
        ),
        'https://maps.google.com/maps'
    );
?>
<section class="hle-section map-section">
    <div class="map-section__container container">
        <div class="map-section__embed">
            <iframe 
                src="<?php echo esc_url( $map_url ); ?>" 
                title="<?php echo esc_attr__( 'Google Map Location', 'hue-local-experience' ); ?>"
                width="100%" 
                height="100%" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>
</section>
<?php endif; ?>