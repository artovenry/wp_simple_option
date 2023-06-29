<?php
class WPSimpleOption{
  const OPTION_NAME = "wp_simple_option";
  const OPTION_LABEL = "WP SIMPLE OPTION";
  const WIDGET_TITLE = "WP SIMPLE OPTION";
  const MESSAGE_UPDATED = "%sを更新しました";
  const MESSAGE_ERROR = "%sの更新に失敗しました、システム管理者に連絡してください";
  const MESSAGE_INVALID = "%sの更新に失敗しました、入力した値は無効です";
  const CAPABILITY = null;
  const OK= "ok";
  const ERROR= "error";
  const INVALID= "invaid";
  

  private $option_name;
  private $option_label;
  private $widget_title;
  private $message_updated;
  private $message_error;
  private $message_invalid;
  private $validator;
  private $capability;
  
  static function run($args= []){
    $defaults= [
      "option_name"=> self::OPTION_NAME,
      "option_label" => self::OPTION_LABEL,
      "widget_title" => self::WIDGET_TITLE,
      "message_updated" => sprintf(self::MESSAGE_UPDATED, self::OPTION_NAME),
      "message_error" => sprintf(self::MESSAGE_ERROR, self::OPTION_NAME),
      "message_invalid" => sprintf(self::MESSAGE_INVALID, self::OPTION_NAME),
      "validator" => function($val){return true;},
      "capability" => self::CAPABILITY,
    ];

    $args = wp_parse_args($args, $defaults);
    extract($args);

    $inst = new self;
    $inst->option_name = $option_name;
    $inst->option_label = $option_label;
    $inst->widget_title = $widget_title;
    $inst->message_updated = $message_updated;
    $inst->message_error = $message_error;
    $inst->message_invalid = $message_invalid;
    $inst->validator = $validator;
    $inst->capability = $capability;

    add_action("wp_dashboard_setup", [$inst, "setup"]);
  }

  function setup(){
    if(!is_super_admin()){
      remove_action('welcome_panel', 'wp_welcome_panel');
      $this->remove_all_dashboard_widgets();
    }
    if(!$this->is_valid_access())return;
    if(!$this->is_reading()){
      if($this->has_validation_passed()){
        if(update_option($this->option_name, $this->sanitized_posted_value())){
          $this->add_message(self::OK);
        }else{
          $this->add_message(self::ERROR);
        }
      }else{
        $this->add_message(self::INVALID);
      }
    }
    wp_add_dashboard_widget($this->option_name, $this->widget_title, [$this, "render_widget"]);
  }

  function render_widget(){
    //--- TEMPLATE_START ?>
    <form method="post" class="dashboard-widget-control-form wp-clearfix">
      <?php wp_nonce_field($this->option_name, $this->nonce_name()); ?>
      <div class="input-text-wrap" id="title-wrap">


        <label style="margin-bottom: 4px; display: inline-block;"  for="<?= $this->option_name ?>"><?= $this->option_label ?></label>
        <input style="margin-bottom: 4px;" type="text" name="<?= $this->option_name ?>" id="<?= $this->option_name ?>" autocomplete="off" value="<?= $this->sanitized_saved_value() ?>" />
      </div>
      <?php submit_button( __( 'Save Changes' ) ); ?>
    </form>
    <?php //--- TEMPLATE_END
  }

  private function add_message($status){
    $template = "<div class='%s'><p>%s</p></div>";
    add_action("admin_notices", function() use($status, $template){
      if($status == self::OK){
        printf($template, "updated", $this->message_updated);  
      }elseif($status == self::INVALID){
        printf($template, "error", $this->message_invalid);  
      }elseif($status == self::ERROR){
        printf($template, "error", $this->message_error);  
      }
    });
  }

  private function is_valid_access(){
    if(!empty($this->capability) && !current_user_can($this->capability)) return false;

    if($this->is_reading())return true;

    $nonce = $_POST[$this->nonce_name()];
    if(!isset($nonce))return false;

    return wp_verify_nonce($nonce, $this->option_name);
  }

  private function has_validation_passed(){
    $val = $this->sanitized_posted_value();
    if(!isset($val))return false;
    return call_user_func($this->validator, $val);
  }

  private function sanitized_posted_value(){
    return sanitize_text_field($_POST[$this->option_name]);
  }

  private function sanitized_saved_value(){
    return sanitize_text_field(get_option($this->option_name));
  }

  private function is_reading(){
    return !isset($_POST[$this->option_name]);
  }

  private function remove_all_dashboard_widgets(){
    global $wp_meta_boxes;
    foreach($wp_meta_boxes["dashboard"]["normal"]["core"] as $key=> $value)
      remove_meta_box($key, "dashboard", "normal");
    foreach($wp_meta_boxes["dashboard"]["side"]["core"] as $key=> $value)
      remove_meta_box($key, "dashboard", "side");
  }

  private function nonce_name(){
    return "{$this->option_name}_nonce";
  }

}

WPSimpleOption::run([
  // "capability"=> "edit_posts",
  "widget_title"=>"ここで設定して",
  "option_label"=>"固定ツイートのID",
  "message_invalid"=>"固定ツイートのIDは半角数字のみを入力してください",
  "validator"=> function($val){
    if($val === "")return true;
    return preg_match("/^[0-9]+$/", $val);
  },
]);