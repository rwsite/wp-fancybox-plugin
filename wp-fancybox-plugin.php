<?php
/**
 * Plugin Name: Fancybox js
 * Description: This WordPress plugin enable support fancybox js for posts.
 * Version: 1.0.0
 * Text Domain: fancybox
 * Domain Path: /languages
 * Author: Aleksey Tikhomirov
 *
 * Requires at least: 4.6
 * Tested up to: 6.3
 * Requires PHP: 8.0+
 *
 * How to use: add_theme_support('fancybox');
 */

namespace theme;

class FancyBox{

    /**
     * @var false|mixed|null
     */
    public array $settings;

    public function __construct()
    {
        $this->settings = $this->get_settings();
    }

    public function get_settings()
    {
        return get_option('fancybox',
            [
                'src'      => 'local',
                'selector' => '.single-post article .entry-content img, .entry-header img',
                'path'     => 'plugin',
                'inline'   => 'yes'
            ]
        );
    }

    public function add_actions(){

        load_plugin_textdomain( 'fancybox', false, dirname(plugin_basename(__FILE__)) . '/languages' );

        add_action('init', function () {

            if (!get_theme_support('fancybox')) {
                return;
            }

            add_action('wp_enqueue_scripts', [$this, 'enqueue']);

            if('yes' !== $this->settings['inline']) {
                add_action('wp_footer', [$this, 'footer']);
            }
        });
    }


    public function enqueue(){

        if(is_admin()){
            return;
        }

        if('cloud' === $this->settings['src']) {
            wp_enqueue_script('jquery.fancybox', 'https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js', 'jquery', '5.0', []);
            wp_enqueue_style('jquery.fancybox', 'https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css');
        } else {
            $path = get_stylesheet_directory_uri();
            if('plugin' === $this->settings['path']){
                $path = plugin_dir_url( __FILE__ );
            }
            wp_enqueue_script( 'jquery.fancybox', $path . '/assets/fancybox.js','jquery', '1.0.0',true);
            wp_enqueue_style( 'jquery.fancybox', $path . '/assets/fancybox.css', false, '1.0.0' );

            if('yes' === $this->settings['inline']){
                $inline_js = str_replace(['<script>','</script>'], '', $this->footer(true));
                wp_add_inline_script('jquery.fancybox', $inline_js );
            }
        }
    }

    public function footer($html = false)
    {
        if($html){
            ob_start();
        }

        ?>
        <script>
            jQuery(document).ready(function($){

                console.log('<?= defined('WP_DEBUG') && WP_DEBUG ? 'WP FancyBox included' : '';?>');

                $("<?= esc_attr($this->settings['selector']); ?>").each( function() {

                    let myObject = $(this);
                    let parent = $(this).parent();

                    if ($(this).parent() !== 'a') {
                        if( $(this).attr('srcset') !== undefined ) {
                            var srcset = $(this).attr('srcset');
                            var fullimg = srcset.replace(/ .*/, '');
                            myObject.replaceWith('<a data-src="' + fullimg + '" data-fancybox="images">' + myObject.prop('outerHTML') + '</a>');
                        } else {
                            myObject.replaceWith('<a data-src="' + $(this).attr('src') + '" data-fancybox="images">' + myObject.prop('outerHTML') + '</a>');
                        }

                    } else if ($(this).parent() === 'a') {
                        parent.attr("data-fancybox", "images").removeAttr("href");
                    }
                } );

                Fancybox.bind('[data-fancybox="images"], [data-fancybox="gallery"]', {});
            });
        </script>
        <?php

        if($html){
            return ob_get_clean();
        }
    }


    public function get_header_image_tag($html, $header, $attr){
        return $html;
    }
}

(new FancyBox())->add_actions();