<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2017 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/

namespace abc\core\cache;

if ( ! class_exists('abc\core\ABC')) {
    header('Location: static_pages/?forbidden='.basename(__FILE__));
}

/**
 * Class ACacheDriverXCache
 *
 * @package abc\cache
 */
class ACacheDriverXCache extends ACacheDriver implements ACacheDriverInterface
{
    protected $secret;

    /**
     * Constructor
     * NOTE: to use this driver define config values inside your abc/config/cache.php file
     *
     * @param array $config
     * @param int   $expiration
     * @param int   $lock_time
     */
    public function __construct(array $config, $expiration, $lock_time = 0)
    {
        $this->secret = $config['CACHE_SECRET'];
        if ( ! $lock_time) {
            $lock_time = 10;
        }
        parent::__construct($expiration, $lock_time);
    }

    /**
     * @return  boolean
     * @since   1.2.7
     */
    public function isSupported()
    {
        if (extension_loaded('xcache')) {
            // XCache Admin must be disabled for AbanteCart to use XCache
            $xcache_admin_enable_auth = ini_get('xcache.admin.enable_auth');

            // Some extensions ini variables are reported as strings
            if ($xcache_admin_enable_auth == 'Off') {
                return true;
            }

            // We require a string with contents 0, not a null value
            // because it is not set since that then defaults to On/True
            if ($xcache_admin_enable_auth === '0') {
                return true;
            }

            // In some environments empty is equivalent to Off;
            if ($xcache_admin_enable_auth === '') {
                return true;
            }
        }

        return false;
    }

    /**
     * Get cached data by key and group
     *
     * @param   string  $key          The cache data key
     * @param   string  $group        The cache data group
     * @param   boolean $check_expire True to verify cache time expiration
     *
     * @return  mixed|false Boolean false on failure or a cached data string
     *
     * @since   1.2.7
     */
    public function get($key, $group, $check_expire = true)
    {
        $cache_id = $this->getCacheId($key, $group);
        $data = xcache_get($cache_id);
        if ($data === null) {
            return false;
        }

        return $data;
    }

    /**
     * Save data to a file by key and group
     *
     * @param   string $key   The cache data key
     * @param   string $group The cache data group
     * @param   string $data  The data to store in cache
     *
     * @return  boolean
     *
     * @since   1.2.7
     */
    public function put($key, $group, $data)
    {
        $cache_id = $this->getCacheId($key, $group);

        return xcache_set($cache_id, $data, $this->expire);
    }

    /**
     * Remove a cached data file by key and group
     *
     * @param   string $key   The cache data key
     * @param   string $group The cache data group
     *
     * @return  boolean
     * @since   1.2.7
     */
    public function remove($key, $group)
    {

        $cache_id = $this->getCacheId($key, $group);
        if ( ! xcache_isset($cache_id)) {
            return true;
        }

        return xcache_unset($cache_id);
    }

    /**
     * Clean cache for a group provided.
     *
     * @param   string $group The cache data group, passed '*' indicate all cache removal
     *
     * @return  boolean
     *
     * @since   1.2.7
     */
    public function clean($group)
    {
        $group = trim($group);
        if ( ! $group) {
            return false;
        }

        $cache_info = xcache_list(XC_TYPE_VAR, 0);
        $keys = $cache_info['cache_list'];
        foreach ($keys as $key) {
            if ($group == '*' || strpos($key['name'], $this->secret.'-cache-'.$group.'.') === 0) {
                xcache_unset($key['name']);
            }
        }

        return true;
    }

    /**
     * Delete expired cache data
     *
     * @return  boolean  True on success, false otherwise.
     *
     * @since   1.2.7
     */
    public function gc()
    {
        return null;
    }

    /**
     * Lock cached item
     *
     * @param   string  $key      The cache data key
     * @param   string  $group    The cache data group
     * @param   integer $locktime Cached item max lock time
     *
     * @return  boolean
     *
     * @since   1.2.7
     */
    public function lock($key, $group, $locktime)
    {
        return null;
    }

    /**
     * Unlock cached item
     *
     * @param   string $key   The cache data key
     * @param   string $group The cache data group
     *
     * @return  boolean
     * @since   1.2.7
     */
    public function unlock($key, $group = null)
    {
        return null;
    }

    protected function getCacheId($key, $group)
    {
        return $this->secret.'-cache-'.$group.'.'.$this->hashCacheKey($key, $group);
    }
}