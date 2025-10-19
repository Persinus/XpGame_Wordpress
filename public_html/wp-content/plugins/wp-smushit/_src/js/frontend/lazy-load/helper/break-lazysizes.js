/**
 * @see https://github.com/aFarkas/lazysizes/issues/643#issuecomment-486168297
 * Or https://github.com/aFarkas/lazysizes/issues/647#issuecomment-487724519
 */
const originalLazySizesConfig = window.lazySizesConfig || null;
if ( originalLazySizesConfig ) {
	delete window.lazySizesConfig;
}

export default () => {
	// Restore the original lazySizesConfig if it was set before.
	if ( originalLazySizesConfig ) {
		window.lazySizesConfig = originalLazySizesConfig;
	} else if ( 'lazySizesConfig' in window ) {
		delete window.lazySizesConfig;
	}
};
