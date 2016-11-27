<?php
$error_msg_prefix = __( 'Brochure Tools product information shortcode error: ',  NARNOO_BROCHURE_TOOLS_I18N_DOMAIN );

extract( shortcode_atts( array(
	'operator' 	=> '', 	// Product ID [required]
	'region'	=> '',	// [required]
	'images' 	=> 8,
	'english'	=> 'yes',
), $atts ) );

//Check for required attributes - If non output an error message
if( empty($operator) || empty($region)  ){

	echo $error_msg_prefix . __( 'Need to set an operator id and region', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN );
	return;
}

$request 	= Brochure_Tools_Helper::init_api('depga');
$cache	 	= Brochure_Tools_Helper::init_cache();

$response = $cache->get( 'operator_'.$operator.'_'.lcfirst( $region ).'_'.$images );



if(empty($response)){
	$response 	= $request->builder( $operator,$region,$images );

	if(!empty($response->success)){
		$cache->set('operator_'.$operator.'_'.lcfirst( $region ).'_'.$images ,$response,14400);
	}

}


/*create media gallery array
	type: string image/logo/video
	media_id: int
	media_thumb: string
	media_large: string
	media_caption: string
*/

//Set up a new gallery array to hold all our gallery elements
$gallery_array 	= array();
$ga_i 					= 0;
//Total number of images allowed is 8.
$totalImages 		= 8;
//Set up a count so we can keep track of images v's logos & videos
$img_count   		= 0;
//Check to see if we have a logo present.
if( !empty($response->logo) ){
	$totalImages--; // If yes then this logo will take one place of an image
}
//Check to see if we have a video present.
if( !empty($response->operator_video) ){
	$totalImages--; // If yes then this video will take one place of an image
}

//Start creating the new gallery array.
if( !empty($response->operator_images) ){
	$img_count = 1;
	foreach ($response->operator_images as $img) {
		if($img_count <= $totalImages){
			$gallery_array[$ga_i]['media_type']  	  = 'image';
			$gallery_array[$ga_i]['media_id']  			= $img->image_id;
			$gallery_array[$ga_i]['media_thumb']  	= $img->xcrop_image_path;
			$gallery_array[$ga_i]['media_large']  	= $img->xlarge_image_path;
			$gallery_array[$ga_i]['media_caption']  = $img->image_caption;
			$gallery_array[$ga_i]['download_link']  = $img->download_image_file;
			$ga_i++;
			$img_count++;
		}

	}
}

if( !empty($response->logo) ){
	$gallery_array[$ga_i]['media_type']  	  = 'logo';
	$gallery_array[$ga_i]['media_id']  			= $response->logo->logo_id;
	$gallery_array[$ga_i]['media_thumb']  	= $response->logo->crop_image_path;
	$gallery_array[$ga_i]['media_large']  	= $response->logo->large_image_path;
	$gallery_array[$ga_i]['media_caption']  = "";
	$gallery_array[$ga_i]['download_link']  = $response->logo->download_logo_file;
	$ga_i++;

}

if( !empty($response->operator_video) ){
	$gallery_array[$ga_i]['media_type']  	  = 'video';
	$gallery_array[$ga_i]['media_id']  			= $response->operator_video->video_id;
	$gallery_array[$ga_i]['media_thumb']  	= $response->operator_video->video_thumb_image_path;
	$gallery_array[$ga_i]['media_large']  	= $response->operator_video->video_embed_link;
	$gallery_array[$ga_i]['media_caption']  = $response->operator_video->video_caption;
	$gallery_array[$ga_i]['download_link']  = $response->operator_video->download_original_file;
	$ga_i++;
}

?>

<?php

//Lets do some error checking of the response - 1 ) No response returned

if(empty($response)){
			echo $error_msg_prefix . __( 'There has been an error requesting this information', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN );
			return;
}elseif(empty( $response->success )) {
			echo $error_msg_prefix . __( 'There are no results for this request - '.$response->message, NARNOO_BROCHURE_TOOLS_I18N_DOMAIN );
			return;
}else{
?>

<div class="heading-title-strikethrough">
	<span class="title-strikethrough">&nbsp;</span>
	<h1><?= $response->business_name; ?></h1>
</div>
<div class="narnoo-gallery-wrap">

	<ul class="narnoo-gallery gallery">

		<?php

		$i = 0;
		foreach ($gallery_array as $img) {

			$i++;
			$class = '';

			if ( 0 == ( $i - 1 ) % 4 )
				$class = ' first first-2-cols';

			if ( 0 == $i % 4 )
				$class = ' last last-2-cols';

			if ( 0 == ( $i - 1 ) % 2 && ! ( 0 == ( $i - 1 ) % 4 ) )
				$class .= ' first-2-cols';

			if ( ( 0 == $i % 2 ) && ! ( 0 == $i % 4 ) )
				$class .= ' last-2-cols';

			if ( 4 >= $i )
				$class .= ' first-row';

			if ( 2 >= $i )
				$class .= ' first-row-2-cols';

			//$requestImageDownload	= Brochure_Tools_Helper::init_api('operator');
			//$responseImageDownload	= $requestImageDownload->downloadImage( $response->narnoo_id, $img->image_id );

		?>

			<li class="narnoo-img-wrap<?php echo $class; ?>">
				<div class="narnoo-img">
					<img class="narnoo-thumbnail image-id-<?php echo $i ?>" src="<?php echo $img['media_thumb']; ?>" width="300" height="238" title="<?php echo $img['media_caption']; ?>" data-map-id="<?php echo $img['media_id']; ?>" alt="Map">
					<div class="narnoo-img-cover">
						<span class="narnoo-link-container">
							<span class="narnoo-link-wrap gallery-item">
								<a class="narnoo-link narnoo-highres link-id-<?php echo $i ?>" href="<?php echo $img['media_large']; ?>">
									<i class="fa fa-search-plus" aria-hidden="true"></i>
									View
								</a>
							</span>
							<span class="narnoo-link-wrap">

								<a class="narnoo-link narnoo-download link-id-<?php echo $i ?>" href="<?php echo $img['download_link']; ?>">
									<i class="fa fa-download" aria-hidden="true"></i>
									Download
								</a>

							</span>
						</span>
					</div>
				</div>
				<div class="narnoo-caption"><?php echo $img['media_caption']; ?></div>
			</li>

		<?php } ?>

	</ul>

</div>

<h2><?php echo __( 'Product Details', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN ); ?></h2>
<div class="narnoo-pd-table">
	<div class="narnoo-pd-row">
		<div class="narnoo-pd-col-1"><strong><?php echo __( 'Business Name:', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN ); ?></strong></div>
		<div class="narnoo-pd-col-2"><?php echo $response->business_name; ?> </div>
	</div>
	<div class="narnoo-pd-row">
		<div class="narnoo-pd-col-1"><strong><?php echo __( 'Product Name:', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN ); ?></strong></div>
		<div class="narnoo-pd-col-2"><?php echo $response->product_name; ?> </div>
	</div>
	<div class="narnoo-pd-row">
		<div class="narnoo-pd-col-1"><strong><?php echo __( 'Product Location:', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN ); ?></strong></div>
		<div class="narnoo-pd-col-2"><?php echo $response->business_location; ?> </div>
	</div>
	<div class="narnoo-pd-row">
		<div class="narnoo-pd-col-1"><strong><?php echo __( 'Web Address:', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN ); ?></strong></div>
		<div class="narnoo-pd-col-2"><a href="<?php echo $response->business_url; ?>" target="_blank"><?php echo $response->business_url; ?></a></div>
	</div>
	<div class="narnoo-pd-row">
		<div class="narnoo-pd-col-1"><strong><?php echo __( 'Contact Person:', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN ); ?></strong></div>
		<div class="narnoo-pd-col-2"><?php echo $response->business_contact; ?></div>
	</div>
	<div class="narnoo-pd-row">
		<div class="narnoo-pd-col-1"><strong><?php echo __( 'Position:', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN ); ?></strong></div>
		<div class="narnoo-pd-col-2"><?php echo $response->contact_title; ?></div>
	</div>
	<div class="narnoo-pd-row">
		<div class="narnoo-pd-col-1"><strong><?php echo __( 'Email:', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN ); ?></strong></div>
		<div class="narnoo-pd-col-2"><a href="mailto:<?php echo $response->business_email; ?>" target="_blank"><?php echo $response->business_email; ?></a></div>
	</div>
	<div class="narnoo-pd-row">
		<div class="narnoo-pd-col-1"><strong><?php echo __( 'Telephone:', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN ); ?></strong></div>
		<div class="narnoo-pd-col-2"><?php echo $response->business_phone; ?></div>
	</div>
	<div class="narnoo-pd-row">
		<div class="narnoo-pd-col-1"><strong><?php echo __( 'Postal Address:', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN ); ?></strong></div>
		<div class="narnoo-pd-col-2"><?php echo $response->business_postal_address; ?></div>
	</div>
</div>

<div>
<h2><?php echo __( 'Company Biography', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN ); ?></h2>
<?php if(lcfirst( $region ) == 'china' && 'yes' != $english){ ?>

  <p> <?php echo $response->operator_company_biography->chinese->large->text; ?></p>

  <?php }elseif(lcfirst( $region ) == 'japan' && 'yes' != $english){ ?>

  <p> <?php echo $response->operator_company_biography->japanese->large->text; ?></p>

  <?php }else{ ?>

  <p> <?php echo $response->operator_company_biography->english->large->text; ?></p>

  <?php } ?>
</div>
<div>
<h2><?php echo __( 'Product Description - Large Description', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN ); ?></h2>
<?php if(lcfirst( $region ) == 'china' && 'yes' != $english){ ?>

  <p> <?php echo $response->operator_biography->chinese->small->text; ?></p>

  <?php }elseif(lcfirst( $region ) == 'japan' && 'yes' != $english){ ?>

  <p> <?php echo $response->operator_biography->japanese->small->text; ?></p>

  <?php }else{ ?>

  <p> <?php echo $response->operator_biography->english->small->text; ?></p>

  <?php } ?>
</div>
<div>
<h2><?php echo __( 'Product Description - Large Description', NARNOO_BROCHURE_TOOLS_I18N_DOMAIN ); ?></h2>
<?php if(lcfirst( $region ) == 'china' && 'yes' != $english){ ?>

  <p> <?php echo $response->operator_biography->chinese->large->text; ?></p>

  <?php }elseif(lcfirst( $region ) == 'japan' && 'yes' != $english){ ?>

  <p> <?php echo $response->operator_biography->japanese->large->text; ?></p>

  <?php }else{ ?>

  <p> <?php echo $response->operator_biography->english->large->text; ?></p>

  <?php } ?>
</div>
<?
}
