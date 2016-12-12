<?php
$error_msg_prefix = __( 'Brochure Tools maps shortcode error: ',  NARNOO_BROCHURE_TOOLS_I18N_DOMAIN );

extract( shortcode_atts( array(
	'region' 	=> '', 	// Country Name [required]
	'location' 	=> '', 	// Location Name [required]
	'number' 	=> '10' // Map number [required]
), $atts ) );

//Check for required attributes - If non output an error message
if( empty($location ) ){

	echo $error_msg_prefix . __( 'Need to set a location', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN );
	return;
}

$request 	= Brochure_Tools_Helper::init_api('depga');
$cache	 	= Brochure_Tools_Helper::init_cache();

if( empty($region)){
	$region = NULL;
	$response = $cache->get('maps_'.lcfirst( $location ).'_'.$number);
}else{
	$response = $cache->get('maps_'.lcfirst( $region ).'_'.lcfirst( $location ).'_'.$number);

}


if(empty($response)){
	$response 	= $request->getMaps( $region,$location,$number );

	if(!empty($response->success)){

		if( empty($region)){
			$cache->set('maps_'.lcfirst( $location ).'_'.$number,$response,14400);
		}else{
			$cache->set('maps_'.lcfirst( $region ).'_'.lcfirst( $location ).'_'.$number,$response,14400);
		}

	}
}


?>


<?php

//Lets do some error checking of the response - 1 ) No response returned 2 ) No results returned.

if(empty($response)){
			echo $error_msg_prefix . __( 'There has been an error requesting this information', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN );
			return;
}elseif(empty( $response->success )) {
			echo $error_msg_prefix . __( 'There are no results for this request - '.$response->message, NARNOO_BROCHURE_TOOLS_I18N_DOMAIN );
			return;
}else{

?>

<div class="narnoo-gallery-wrap">

	<ul class="narnoo-gallery">

		<?php

		$i = 0;
		foreach ($response->maps as $map) {

			$i++;
			$class = '';

			if ( 0 == ( $i - 1 ) % 3 )
				$class = ' first';

			if ( 0 == $i % 3 )
				$class = ' last';

			if ( 3 >= $i )
				$class .= ' first-row';

			if ( 0 == ( $i - 1 ) % 2 )
				$class .= ' first-2-cols';

			if ( 0 == $i % 2 )
				$class .= ' last-2-cols';

			if ( 2 >= $i )
				$class .= ' first-row-2-cols';

			$requestMapDownload 	= Brochure_Tools_Helper::init_api('distributor');
			$responseMapDownload 	= $requestMapDownload->downloadBrochure( $map->brochure_id );

		?>

			<li class="narnoo-img-wrap<?php echo $class; ?>">
				<div class="narnoo-img">
					<img class="narnoo-thumbnail image-id-<?php echo $i ?>" src="<?php echo $map->xcrop_image_path; ?>" width="300" height="238" title="<?php echo $map->caption; ?>" data-map-id="<?php echo $map->brochure_id; ?>" alt="Map">
					<div class="narnoo-img-cover">
						<span class="narnoo-link-container">
							<span class="narnoo-link-wrap">
								<a class="narnoo-link narnoo-highres link-id-<?php echo $i ?>" href="<?php echo $map->preview_pages[0]; ?>">
									<i class="fa fa-search-plus" aria-hidden="true"></i>
									View
								</a>
							</span>
							<span class="narnoo-link-wrap">
							<?php
							//Lets do some error checking of the response - 1 ) No response returned
							if(empty($responseMapDownload)){
								echo $error_msg_prefix . __( 'There has been an error requesting this information', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN );
							}else{
							?>
								<a class="narnoo-link narnoo-download link-id-<?php echo $i ?>" href="<?php echo $responseMapDownload->download_brochure_file; ?>">
									<i class="fa fa-download" aria-hidden="true"></i>
									Download
								</a>
							<?php
							}
							?>
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
