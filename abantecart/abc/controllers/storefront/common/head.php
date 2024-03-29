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

namespace abc\controllers\storefront;

use abc\core\ABC;
use abc\core\engine\AController;
use abc\core\helper\AHelperSystemCheck;
use abc\core\engine\AResource;
use abc\core\lib\ADebug;

if ( ! class_exists( 'abc\core\ABC' ) ) {
    header( 'Location: static_pages/?forbidden='.basename( __FILE__ ) );
}

class ControllerCommonHead extends AController
{
    public function main()
    {

        //init controller data
        $this->extensions->hk_InitData( $this, __FUNCTION__ );

        //run system check to make sure system is stable to run the request
        //for storefront log messages. nothing is shown to users
        AHelperSystemCheck::run_system_check( $this->registry, 'log' );

        $this->loadLanguage( 'common/header' );

        $this->view->assign( 'title', $this->document->getTitle() );
        $this->view->assign( 'keywords', $this->document->getKeywords() );
        $this->view->assign( 'description', $this->document->getDescription() );
        $this->view->assign( 'template', $this->config->get( 'config_storefront_template' ) );

        $retina = $this->config->get( 'config_retina_enable' );
        $this->view->assign( 'retina', $retina );
        //remove cookie for retina
        if ( ! $retina ) {
            $this->request->deleteCookie( 'HTTP_IS_RETINA' );
        }

        if ( ABC::env( 'HTTPS' ) ) {
            $this->view->assign( 'base', ABC::env( 'HTTPS_SERVER' ) );
            $this->view->assign( 'ssl', 1 );
        } else {
            $this->view->assign( 'base', ABC::env( 'HTTP_SERVER' ) );
        }

        $icon_rl = $this->config->get( 'config_icon' );
        //see if we have a resource ID or path
        if ( $icon_rl ) {
            if ( is_numeric( $icon_rl ) ) {
                $resource = new AResource( 'image' );
                $image_data = $resource->getResource( $icon_rl );
                if ( is_file( ABC::env( 'DIR_RESOURCES' ).$image_data['image'] ) ) {
                    $icon_rl = 'resources/'.$image_data['image'];
                } else {
                    $icon_rl = $image_data['resource_code'];
                }
            } else {
                if ( ! is_file( ABC::env( 'DIR_RESOURCES' ).$icon_rl ) ) {
                    $this->messages->saveWarning( 'Check favicon.', 'Warning: please check favicon in your store settings. Current path is "'.ABC::env( 'DIR_RESOURCES' ).$icon_rl.'" but file does not exists.' );
                    $icon_rl = '';
                }
            }
        }

        if ($this->config->get('config_google_tag_manager_id')) {
            $this->view->assign('google_tag_manager', trim($this->config->get('config_google_tag_manager_id')));
        }

        $this->view->assign( 'icon', $icon_rl );
        $this->view->assign( 'lang', $this->language->get( 'code' ) );
        $this->view->assign( 'direction', $this->language->get( 'direction' ) );
        $this->view->assign( 'links', $this->document->getLinks() );
        $this->view->assign( 'styles', $this->document->getStyles() );
        $this->view->assign( 'scripts', $this->document->getScripts() );
        $this->view->assign( 'breadcrumbs', $this->document->getBreadcrumbs() );

        $this->view->assign( 'store', $this->config->get( 'store_name' ) );
        $this->view->assign( 'cart_url', $this->html->getSecureURL( 'checkout/cart' ) );
        $this->view->assign( 'cart_ajax', (int)$this->config->get( 'config_cart_ajax' ) );
        //URL should be automatic for CORS
        $this->view->assign( 'cart_ajax_url', $this->html->getURL( 'r/product/product/addToCart' ) );
        $this->view->assign( 'search_url', $this->html->getNonSecureURL( 'product/search' ) );
        $this->view->assign( 'call_to_order_url', $this->html->getURL( 'content/contact' ) );
        //load template debug resources if needed
        $this->view->assign( 'template_debug_mode', $this->config->get( 'storefront_template_debug' ) );

        /**
         * @var \DebugBar\StandardDebugBar $debug_bar
         */
        $debug_bar = ADebug::$debug_bar;
        if ( $debug_bar ) {
            $debugbar_assets = ADebug::getDebugBarAssets();
            $dbg_js_set = $debugbar_assets['js'];
            $dbg_css_set = $debugbar_assets['css'];
            foreach ( $dbg_css_set as $src ) {
                $this->document->addStyle(
                    array(
                        // remove forward slash
                        'href'  => substr( $src, 1 ),
                        'rel'   => 'stylesheet',
                        'media' => 'screen',
                    )
                );
            }
            foreach ( $dbg_js_set as $src ) {
                $this->document->addScript( substr( $src, 1 ) );
            }
        }

        $this->processTemplate( 'common/head.tpl' );
        //init controller data
        $this->extensions->hk_UpdateData( $this, __FUNCTION__ );
    }
}