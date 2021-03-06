<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2018 Belavier Commerce LLC

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
 * Abstract cache driver class
 *
 * @since  1.2.7
 */
class ACacheDriver
{

    /**
     * @var string  key
     * @since  1.2.7
     */
    protected $key;

    /**
     * @var int Now time
     * @since  1.2.7
     */
    public $now;

    /**
     * @var integer, cache lifetime
     * @since  1.2.7
     */
    protected $expire;

    /**
     * @var int  Lock period (0 - no lock)
     * @since  1.2.7
     */
    public $lock_time;

    /**
     * Base Constructor
     *
     * @param int $expiration
     * @param int $lock_time
     *
     * @since   1.2.7
     */
    public function __construct($expiration, $lock_time = 0)
    {

        //expiration is default to 1 day
        $this->expire = ($expiration) ? $expiration : 86400;
        $this->lock_time = ($lock_time) ? $lock_time : 0;
        $this->now = time();

    }

    /**
     * Set cache expiration time
     *
     * @param   int $expire_time expiration time
     *
     * @return  true
     *
     * @since   1.2.7
     */
    public function setExpiration($expire_time)
    {
        $this->expire = $expire_time;

        return false;
    }

    /**
     * Get all cached data
     *     *
     *
     * @return  mixed  Boolean  false on failure or a cached data object
     *
     * @since   1.2.7
     */
    public function getAll()
    {
        return false;
    }

    /**
     * Get unique hashed cache key string from an key/group pair
     *
     * @param   string $key   The cache data key
     * @param   string $group The cache data group
     *
     * @return  string
     *
     * @since   1.2.7
     */
    protected function hashCacheKey($key, $group)
    {
        return md5($group.'-'.$key);
    }

}