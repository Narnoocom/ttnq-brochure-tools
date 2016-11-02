<?php
$error_msg_prefix = __( 'Brochure Tools product information shortcode error: ',  NARNOO_BROCHURE_TOOLS_I18N_DOMAIN );

extract( shortcode_atts( array(
	'operator' 	=> '', 	// Operator ID [required]
	'region'	=> '',	// [required]
	'images' 	=> 8
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

<h1><?= $response->business_name; ?></h1>

<div class="narnoo-gallery-wrap">

	<ul class="narnoo-gallery gallery">

		<?php

		$i = 0;
	  foreach ($gallery_array as $img) {
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
					<img class="narnoo-thumbnail image-id-<?php echo $i ?>" src="<?php echo $img['media_thumb']; ?>" width="300" height="238" title="<?php echo $img['media_caption']; ?>" data-map-id="<?php echo $img['media_id']; ?>" alt="Map">
					<div class="narnoo-img-cover">
						<span class="narnoo-link-container">
							<span class="narnoo-link-wrap gallery-item">
								<a class="narnoo-link narnoo-highres link-id-<?php echo $i ?>" href="<?php echo $img['media_large']; ?>" data-featherlight="image">
									View
								</a>
							</span>
							<span class="narnoo-link-wrap">
								<a class="narnoo-link narnoo-download link-id-<?php echo $i ?>" href="<?php echo $img['download_link']; ?>">
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
<h2>Product Details</h2>
<div>
<table>
	<tr>
		<td><strong>Business Name:</strong></td>
		<td><?= $response->business_name; ?> </td>
	</tr>
	<tr>
		<td><strong>Product Name:</strong></td>
		<td><?= $response->product_name; ?> </td>
	</tr>
	<tr>
		<td><strong>Product Location:</strong></td>
		<td><?= $response->business_location; ?> </td>
	</tr>
	<tr>
		<td><strong>Web Address:</strong></td>
		<td><?= $response->business_url; ?></td>
	</tr>
	<tr>
		<td><strong>Contact Person:</strong></td>
		<td> <?= $response->business_contact; ?></td>
	</tr>
	<tr>
		<td><strong>Contact Title:</strong></td>
		<td> <?= $response->contact_title; ?></td>
	</tr>
	<tr>
		<td><strong>Email:</strong></td>
		<td> <?= $response->business_email; ?></td>
	</tr>
	<tr>
		<td><strong>Telephone:</strong></td>
		<td> <?= $response->business_phone; ?></td>
	</tr>
</table>
</div>
<div>
<h2>Company Biography</h2>
<?php if(lcfirst( $region ) == 'china'){ ?>

  <p> <?php echo $response->operator_company_biography->chinese->large->text; ?></p>

  <?php }elseif(lcfirst( $region ) == 'japan'){ ?>

  <p> <?php echo $response->operator_company_biography->japanese->large->text; ?></p>

  <?php }else{ ?>

  <p> <?php echo $response->operator_company_biography->english->large->text; ?></p>

  <?php } ?>
</div>
<div>
<h2>Product Description - Small Description</h2>
<?php if(lcfirst( $region ) == 'china'){ ?>

  <p> <?php echo $response->operator_biography->chinese->small->text; ?></p>

  <?php }elseif(lcfirst( $region ) == 'japan'){ ?>

  <p> <?php echo $response->operator_biography->japanese->small->text; ?></p>

  <?php }else{ ?>

  <p> <?php echo $response->operator_biography->english->small->text; ?></p>

  <?php } ?>
</div>
<div>
<h2>Product Description - Large Description</h2>
<?php if(lcfirst( $region ) == 'china'){ ?>

  <p> <?php echo $response->operator_biography->chinese->large->text; ?></p>

  <?php }elseif(lcfirst( $region ) == 'japan'){ ?>

  <p> <?php echo $response->operator_biography->japanese->large->text; ?></p>

  <?php }else{ ?>

  <p> <?php echo $response->operator_biography->english->large->text; ?></p>

  <?php } ?>
</div>
<?

//print_r($response);
}
