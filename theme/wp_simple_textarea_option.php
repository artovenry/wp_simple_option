<?php

class WPSimpleTextAreaOption extends WPSimpleOption{
  function render_widget(){
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
          style="height: 34px; margin-bottom: 8px;padding: 6px 7px;"
          name="<?= $this->option_name ?>"
          id="<?= $this->option_name ?>"
          autocomplete="off"
          rows="3" cols="15"
          placeholder="保存したい文章を記入してください"
          value="<?= $this->sanitized_saved_value() ?>"
        >

        </textarea>
      </div>
      <?php submit_button( __( 'Save Changes' ) ); ?>
    </form>
    <?php //--- TEMPLATE_END
  }
}