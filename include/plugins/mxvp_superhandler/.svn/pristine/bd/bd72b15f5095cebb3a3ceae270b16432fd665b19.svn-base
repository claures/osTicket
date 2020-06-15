<?php
require_once INCLUDE_DIR . 'class.plugin.php';

class SuperhandlerPluginConfig extends PluginConfig {

  // Provide compatibility function for versions of osTicket prior to
  // translation support (v1.9.4)
  function translate() {
    if (! method_exists('Plugin', 'translate')) {
      return [
        function ($x) {
          return $x;
        },
        function ($x, $y, $n) {
          return $n != 1 ? $y : $x;
        }
      ];
    }
    return Plugin::translate('attachment_preview');
  }

  /**
   * Build an Admin settings page.
   *
   * {@inheritdoc}
   *
   * @see PluginConfig::getOptions()
   */
  function getOptions() {
    return array('mxvp_superhandler_enable' => new BooleanField(array(
      'id'=>'mxvp_superhandler_enable',
      'label'=>'Enable Incomming Webhook',
      'configuration' => array(
        'desc' => 'Enable Incomming Webhook'
      )
    )),
    'mxvp_superhandler_apikey' => new TextboxField(array(
      'id'=>'mxvp_superhandler_apikey',
      'label'=>'API Key for the Webhook',
      'configuration' => array(
        'desc' => 'API Key for the Webhook'
      )
    )),
  );
  }

  function pre_save(&$config, &$errors) {
    global $msg;

    if (!$errors) {
      $msg = 'Configuration updated successfully';
    }

    return true;
  }
}