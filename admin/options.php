<?php 

class WordLoaderOptions 
{
    private static $instance = null;

    private $options = [];
    private $optionName = 'o-word-loader';
    

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new WordLoaderOptions();
        }

        return self::$instance;
    }

    public function getOptions()
    {
        if (!$this->options) {
            $this->options = get_option($this->optionName, []);
        }
        
        return $this->options;
    }

    public function getOption($key, $default = null)
    {
        $options = $this->getOptions();

        if (isset($options[$key])) {
            return $options[$key];
        }

        return $default;
    }

    public function __construct()
    {
        add_action('admin_menu', [$this, 'admin_add_page']);

        // define the settings
        add_action('admin_init', [$this, 'admin_init']);

        // add css
        add_action('admin_enqueue_scripts', [$this, 'admin_style']);
    }

    public function admin_style()
    {
        wp_enqueue_style('wl-style', plugin_dir_url(__FILE__) . '/assets/admin.css');
    }

    public function getOptionName()
    {
        return $this->optionName;
    }

    public function admin_add_page()
    {
        add_options_page(
            __('Word Loader', 'd-word-loader'),
            __('Word Loader', 'd-word-loader'), 
            'manage_options', 
            'word-loader', 
            [$this, 'generate_options_page']
        );
    }

    public function generate_options_page()
    {
        include(__DIR__ . '/views/options.php');
    }

    public function admin_init()
    {
        register_setting(
            'word-loader-settings', // same as settings_fields()
            $this->optionName, 
            [$this, 'options_validate']
        );
        
        add_settings_section(
                            'main_section', 
                            __('Main Settings', 'd-word-loader'), 
                            [$this, 'section_text'],
                            'word-loader' // same as do_settings_sections()
                        );

        add_settings_field(
                            'word_list', 
                            __('Word list', 'd-word-loader'), 
                            [$this, 'setting_wordlist'],
                            'word-loader', // same as do_settings_sections()
                            'main_section'
                        );
    }

    public function section_text()
    {
        // echo '<p>'.__("Required minimum config for the plugin", 'd-word-loader').'</p>';
    }

    public function setting_wordlist()
    {
        $wordList = $this->getOption('word_list', []);
        $listImploded = implode(', ', $wordList);
        echo '<span class="wl-hint">' . __('Insert words separated by a comma', 'd-word-loader') . '</span>';
        echo "<textarea id='word_list' name='{$this->optionName}[word_list]' cols='40' rows='4'>{$listImploded}</textarea>";
    }

    public function options_validate($input)
    {
        $options = $this->getOptions();

        // initialize if empty
        $options = $options ? $options : [];

        if (isset($input['word_list'])) {
            if (is_array($input['word_list'])) {
                $input['word_list'] = implode(',', $input['word_list']);
            }
            $wordList = trim($input['word_list']);
            $listExploded = explode(',', $wordList);
            $list = [];
            foreach($listExploded as $item) {
                $itemCleaned = trim($item);
                if (!empty($itemCleaned)) {
                    $list[] = $itemCleaned;
                }
            }
            $options['word_list'] = $list;
        }

        return $options;
    }
}

WordLoaderOptions::getInstance();