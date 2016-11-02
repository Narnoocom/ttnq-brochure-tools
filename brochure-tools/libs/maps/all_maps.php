<?php
$error_msg_prefix = __( 'Brochure Tools maps shortcode error: ',  NARNOO_BROCHURE_TOOLS_I18N_DOMAIN );

extract( shortcode_atts( array(
	'brochures' => '' 	// Brochure ID [required]
), $atts ) );

//Check for required attributes - If non output an error message
if( empty($brochures)  ){

	echo $error_msg_prefix . __( 'Need to list your bochure IDs', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN );
	return;
}

$request 	= Brochure_Tools_Helper::init_api('depga');
$cache	 	= Brochure_Tools_Helper::init_cache();

$response = $cache->get('all_maps' );

if(empty($response)){
	$response 	= $request->getBrochures( $brochures );

	if(!empty($response->success)){
		$cache->set('all_maps',$response,14400);
	}
}


?>


<?php

//Lets do some error checking of the response - 1 ) No response returned 2 ) No results returned.

if(empty($response)){
			echo $error_msg_prefix . __( 'There has been an error requesting this information', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN );
			return;
}else{

?>
<div class="narnoo-gallery-wrap">

	<ul class="narnoo-gallery">

	<?php foreach ($response->brochures as $map) { ?>

			<?php if(!empty( $map->success ) ) {  //Only show successful responses ?>

		 	<li class="narnoo-img">
				<a aria-hidden="true" rel="lightbox" class="narnoo-highres link-id-3" href="<?php echo $map->preview_pages[0]; ?>">
					<img class="narnoo-thumbnail image-id-2" width="400" height="300" alt="Map" src="<?php echo $map->image_800_path; ?>" title="<?php echo $map->caption; ?>" data-map-id="<?php echo $map->brochure_id; ?>">
				</a>
			</li>

			<?php } ?>

		<?php } ?>


	</ul>

</div>

<?
}
