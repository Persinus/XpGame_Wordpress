<?php


return [
	'title' => __( 'Blocksy - Hero Section', 'blocksy' ),
	'categories' => ['intro', 'blocksy'],
	
	'content' => '<!-- wp:group {"align":"full","style":{"color":{"background":"#101010"},"spacing":{"padding":{"top":"7rem","bottom":"7rem"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-background" style="background-color:#101010;padding-top:7rem;padding-bottom:7rem"><!-- wp:columns {"verticalAlignment":"center","style":{"border":{"bottom":{"color":"#ffffff1a","width":"1px","style":"solid"},"top":[],"right":[],"left":[]},"spacing":{"margin":{"bottom":"3.7rem"},"padding":{"bottom":"4rem"},"blockGap":{"top":"var:preset|spacing|80","left":"var:preset|spacing|80"}}}} -->
<div class="wp-block-columns are-vertically-aligned-center" style="border-bottom-color:#ffffff1a;border-bottom-style:solid;border-bottom-width:1px;margin-bottom:3.7rem;padding-bottom:4rem"><!-- wp:column {"verticalAlignment":"center","width":"55%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:55%"><!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|white"}}},"spacing":{"margin":{"bottom":"15px"}},"typography":{"fontStyle":"normal","fontWeight":"500"}},"textColor":"white"} -->
<p class="has-white-color has-text-color has-link-color" style="margin-bottom:15px;font-style:normal;font-weight:500">Dolore Magna Oliqua</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"style":{"elements":{"link":{"color":{"text":"var:preset|color|white"}}},"typography":{"lineHeight":"1.2","fontStyle":"normal","fontWeight":"800"},"spacing":{"margin":{"bottom":"20px","top":"0"}}},"textColor":"white","fontSize":"xx-large"} -->
<h1 class="wp-block-heading has-white-color has-text-color has-link-color has-xx-large-font-size" id="the-perfect-theme-for-stunning-websites" style="margin-top:0;margin-bottom:20px;font-style:normal;font-weight:800;line-height:1.2">The Perfect Theme For Stunning Websites!</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|white"}}},"spacing":{"margin":{"bottom":"50px"}}},"textColor":"white","fontSize":"medium"} -->
<p class="has-white-color has-text-color has-link-color has-medium-font-size" style="margin-bottom:50px">Lorem ipsum dolor sit amet consectetur adipiscing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|50"}}},"layout":{"type":"flex","verticalAlignment":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"white","style":{"border":{"radius":"8px"},"spacing":{"padding":{"left":"25px","right":"25px","top":"15px","bottom":"15px"}},"typography":{"fontStyle":"normal","fontWeight":"600","fontSize":"18px"},"color":{"text":"#101010"},"elements":{"link":{"color":{"text":"#101010"}}}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-white-background-color has-text-color has-background has-link-color has-custom-font-size wp-element-button" style="border-radius:8px;color:#101010;padding-top:15px;padding-right:25px;padding-bottom:15px;padding-left:25px;font-size:18px;font-style:normal;font-weight:600">Click here now</a></div>
<!-- /wp:button -->

<!-- wp:button {"textColor":"white","className":"is-style-outline","style":{"border":{"radius":"8px","color":"#fefefe","width":"2px"},"spacing":{"padding":{"left":"25px","right":"25px","top":"13px","bottom":"13px"}},"typography":{"fontStyle":"normal","fontWeight":"600","fontSize":"18px"},"color":{"background":"#ffffff00"},"elements":{"link":{"color":{"text":"var:preset|color|white"}}}}} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link has-white-color has-text-color has-background has-link-color has-border-color has-custom-font-size wp-element-button" style="border-color:#fefefe;border-width:2px;border-radius:8px;background-color:#ffffff00;padding-top:13px;padding-right:25px;padding-bottom:13px;padding-left:25px;font-size:18px;font-style:normal;font-weight:600">More information</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:image {"id":7567,"aspectRatio":"1","scale":"cover","sizeSlug":"full","linkDestination":"none","style":{"border":{"radius":"25px"}}} -->
<figure class="wp-block-image size-full has-custom-border"><img src="' . trailingslashit( get_template_directory_uri() ) . 'inc/components/patterns/assets/pattern-image-1.webp" alt="" class="wp-image-7567" style="border-radius:25px;aspect-ratio:1;object-fit:cover"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|60"}},"layout":{"type":"grid","minimumColumnWidth":"16rem"}} -->
<div class="wp-block-group"><!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:heading {"textAlign":"center","style":{"elements":{"link":{"color":{"text":"var:preset|color|white"}}},"spacing":{"margin":{"bottom":"0"}},"typography":{"fontStyle":"normal","fontWeight":"900"}},"textColor":"white"} -->
<h2 class="wp-block-heading has-text-align-center has-white-color has-text-color has-link-color" id="300" style="margin-bottom:0;font-style:normal;font-weight:900">300+</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"elements":{"link":{"color":{"text":"var:preset|color|white"}}}},"textColor":"white","fontSize":"medium"} -->
<p class="has-text-align-center has-white-color has-text-color has-link-color has-medium-font-size">Excepteur sint occaecat</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:heading {"textAlign":"center","style":{"elements":{"link":{"color":{"text":"var:preset|color|white"}}},"spacing":{"margin":{"bottom":"0"}},"typography":{"fontStyle":"normal","fontWeight":"900"}},"textColor":"white"} -->
<h2 class="wp-block-heading has-text-align-center has-white-color has-text-color has-link-color" id="300" style="margin-bottom:0;font-style:normal;font-weight:900">17k</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"elements":{"link":{"color":{"text":"var:preset|color|white"}}}},"textColor":"white","fontSize":"medium"} -->
<p class="has-text-align-center has-white-color has-text-color has-link-color has-medium-font-size">Commodo consequat</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:heading {"textAlign":"center","style":{"elements":{"link":{"color":{"text":"var:preset|color|white"}}},"spacing":{"margin":{"bottom":"0"}},"typography":{"fontStyle":"normal","fontWeight":"900"}},"textColor":"white"} -->
<h2 class="wp-block-heading has-text-align-center has-white-color has-text-color has-link-color" id="300" style="margin-bottom:0;font-style:normal;font-weight:900">83%</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"elements":{"link":{"color":{"text":"var:preset|color|white"}}}},"textColor":"white","fontSize":"medium"} -->
<p class="has-text-align-center has-white-color has-text-color has-link-color has-medium-font-size">Aute birure dolor</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:heading {"textAlign":"center","style":{"elements":{"link":{"color":{"text":"var:preset|color|white"}}},"spacing":{"margin":{"bottom":"0"}},"typography":{"fontStyle":"normal","fontWeight":"900"}},"textColor":"white"} -->
<h2 class="wp-block-heading has-text-align-center has-white-color has-text-color has-link-color" id="300" style="margin-bottom:0;font-style:normal;font-weight:900">125+</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"elements":{"link":{"color":{"text":"var:preset|color|white"}}}},"textColor":"white","fontSize":"medium"} -->
<p class="has-text-align-center has-white-color has-text-color has-link-color has-medium-font-size">Officia deserunt molit</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->'
];