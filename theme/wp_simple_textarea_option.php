<?php
require __DIR__ . "/wp_simple_option.php";

class WPSimpleTextAreaOption extends WPSimpleOption{
  protected function sanitized_posted_value(){
    return sanitize_textarea_field($_POST[$this->option_name]);
  }
  protected function sanitized_saved_value(){
    return sanitize_textarea_field(get_option($this->option_name));
  }

  function render_widget($post, $callback_args){
    extract($callback_args["args"]);
    $invalid_style= ($input_field_state == 'invalid') ? 'border: 1px solid red;' : '';
    //--- TEMPLATE_START ?>
    <form method="post" class="dashboard-widget-control-form wp-clearfix">
      <?php wp_nonce_field($this->option_name, $this->nonce_name()); ?>
      <div class="textarea-wrap">
        <label 
          style="margin-bottom: 4px; display: inline-block;"
          for="<?= $this->option_name ?>"
        >
          <?= $this->option_label ?>
        </label>
        <textarea
          style="margin-bottom: 8px;padding: 6px 7px; <?= $invalid_style?>"
          name="<?= $this->option_name ?>"
          id="<?= $this->option_name ?>"
          autocomplete="off"
          rows="9"
          placeholder="保存したい文章を記入してください"
        ><?=$value ?></textarea>
      </div>
      <?php submit_button( __( 'Save Changes' ) ); ?>
    </form>
    <?php //--- TEMPLATE_END
  }
}