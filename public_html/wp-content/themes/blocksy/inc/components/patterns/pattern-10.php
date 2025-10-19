<?php


return [
	'title' => __( 'Blocksy - Testimonial', 'blocksy' ),
	'categories' => ['reviews', 'blocksy'],
	
	'content' => '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"7rem","bottom":"7rem"}}},"backgroundColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-white-background-color has-background" style="padding-top:7rem;padding-bottom:7rem"><!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|70","left":"var:preset|spacing|80"}}}} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"60%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:60%"><!-- wp:image {"id":7895,"width":"200px","height":"auto","sizeSlug":"full","linkDestination":"none","style":{"spacing":{"margin":{"bottom":"50px"}}}} -->
<figure class="wp-block-image size-full is-resized" style="margin-bottom:50px"><img src="' . trailingslashit( get_template_directory_uri() ) . 'inc/components/patterns/assets/pattern-client-5.svg" alt="" class="wp-image-7895" style="width:200px;height:auto"/></figure>
<!-- /wp:image -->

<!-- wp:heading {"style":{"elements":{"link":{"color":{"text":"var:preset|color|black"}}},"spacing":{"margin":{"top":"50px","bottom":"50px"},"padding":{"top":"50px","bottom":"50px"}},"border":{"top":{"color":"#0000001f","style":"solid","width":"1px"},"right":{},"bottom":{"color":"#0000001f","style":"solid","width":"1px"},"left":{}}},"textColor":"black"} -->
<h2 class="wp-block-heading has-black-color has-text-color has-link-color" style="border-top-color:#0000001f;border-top-style:solid;border-top-width:1px;border-bottom-color:#0000001f;border-bottom-style:solid;border-bottom-width:1px;margin-top:50px;margin-bottom:50px;padding-top:50px;padding-bottom:50px"><em>"Massa vitae tortor condimentum lacinia quis vel eros donec felis eget velit aliquet sagittis id consectetur eget nulla facilisi etiam dignissim diam quis enim lobortis scelerisque."</em></h2>
<!-- /wp:heading -->

<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group"><!-- wp:image {"id":7833,"width":"70px","sizeSlug":"full","linkDestination":"none","style":{"border":{"radius":"100%"}}} -->
<figure class="wp-block-image size-full is-resized has-custom-border"><img src="' . trailingslashit( get_template_directory_uri() ) . 'inc/components/patterns/assets/pattern-avatar-2.webp" alt="" class="wp-image-7833" style="border-radius:100%;width:70px"/></figure>
<!-- /wp:image -->

<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:heading {"level":5,"style":{"elements":{"link":{"color":{"text":"var:preset|color|black"}}},"spacing":{"margin":{"top":"0px","bottom":"5px"}},"typography":{"fontSize":"18px"}},"textColor":"black"} -->
<h5 class="wp-block-heading has-black-color has-text-color has-link-color" style="margin-top:0px;margin-bottom:5px;font-size:18px">Michael Anderson</h5>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"500"},"elements":{"link":{"color":{"text":"#0000009c"}}},"color":{"text":"#0000009c"}}} -->
<p class="has-text-color has-link-color" style="color:#0000009c;font-style:normal;font-weight:500"><em>Chief Investment Officer</em></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:image {"id":7640,"aspectRatio":"3/4","scale":"cover","sizeSlug":"full","linkDestination":"none","style":{"border":{"radius":"20px"}}} -->
<figure class="wp-block-image size-full has-custom-border"><img src="' . trailingslashit( get_template_directory_uri() ) . 'inc/components/patterns/assets/pattern-image-3.webp" alt="" class="wp-image-7640" style="border-radius:20px;aspect-ratio:3/4;object-fit:cover"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->'
];