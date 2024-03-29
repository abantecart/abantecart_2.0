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

namespace abc\core\helper;

use abc\core\ABC;
use abc\core\engine\Registry;
use abc\core\lib\AError;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class AHelperSystemCheck extends AHelper
{
    /**
     * Main driver for running system check
     *
     * @since 1.2.4
     *
     * @param  Registry $registry
     * @param string $mode ('log', 'return')
     *
     * @return array
     *
     * Note: This is English text only. Can be call before database and languages are loaded
     * @throws \ReflectionException
     */
    static function run_system_check($registry, $mode = 'log')
    {
        $mlog = $counts = [];
        //run anyway
        $mlog[] = self::check_install_directory();

        if ( //run on admin side
            (ABC::env('IS_ADMIN') === true
                && (!$registry->get('config')->get('config_system_check')
                    || $registry->get('config')->get('config_system_check') == 1))
            || //run on storefront side
            (ABC::env('IS_ADMIN') !== true
                && (!$registry->get('config')->get('config_system_check')
                    || $registry->get('config')->get('config_system_check') == 2))
        ) {

            $mlog = array_merge($mlog, self::check_file_permissions($registry));
            $mlog = array_merge($mlog, self::check_php_configuration());
            $mlog = array_merge($mlog, self::check_server_configuration($registry));
            $mlog = array_merge($mlog, self::check_order_statuses($registry));
            $mlog = array_merge($mlog, self::check_web_access());
        }

        $counts['error_count'] = $counts['warning_count'] = $counts['notice_count'] = 0;
        foreach ($mlog as $message) {
            if ($message['type'] == 'E') {
                if ($mode == 'log') {
                    //only save errors to the log
                    $error = new AError($message['body']);
                    $error->toLog()->toDebug();
                    $registry->get('messages')->saveError($message['title'], $message['body']);
                }
                $counts['error_count']++;
            } else {
                if ($message['type'] == 'W') {
                    if ($mode == 'log') {
                        $registry->get('messages')->saveWarning($message['title'], $message['body']);
                    }
                    $counts['warning_count']++;
                } else {
                    if ($message['type'] == 'N') {
                        if ($mode == 'log') {
                            $registry->get('messages')->saveNotice($message['title'], $message['body']);
                        }
                        $counts['notice_count']++;
                    }
                }
            }
        }

        return [$mlog, $counts];
    }

    static function check_install_directory()
    {
        //check if install dir existing. warn
        if (file_exists(dirname(ABC::env('DIR_APP')).DS.'install')) {
            return [
                'title' => 'Security warning',
                'body'  => 'You still have install directory present in your '
                            .'AbanteCart main directory. It is highly recommended to delete install directory.',
                'type'  => 'W',
            ];
        }
        return [];
    }

    /**
     * @param Registry $registry
     *
     * @return array
     */
    static function check_file_permissions($registry)
    {
        //check file permissions.
        $ret_array = [];
        $index = ABC::env('DIR_PUBLIC').'index.php';
        if (is_writable($index) || substr(sprintf("%o", fileperms($index)), -3) == '777') {
            $ret_array[] = [
                'title' => 'Incorrect index.php file permissions',
                'body'  => $index.' file is writable. It is recommended to set read and execute modes '
                        .'for this file to keep it secured and running properly!',
                'type'  => 'W',
            ];
        }

        if (is_writable(ABC::env('DIR_CONFIG'))) {
            $ret_array[] = [
                'title' => 'Incorrect config directory permissions',
                'body'  => ABC::env('DIR_CONFIG')
                    .' directory needs to be set to read and execute modes to keep it secured from editing!',
                'type'  => 'W',
            ];
        }

        //if cache is enabled
        if ($registry->get('config')->get('config_cache_enable') && ABC::env('CACHE')['driver'] == 'file') {
            $cache_files = self::get_all_files_dirs(ABC::env('DIR_SYSTEM').'cache'.DS);
            $cache_message = '';
            foreach ($cache_files as $file) {
                if (!is_file($file)) {
                    continue;
                }
                $cache_message = '';
                if (in_array(basename($file), ['index.html', 'index.html', '.', '', '..'])) {
                    continue;
                }
                if (!is_writable($file)) {
                    $cache_message .= $file."<br/>";
                }
            }
            if ($cache_message) {
                $ret_array[] = [
                    'title' => 'Incorrect cache files permissions',
                    'body'  => "Following files do not have write permissions."
                            ." AbanteCart will not function properly. <br/>"
                        .$cache_message,
                    'type'  => 'E',
                ];
            }
        }

        if (!is_writable(ABC::env('DIR_LOGS')) || !is_writable(ABC::env('DIR_LOGS').'error.txt')) {
            $ret_array[] = [
                'title' => 'Incorrect log dir/file permissions',
                'body'  => ABC::env('DIR_LOGS').' directory or error.txt file needs to be '
                        .'set to full permissions(777)! Error logs can not be saved',
                'type'  => 'W',
            ];
        }
        //check resource directories
        $resource_files = self::get_all_files_dirs(ABC::env('DIR_PUBLIC').'resources'.DS);
        $resource_message = '';
        foreach ($resource_files as $file) {
            if (in_array(basename($file), ['.htaccess', 'index.php', 'index.html', '.', '', '..'])) {
                continue;
            }
            if (!is_writable($file)) {
                $resource_message .= $file."<br/>";
            }
        }
        if ($resource_message) {
            $ret_array[] = [
                'title' => 'Incorrect resource files permissions',
                'body'  => "Following files(folders) do not have write permissions. "
                           ."AbanteCart Media Manager will not function properly. <br/>"
                    .$resource_message,
                'type'  => 'W',
            ];
        }

        $image_files = self::get_all_files_dirs(ABC::env('DIR_PUBLIC').'images'.DS.'thumbnails'.DS);
        $image_message = '';
        foreach ($image_files as $file) {
            if (in_array(basename($file), ['index.php', 'index.html', '.', '', '..'])) {
                continue;
            }
            if (!is_writable($file)) {
                $image_message .= $file."<br/>";
            }
        }
        if ($image_message) {
            $ret_array[] = [
                'title' => 'Incorrect image files permissions',
                'body'  => "Following files do not have write permissions. "
                        ."AbanteCart thumbnail images will not function properly. <br/>"
                    .$image_message,
                'type'  => 'W',
            ];
        }

        if (!is_writable(ABC::env('DIR_SYSTEM'))) {
            $ret_array[] = [
                'title' => 'Incorrect directory permission',
                'body'  => ABC::env('DIR_SYSTEM')
                    .' directory needs to be set to full permissions(777)! '
                    .'AbanteCart backups and upgrade will not work.',
                'type'  => 'W',
            ];
        }

        if (is_dir(ABC::env('DIR_SYSTEM').'backup')
            && !is_writable(ABC::env('DIR_SYSTEM').'backup')) {
            $ret_array[] = [
                'title' => 'Incorrect backup directory permission',
                'body'  => ABC::env('DIR_SYSTEM').'backup'
                    .' directory needs to be set to full permissions(777)! '
                    .'AbanteCart backups and upgrade will not work.',
                'type'  => 'W',
            ];
        }

        if (is_dir(ABC::env('DIR_APP').'system'.DS.'temp')
            && !is_writable(ABC::env('DIR_APP').'system'.DS.'temp')) {
            $ret_array[] = [
                'title' => 'Incorrect temp directory permission',
                'body'  => ABC::env('DIR_SYSTEM').'temp'
                    .' directory needs to be set to full permissions(777)!',
                'type'  => 'W',
            ];
        }

        if (is_dir(ABC::env('DIR_SYSTEM').'uploads')
            && !is_writable(ABC::env('DIR_SYSTEM').'uploads')) {
            $ret_array[] = [
                'title' => 'Incorrect "uploads" directory permission',
                'body'  => ABC::env('DIR_SYSTEM').'uploads'
                    .' directory needs to be set to full permissions(777)! '
                    .'Probably AbanteCart file uploads will not work.',
                'type'  => 'W',
            ];
        }

        return $ret_array;
    }

    static function check_php_configuration()
    {
        //check if all modules and settings on PHP side are OK.
        $ret_array = [];

        if (!extension_loaded('mysql') && !extension_loaded('mysqli')) {
            $ret_array[] = [
                'title' => 'MySQL extension is missing',
                'body'  => 'MySQL extension needs to be enabled on PHP for AbanteCart to work!',
                'type'  => 'E',
            ];
        }
        if (!ini_get('file_uploads')) {
            $ret_array[] = [
                'title' => 'File Upload Warning',
                'body'  => 'PHP file_uploads option is disabled. File uploading will not function properly',
                'type'  => 'W',
            ];
        }
        if (ini_get('session.auto_start')) {
            $ret_array[] = [
                'title' => 'Issue with session.auto_start',
                'body'  => 'AbanteCart will not work with session.auto_start enabled!',
                'type'  => 'E',
            ];
        }
        if (!extension_loaded('gd')) {
            $ret_array[] = [
                'title' => 'GD extension is missing',
                'body'  => 'GD extension needs to be enabled in PHP for '
                          .'AbanteCart to work! Images will not display properly',
                'type'  => 'E',
            ];
        }

        if (!extension_loaded('mbstring') || !function_exists('mb_internal_encoding')) {
            $ret_array[] = [
                'title' => 'mbstring extension is missing',
                'body'  => 'MultiByte String extension needs to be loaded in PHP for AbanteCart to work!',
                'type'  => 'E',
            ];
        }
        if (!extension_loaded('zlib')) {
            $ret_array[] = [
                'title' => 'ZLIB extension is missing',
                'body'  => 'ZLIB extension needs to be loaded in PHP for backups to work!',
                'type'  => 'W',
            ];
        }

        //check memory limit

        $memory_limit = trim(ini_get('memory_limit'));
        $last = strtolower($memory_limit[strlen($memory_limit) - 1]);

        switch ($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $memory_limit *= (1024 * 1024 * 1024);
                break;
            case 'm':
                $memory_limit *= (1024 * 1024);
                break;
            case 'k':
                $memory_limit *= 1024;
                break;
        }

        //Recommended minimal PHP memory size is 64mb
        if ($memory_limit < (64 * 1024 * 1024)) {
            $ret_array[] = [
                'title' => 'Memory limitation',
                'body'  => 'Low PHP memory setting. Some Abantecart features will not work '
                    .'with memory limit less than 64Mb! Check '
                    .'<a href="http://php.net/manual/en/ini.core.php#ini.memory-limit" target="_help_doc">'
                    .'PHP memory-limit setting</a>',
                'type'  => 'W',
            ];
        }

        return $ret_array;
    }

    /**
     * @param Registry $registry
     *
     * @return array
     */
    static function check_server_configuration($registry)
    {
        //check server configurations.
        $ret_array = [];

        $size = self::disk_size(ABC::env('DIR_APP'));
        //check for size to drop below 10mb
        if (isset($size['bytes']) && $size['bytes'] < 1024 * 10000) {
            $ret_array[] = [
                'title' => 'Critically low disk space',
                'body'  => 'AbanteCart is running on critically low disk space of '.$size['human']
                    .'! Increase disk size to prevent failure.',
                'type'  => 'E',
            ];
        }

        //if SEO is enabled
        if ($registry->get('config')->get('enable_seo_url')
            && str_contains($_SERVER['SERVER_SOFTWARE'], 'Apache')
        ) {
            $htaccess = ABC::env('DIR_PUBLIC').'/.htaccess';
            if (!file_exists($htaccess)) {
                $ret_array[] = [
                    'title' => 'SEO URLs does not work',
                    'body'  => $htaccess.' file is missing. SEO URL functionality will not work. '
                        .'Check the <a href="http://docs.abantecart.com/pages/tips/enable_seo.html" target="_help_doc">'
                        .'manual for SEO URL setting</a> ',
                    'type'  => 'W',
                ];
            }
        }

        return $ret_array;
    }

    static function get_all_files_dirs($start_dir)
    {
        $iteration = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($start_dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
        );

        $paths = [$start_dir];
        foreach ($iteration as $path => $dir) {
            $paths[] = $path;
        }
        return $paths;
    }

    static function disk_size($path)
    {
        //check if this is supported by server
        if (function_exists('disk_free_space')) {
            try {
                $bytes = disk_free_space($path);
                $si_prefix = ['B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB'];
                $base = 1024;
                $class = min((int)log($bytes, $base), count($si_prefix) - 1);
                return [
                    'bytes' => $bytes,
                    'human' => sprintf('%1.2f', $bytes / pow($base, $class)).' '.$si_prefix[$class],
                ];
            } catch (Exception $e) {
                return [];
            }
        } else {
            return [];
        }
    }

    /**
     * @param \abc\core\engine\Registry $registry
     *
     * @return array
     * @throws Exception
     */
    static function check_order_statuses($registry)
    {

        $db = $registry->get('db');

        $order_statuses = $registry->get('order_status')->getStatuses();

        $query = $db->query(
            "SELECT os.order_status_id, os.status_text_id
            FROM ".$db->table_name('order_statuses')." os"
        );
        $db_statuses = [];
        foreach ($query->rows as $row) {
            $db_statuses[(int)$row['order_status_id']] = $row['status_text_id'];
        }

        $ret_array = [];
        foreach ($order_statuses as $id => $text_id) {
            if ($text_id != $db_statuses[$id]) {
                $ret_array[] = [
                    'title' => 'Incorrect order status with id '.$id,
                    'body'  => 'Incorrect status text id for order status #'.$id.'. Value must be "'.$text_id.'" ('
                        .$db_statuses[$id].'). Please check data of tables '.$db->table_name('order_status_ids').' and '
                        .$db->table_name('order_statuses'),
                    'type'  => 'W',
                ];
            }
        }

        return $ret_array;
    }

    /**
     * function checks restricted areas
     */
    static function check_web_access()
    {

        $areas = [
            'system' => ['.htaccess', 'index.php'],
            'resources'.DS.'download' => ['.htaccess'],
            'download' => ['index.html'],
            'admin' => ['.htaccess', 'index.php'],
            'admin'.DS.'system' => ['.htaccess', 'index.html'],
        ];

        $ret_array = [];

        foreach ($areas as $subfolder => $rules) {
            $dirname = ABC::env('DIR_APP').DS.$subfolder;
            if (!is_dir($dirname)) {
                continue;
            }

            foreach ($rules as $rule) {
                $message = '';
                switch ($rule) {
                    case '.htaccess':
                        if (!is_file($dirname.DS.'.htaccess')) {
                            $message = 'Restricted directory '.$dirname
                                        .' have public access. It is highly recommended to create'
                                        .' .htaccess file and forbid access. ';
                        }
                        break;
                    case 'index.php':
                        if (!is_file($dirname.DS.'index.php')) {
                            $message = 'Restricted directory '.$dirname
                                .' does not contain index.php file. It is highly recommended to create it.';
                        }
                        break;
                    case 'index.html':
                        if (!is_file($dirname.DS.'index.html')) {
                            $message = 'Restricted directory '.$dirname
                                .' does not contain empty index.html file. It is highly recommended to create it.';
                        }

                        break;
                    default:
                        break;
                }
                if ($message) {
                    $ret_array[] = [
                        'title' => 'Security warning ('.$subfolder.', '.$rule.')',
                        'body'  => $message,
                        'type'  => 'W',
                    ];
                }
            }
        }
        return $ret_array;
    }

    /**
     * @param \abc\core\engine\Registry $registry
     * @param string $mode
     *
     * @return array
     * @throws \ReflectionException
     */

    static function run_critical_system_check($registry, $mode = 'log')
    {

        $mlog = [];
        $mlog[] = self::check_session_save_path();

        $output = [];

        foreach ($mlog as $message) {
            if ($message['body']) {
                if ($mode == 'log') {
                    //only save errors to the log
                    $error = new AError($message['body']);
                    $error->toLog()->toDebug();
                    $registry->get('messages')->saveError($message['title'], $message['body']);
                }
                $output[] = $message;
            }
        }

        return $output;
    }

    /**
     * @return array
     */
    static function check_session_save_path()
    {
        $save_path = ini_get('session.save_path');
        //check for non-empty path (it can be on some fast-cgi php)
        if ($save_path) {
            $parts = explode(';', $save_path);
            $path = array_pop($parts);
            if (!is_writable($path)) {
                return [
                    'title' => 'Session save path is not writable! ',
                    'body'  => 'Your server is unable to create a session necessary for '
                                .'AbanteCart functionality. Check logs for exact error details and '
                                .'contact your hosting support administrator to resolve this error.',
                ];
            }
        }
        return [];
    }

}