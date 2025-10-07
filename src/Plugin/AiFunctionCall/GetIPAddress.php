<?php

namespace Drupal\generic_colors\Plugin\AiFunctionCall;

use Drupal\ai\Attribute\FunctionCall;
use Drupal\ai\Base\FunctionCallBase;
use Drupal\ai\Service\FunctionCalling\ExecutableFunctionCallInterface;
use Drupal\ai\Service\FunctionCalling\FunctionCallInterface;
use Drupal\ai_agents\PluginInterfaces\AiAgentContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Plugin implementation to get user's IP address.
 */
#[FunctionCall(
  id: 'ai_agent:get_ip_address',
  function_name: 'ai_agent_get_ip_address',
  name: 'Get IP Address',
  description: 'Returns the IP address of the user.',
  group: 'information_tools',
  context_definitions: [
  ],
)]
class GetIPAddress extends FunctionCallBase implements ExecutableFunctionCallInterface, AiAgentContextInterface {

  /**
   * The config data.
   *
   * @var string
   */
  protected string $response = '';

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected RequestStack $requestStack;

  /**
   * Load from dependency injection container.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): FunctionCallInterface|static {
    $instance = new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('ai.context_definition_normalizer'),
    );
    $instance->requestStack = $container->get('request_stack');
    return $instance;
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\ai_agents\Exception\AgentProcessingException
   */
  public function execute() {
    $request = $this->requestStack->getCurrentRequest();
    if (!$request) {
      $this->response = json_encode(["error" => "No active request available."]);
      return;
    }

    // getClientIp respects trusted proxies configuration.
    $ip = $request->getClientIp();
    $ips = $request->getClientIps();

    $this->response = json_encode([
      'ip' => $ip,
      'ips' => $ips,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getReadableOutput(): string {
    return $this->response;
  }

}
