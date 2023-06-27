<?php
class WPSimpleOption{
  const OPTION_NAME = "wp_simple_option";
  const OPTION_LABEL = "WP SIMPLE OPTION";
  const WIDGET_TITLE = "WP SIMPLE OPTION";
  private $option_name = null;
  private $option_label = null;
  private $widget_title = null;
  
  static function run($args= []){
    $defaults= [
      "option_name"=> self::OPTION_NAME,
      "option_label" => self::OPTION_LABEL,
      "widget_title" => self::WIDGET_TITLE,
    ];

    $args = wp_parse_args($args, $defaults);
    extract($args);

    $inst = new self;
    $inst->option_name = $option_name;
    $inst->option_label = $option_label;
    $inst->widget_title = $widget_title;

    add_action("wp_dashboard_setup", [$inst, "setup"]);
    add_action("admin_init", function() use($inst){
      add_action("admin_notices", [$inst, "message"], 10);
    });
  }

  private function remove_all_dashboard_widgets(){
    global $wp_meta_boxes;
    foreach($wp_meta_boxes["dashboard"]["normal"]["core"] as $key=> $value)
      remove_meta_box($key, "dashboard", "normal");
    foreach($wp_meta_boxes["dashboard"]["side"]["core"] as $key=> $value)
      remove_meta_box($key, "dashboard", "side");
  }

  private function render(){
    //--- TEMPLATE_START ?>
    <form method="post" class="dashboard-widget-control-form wp-clearfix">
      <?php wp_nonce_field($this->option_name, "{$this->option_name}_nonce"); ?>
      <div class="input-text-wrap" id="title-wrap">
        <label style="margin-bottom: 4px; display: inline-block;"  for="<?= $this->option_name ?>"><?= $this->option_label ?></label>
        <input style="margin-bottom: 4px; type="text" name="<?= $this->option_name ?>" id="<?= $this->option_name ?>" autocomplete="off" />
      </div>
      <?php submit_button( __( 'Save Changes' ) ); ?>
    </form>
    <?php //--- TEMPLATE_END
  }

  function widget_callback(){
    $this->render();
  }

  function message(){
    global $pagenow;
    if($pagenow != "index.php")return;
    ?>
      <div class="error"><p>メッセージ内容</p></div>
      <div class="updated"><p>メッセージ内容</p></div>
    <?php
  }

  function setup(){
    if(!is_super_admin()){
      remove_action('welcome_panel', 'wp_welcome_panel');
      $this->remove_all_dashboard_widgets();
    }
    wp_add_dashboard_widget($this->option_name, $this->widget_title, [$this, "widget_callback"]);
  }
}

WPSimpleOption::run([
  "widget_title"=>"ここで設定して",
  "option_label"=>"固定ツイートのID",
]);