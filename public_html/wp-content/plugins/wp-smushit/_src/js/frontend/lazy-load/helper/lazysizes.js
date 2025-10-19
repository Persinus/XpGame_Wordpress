import lazySizes from 'lazysizes';

/*
 * TODO: Change the lazyClass name to be more specific to avoid conflicts with other plugins
 * in the case that they are using the same default class name.
 * @see https://github.com/aFarkas/lazysizes/issues/643#issuecomment-486168297
 * or https://github.com/aFarkas/lazysizes/issues/647#issuecomment-487724519
 */

export const isSmushLazySizesInstance = ( instance ) => {
	return instance === lazySizes ||
        ( JSON.stringify( instance?.cfg || {} ) === JSON.stringify( lazySizes?.cfg || {} ) );
};

export default lazySizes;
