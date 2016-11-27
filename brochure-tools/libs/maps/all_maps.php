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

		<?php

		$i = 0;
		foreach ($response->brochures as $map) {

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

			if ( ( 0 == $i % 2 ) )
				$class .= ' last-2-cols';

			if ( 2 >= $i )
				$class .= ' first-row-2-cols';

			if(!empty($map->success)) { //Only show successful responses

		?>

			<li class="narnoo-img-wrap<?php echo $class; ?>"><h2><?php echo $map->brochure_caption; ?></h2><div class="narnoo-img"><img class="narnoo-thumbnail image-id-<?php echo $i ?>" src="<?php echo $map->xcrop_image_path; ?>" width="300" height="238" title="<?php echo $map->brochure_caption; ?>" data-map-id="<?php echo $map->brochure_id; ?>" alt="Map"><div class="narnoo-img-cover"><span class="narnoo-link-container"><span class="narnoo-link-wrap"><a class="narnoo-link narnoo-highres link-id-<?php echo $i ?>" href="<?php echo $map->zoom_pages[0]; ?>"><i class="fa fa-search-plus" aria-hidden="true"></i>View</a></span><span class="narnoo-link-wrap"><a class="narnoo-link narnoo-download link-id-<?php echo $i ?>" href="<?php echo $map->file_path_to_pdf; ?>"><i class="fa fa-download" aria-hidden="true"></i>Download</a></span></span></div></div></li>

			<?php } ?>

		<?php } ?>

	</ul>

</div>

<?
}
