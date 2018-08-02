<?php 
class Smarty_Welcome extends PxPSmarty {
    function __construct($app = ''){
        global $theme,$config;
        $site_url = $config['site_url'];

        parent::__construct();
        $this->template_dir = ROOT . "/apps/$theme/$app/templates/$app/";
        $this->config_dir   = ROOT . "/apps/$theme/$app/configs/";
        $this->cache_dir    = ROOT . "/apps/$theme/$app/cache/";
        $this->assign(array(
            'app_name' => 'welcome',
            'xhr_url' => "$site_url/xhr/signin",
            'xhr_reset_url' => "$site_url/xhr/reset"
        ));
    }
}
