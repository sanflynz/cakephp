<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Cake\Cache;

use Cake\Core\App;
use Cake\Error;
use Cake\Utility\ObjectRegistry;

/**
 * An object registry for cache engines.
 *
 * Used by Cake\Cache\Cache to load and manage cache engines.
 *
 * @since CakePHP 3.0
 */
class CacheRegistry extends ObjectRegistry {

/**
 * Resolve a cache engine classname.
 *
 * Part of the template method for Cake\Utility\ObjectRegistry::load()
 *
 * @param string $class Partial classname to resolve.
 * @return string|false Either the correct classname or false.
 */
	protected function _resolveClassName($class) {
		if (is_object($class)) {
			return $class;
		}
		return App::classname($class, 'Cache/Engine', 'Engine');
	}

/**
 * Throws an exception when a cache engine is missing.
 *
 * Part of the template method for Cake\Utility\ObjectRegistry::load()
 *
 * @param string $class The classname that is missing.
 * @param string $plugin The plugin the cache is missing in.
 * @throws \Cake\Error\Exception
 */
	protected function _throwMissingClassError($class, $plugin) {
		throw new Error\Exception(sprintf('Cache engine %s is not available.', $class));
	}

/**
 * Create the cache engine instance.
 *
 * Part of the template method for Cake\Utility\ObjectRegistry::load()
 * @param string|CacheEngine $class The classname or object to make.
 * @param string $alias The alias of the object.
 * @param array $config An array of settings to use for the cache engine.
 * @return CacheEngine The constructed CacheEngine class.
 * @throws \Cake\Error\Exception when an object doesn't implement
 *    the correct interface.
 */
	protected function _create($class, $alias, $config) {
		if (is_object($class)) {
			$instance = $class;
		}

		unset($config['className']);
		if (!isset($instance)) {
			$instance = new $class($config);
		}

		if (!($instance instanceof CacheEngine)) {
			throw new Error\Exception(
				'Cache engines must use Cake\Cache\CacheEngine as a base class.'
			);
		}

		if (!$instance->init($config)) {
			throw new Error\Exception(
				sprintf('Cache engine %s is not properly configured.', get_class($instance))
			);
		}

		$config = $instance->config();
		if ($config['probability'] && time() % $config['probability'] === 0) {
			$instance->gc();
		}
		return $instance;
	}

/**
 * Remove a single adapter from the registry.
 *
 * @param string $name The adapter name.
 * @return void
 */
	public function unload($name) {
		unset($this->_loaded[$name]);
	}

}