<?php
$error_msg_prefix = __( 'Brochure Tools image album shortcode error: ',  NARNOO_BROCHURE_TOOLS_I18N_DOMAIN );

extract( shortcode_atts( array(
	'album' 	=> '' 	// Album Name
), $atts ) );

//Check for required attributes - If non output an error message
if( empty($album)  ){

	echo $error_msg_prefix . __( 'Need to set a album id or name', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN );
	return;
}

$request 	= Brochure_Tools_Helper::init_api('depga');
$cache	 	= Brochure_Tools_Helper::init_cache();

$response = $cache->get( 'album_'.lcfirst( $album ) );

if(empty($response)){
	$response 	= $request->getAlbumImages( $album );
	// I've set the case to represent 45minutes as this equals the expiry time for the download image link

	if(!empty($response->success)){
			$cache->set('album_'.lcfirst( $album ) ,$response,2700);
	}
}


?>

<?php

//Lets do some error checking of the response - 1 ) No response returned

if(empty($response)){
			echo $error_msg_prefix . __( 'There has been an error requesting this information', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN );
			return;
}else{

?>

<div class="narnoo-gallery-wrap">

	<ul class="narnoo-gallery gallery">

		<?php

		$i = 0;
		foreach ($response->distributor_albums_images as $img) {

			$i++;
			$class = '';

			if ( 0 == ( $i - 1 ) % 4 )
				$class = ' first';

			if ( 0 == $i % 4 )
				$class = ' last';

			if ( 4 >= $i )
				$class .= ' first-row';


		?>

			<li class="narnoo-img-wrap<?php echo $class; ?>">
				<div class="narnoo-img">
					<img class="narnoo-thumbnail image-id-<?php echo $i ?>" src="<?php echo $img->xcrop_image_path; ?>" width="300" height="238" title="<?php echo $img->image_caption; ?>" data-map-id="<?php echo $img->image_id; ?>" alt="Map">
					<div class="narnoo-img-cover">
						<span class="narnoo-link-container">
							<span class="narnoo-link-wrap gallery-item">
								<a class="narnoo-link narnoo-highres link-id-<?php echo $i ?>" href="<?php echo $img->xlarge_image_path; ?>">
									View
								</a>
							</span>
							<span class="narnoo-link-wrap">
								<a class="narnoo-link narnoo-download link-id-<?php echo $i ?>" href="<?php echo $img->download_link; ?>">
									Download
								</a>
							</span>
						</span>
					</div>
				</div>
			</li>

		<?php } ?>

	</ul>

</div>

<?php

}