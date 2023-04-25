const path = require( 'path' );

/**
 * WordPress Dependencies
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config.js' );
const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );

module.exports = {
    ...defaultConfig,
	...{
        entry: {
            'close-notices-block/index': './src/js/blocks/bp-messages/close-notices-block/sitewide-notices.js',
			'sitewide-notices/index': './src/js/blocks/bp-messages/sitewide-notices/sitewide-notices.js',
        },
		output: {
			filename: '[name].js',
			path: path.join( __dirname, '..', '..', '..', '..', 'src', 'bp-messages', 'blocks' ),
		}
    },
	plugins: [
		...defaultConfig.plugins.filter(
			( plugin ) =>
				plugin.constructor.name !== 'DependencyExtractionWebpackPlugin'
		),
		new DependencyExtractionWebpackPlugin( {
			requestToExternal( request ) {
				if ( request === '@buddypress/block-components' ) {
					return [ 'bp', 'blockComponents' ];
				} else if ( request === '@buddypress/block-data' ) {
					return [ 'bp', 'blockData' ];
				} else if ( request === '@buddypress/dynamic-widget-block' ) {
					return [ 'bp', 'dynamicWidgetBlock' ];
				}
			},
			requestToHandle( request ) {
				if ( request === '@buddypress/block-components' ) {
					return 'bp-block-components';
				} else if ( request === '@buddypress/block-data' ) {
					return 'bp-block-data';
				} else if ( request === '@buddypress/dynamic-widget-block' ) {
					return 'bp-dynamic-widget-block';
				}
			}
		} )
	],
}
