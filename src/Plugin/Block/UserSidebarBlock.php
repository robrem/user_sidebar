<?php

namespace Drupal\user_sidebar\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'UserSidebarBlock' block.
 *
 * @Block(
 *  id = "user_sidebar_block",
 *  admin_label = @Translation("User sidebar block"),
 * )
 */
class UserSidebarBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Datetime\DateFormatterInterface definition.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->dateFormatter = $container->get('date.formatter');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'custom_message' => '',
      'hide_for_anonymous' => FALSE,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['custom_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Custom Message'),
      '#description' => $this->t('Enter an optional message to display to all users in this block.'),
      '#default_value' => $this->configuration['custom_message'],
      '#weight' => '1',
    ];
    $form['hide_for_anonymous'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide for anonymous'),
      '#description' => $this->t('Hide the custom message from anonymous users.'),
      '#default_value' => $this->configuration['hide_for_anonymous'],
      '#weight' => '2',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['custom_message'] = $form_state->getValue('custom_message');
    $this->configuration['hide_for_anonymous'] = $form_state->getValue('hide_for_anonymous');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $user = \Drupal::currentUser();
    $username = $user->getDisplayName();
    // Link to the user profile page. Defaults to login block for Anonymous
    // users.
    $user_url = Url::fromRoute('user.page')->toString();
    // Datetime of the user's last login.
    $last_login = NULL;
    if ($user->isAuthenticated()) {
      // Date of user's last login in format "December 21st, 2012 9:01 am".
      $last_login = $user->getLastAccessedTime();
      $last_login = $this->dateFormatter->format($last_login, 'custom', 'F jS, Y g:i a');
    }

    $build = [
      '#theme' => 'user_sidebar_block',
      '#username' => $username,
      '#user_url' => $user_url,
      '#last_login' => $last_login,
      '#custom_message' => $this->configuration['custom_message'],
      '#hide_for_anonymous' => $this->configuration['hide_for_anonymous']
    ];

    return $build;
  }

}
