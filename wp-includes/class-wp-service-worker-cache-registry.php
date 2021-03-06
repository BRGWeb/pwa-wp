<?php
/**
 * WP_Service_Worker_Cache_Registry class.
 *
 * @package PWA
 */

/**
 * Class used to register service worker behavior.
 *
 * @since 0.2
 */
class WP_Service_Worker_Cache_Registry {

	/**
	 * Stale while revalidate caching strategy.
	 *
	 * @var string
	 * @since 0.2
	 */
	const STRATEGY_STALE_WHILE_REVALIDATE = 'staleWhileRevalidate';

	/**
	 * Cache first caching strategy.
	 *
	 * @var string
	 * @since 0.2
	 */
	const STRATEGY_CACHE_FIRST = 'cacheFirst';

	/**
	 * Network first caching strategy.
	 *
	 * @var string
	 * @since 0.2
	 */
	const STRATEGY_NETWORK_FIRST = 'networkFirst';

	/**
	 * Cache only caching strategy.
	 *
	 * @var string
	 * @since 0.2
	 */
	const STRATEGY_CACHE_ONLY = 'cacheOnly';

	/**
	 * Network only caching strategy.
	 *
	 * @var string
	 * @since 0.2
	 */
	const STRATEGY_NETWORK_ONLY = 'networkOnly';

	/**
	 * Name of 'precache' cache.
	 *
	 * This will be replaced with `wp.serviceWorker.core.cacheNames.precache` in the service worker JavaScript.
	 *
	 * @var string
	 * @since 0.2
	 */
	const PRECACHE_CACHE_NAME = 'precache';

	/**
	 * Name of 'runtime' cache.
	 *
	 * This will be replaced with `wp.serviceWorker.core.cacheNames.runtime` in the service worker JavaScript.
	 *
	 * @var string
	 * @since 0.2
	 */
	const RUNTIME_CACHE_NAME = 'runtime';

	/**
	 * Registered caching routes and scripts.
	 *
	 * @var array
	 * @since 0.2
	 */
	protected $registered_caching_routes = array();

	/**
	 * Registered routes and files for precaching.
	 *
	 * @var array
	 * @since 0.2
	 */
	protected $registered_precaching_routes = array();

	/**
	 * Register route and caching strategy.
	 *
	 * @since 0.2
	 *
	 * @param string $route    Route regular expression, without delimiters.
	 * @param string $strategy Strategy, can be WP_Service_Worker_Cache_Registry::STRATEGY_STALE_WHILE_REVALIDATE, WP_Service_Worker_Cache_Registry::STRATEGY_CACHE_FIRST,
	 *                         WP_Service_Worker_Cache_Registry::STRATEGY_NETWORK_FIRST, WP_Service_Worker_Cache_Registry::STRATEGY_CACHE_ONLY,
	 *                         WP_Service_Worker_Cache_Registry::STRATEGY_NETWORK_ONLY.
	 * @param array  $strategy_args {
	 *     An array of strategy arguments.
	 *
	 *     @type string $cache_name Cache name. Optional.
	 *     @type array  $plugins    Array of plugins with configuration. The key of each plugin in the array must match the plugin's name.
	 *                              See https://developers.google.com/web/tools/workbox/guides/using-plugins#workbox_plugins.
	 * }
	 */
	public function register_cached_route( $route, $strategy, $strategy_args = array() ) {

		$valid_strategies = array(
			self::STRATEGY_STALE_WHILE_REVALIDATE,
			self::STRATEGY_CACHE_FIRST,
			self::STRATEGY_CACHE_ONLY,
			self::STRATEGY_NETWORK_FIRST,
			self::STRATEGY_NETWORK_ONLY,
		);

		if ( ! in_array( $strategy, $valid_strategies, true ) ) {
			_doing_it_wrong(
				__METHOD__,
				sprintf(
					/* translators: %s is a comma-separated list of valid strategies */
					esc_html__( 'Strategy must be one out of %s.', 'pwa' ),
					esc_html( implode( ', ', $valid_strategies ) )
				),
				'0.2'
			);
			return;
		}

		if ( ! is_string( $route ) ) {
			_doing_it_wrong(
				__METHOD__,
				sprintf(
					/* translators: %s is caching strategy */
					esc_html__( 'Route for the caching strategy %s must be a string.', 'pwa' ),
					esc_html( $strategy )
				),
				'0.2'
			);
			return;
		}

		$this->registered_caching_routes[] = array(
			'route'         => $route,
			'strategy'      => $strategy,
			'strategy_args' => $strategy_args,
		);
	}

	/**
	 * Register precached route.
	 *
	 * The routes registered here are served with the cache-first strategy.
	 *
	 * @since 0.2
	 * @see WP_Service_Worker_Cache_Registry::register_cached_route()
	 *
	 * @param string       $url URL to cache.
	 * @param array|string $options {
	 *     Options. If a string, then this is the revision.
	 *
	 *     @type string $revision Revision. Optional.
	 * }
	 */
	public function register_precached_route( $url, $options = array() ) {
		if ( ! is_array( $options ) ) {
			$options = array(
				'revision' => $options,
			);
		}

		$defaults = array(
			'revision' => null,
		);

		$options = array_merge( $defaults, $options );
		foreach ( array_diff( array_keys( $options ), array_keys( $defaults ) ) as $unrecognized_key ) {
			/* translators: %1$s is the unrecognized option key, %2$s is the precached route */
			_doing_it_wrong( __METHOD__, esc_html( sprintf( __( 'An unrecognized option "%1$s" was provided for a precached route %2$s.', 'pwa' ), $unrecognized_key, $url ) ), '0.2' );
		}

		$this->registered_precaching_routes[] = array_merge( $options, compact( 'url' ) );
	}

	/**
	 * Gets all registered cached routes.
	 *
	 * @since 0.2
	 *
	 * @return array List of cached routes and their data.
	 */
	public function get_cached_routes() {
		return $this->registered_caching_routes;
	}

	/**
	 * Gets all registered precached routes.
	 *
	 * @since 0.2
	 *
	 * @return array List of precached routes and their data.
	 */
	public function get_precached_routes() {
		return $this->registered_precaching_routes;
	}
}
