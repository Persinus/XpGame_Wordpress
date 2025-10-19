import restoreLazySizesConfig from './lazy-load/helper/break-lazysizes';
import lazySizes from './lazy-load/helper/lazysizes';
require( './lazy-load/lazy-load-background-images' );
require( './lazy-load/lazy-load-video' );
require( './lazy-load/auto-resizing' );

lazySizes.init();

restoreLazySizesConfig();
