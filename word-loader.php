<?php
/**
 * Plugin Name: WordLoader
 * Description: Add a loader that shows words in a random position and order
 * Version:     0.0.3
 * Author:      Enrico Atzeni
 * Author URI:  https://enricoatzeni.it
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: d-word-loader
 */

class WordLoader
{
    private $canIdo = null;

    public function __construct()
    {
        require_once(__DIR__ . '/admin/options.php');

        add_action('wp_enqueue_scripts', [$this, 'register_front_assets']);
        
        add_action('wp_footer', array($this, 'add_loading_mask'));

        // adds the onload callback to our script
        // add_filter('script_loader_tag', [$this, 'add_onload_callback'], 10, 2);
    }

    private function canIdo()
    {
        if ($this->canIdo === null) {
            $canShow = apply_filters('word-loader-visibility', true);

            if (!$canShow) {
                $this->canIdo = false;    
            } else {
                $WLOptions = WordLoaderOptions::getInstance();
                $wordList = $WLOptions->getOption('word_list', null);
                
                $this->canIdo = !!$wordList;
            }
        }

        return $this->canIdo;
    }

    public function register_front_assets()
    {
        if (!$this->canIdo()) {
            return;
        }

        $css_version = filemtime(__DIR__ . '/front/assets/word-loader.min.css');
        wp_enqueue_style('wl-style', plugin_dir_url(__FILE__) . '/front/assets/word-loader.min.css', [], $css_version);

        $js_version = filemtime(__DIR__ . '/front/assets/word-loader.min.js');
        // NOTE: DON'T load this in the footer
        // its a loader, so you want it to be shown as soon as possibile!
        wp_enqueue_script('wl-script', plugin_dir_url(__FILE__) . '/front/assets/word-loader.min.js', [], $js_version, false);
    }

    public function add_loading_mask()
    {
        if (!$this->canIdo()) {
            return;
        }

        $WLOptions = WordLoaderOptions::getInstance();
        $wordList = $WLOptions->getOption('word_list', null);

        $words = json_encode($wordList);

        // filters to allow developers to customize the loader
        $wl_id = apply_filters('word-loader-id', 'word-loader');
        $wl_options = apply_filters('word-loader-options', []);

        echo '<div id="'. $wl_id .'" words="' . htmlentities($words) . '">';

        // NOTE: require word-loader JS to be loaded before and in SYNC mode
        // its a loader, so you want it to be shown as soon as possibile!
        echo '<script>new WordLoader(\''. $wl_id .'\', '.json_encode($wl_options, JSON_FORCE_OBJECT).')</script>';

        echo '</div>';
        
    }

    public function add_onload_callback($tag, $handle)
    {
        if ("wl-script" !== $handle) {
            return $tag;
        }

        return str_replace( ' src=', ' onload="new WordLoader(\'word-loader\')" src=', $tag);
    }
}

new WordLoader();