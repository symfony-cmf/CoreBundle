<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\CoreBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Cmf\Bundle\CoreBundle\DependencyInjection\CmfCoreExtension;
use Symfony\Cmf\Bundle\RoutingBundle\CmfRoutingBundle;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Routing\Router;

class CmfCoreExtensionTest extends TestCase
{
    private CmfCoreExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new CmfCoreExtension();
    }

    public function testPublishWorkflowAutoSupported(): void
    {
        $container = $this->createContainer(['kernel.bundles' => ['SecurityBundle' => SecurityBundle::class]]);

        $this->extension->load([['publish_workflow' => ['request_listener' => false]]], $container);

        $this->assertTrue($container->hasAlias('cmf_core.publish_workflow.checker'));
        $this->assertTrue($container->hasDefinition('cmf_core.publish_workflow.checker.default'));
        $this->assertTrue($container->hasDefinition('cmf_core.security.publishable_voter'));
        $this->assertTrue($container->hasDefinition('cmf_core.security.publish_time_period_voter'));
        $this->assertFalse($container->hasDefinition('cmf_core.publish_workflow.request_listener'));
    }

    public function testPublishWorkflowListenerEnabled(): void
    {
        $container = $this->createContainer(['kernel.bundles' => [
            'SecurityBundle' => SecurityBundle::class,
            'CmfRoutingBundle' => CmfRoutingBundle::class,
        ]]);

        $this->extension->load([], $container);

        $this->assertTrue($container->hasAlias('cmf_core.publish_workflow.checker'));
        $this->assertTrue($container->hasDefinition('cmf_core.publish_workflow.checker.default'));
        $this->assertTrue($container->hasDefinition('cmf_core.security.publishable_voter'));
        $this->assertTrue($container->hasDefinition('cmf_core.security.publish_time_period_voter'));
        $this->assertTrue($container->hasDefinition('cmf_core.publish_workflow.request_listener'));
    }

    public function testPublishWorkflowAutoNotSupported(): void
    {
        $container = $this->createContainer(['kernel.bundles' => []]);

        $this->extension->load([], $container);

        $this->assertTrue($container->hasDefinition('cmf_core.publish_workflow.checker'));
        $this->assertFalse($container->hasAlias('cmf_core.publish_workflow.checker'));
        $this->assertFalse($container->hasDefinition('cmf_core.publish_workflow.checker.default'));
        $this->assertFalse($container->hasDefinition('cmf_core.security.publishable_voter'));
        $this->assertFalse($container->hasDefinition('cmf_core.security.publish_time_period_voter'));
        $this->assertFalse($container->hasDefinition('cmf_core.publish_workflow.request_listener'));
    }

    public function testPublishWorkflowFalse(): void
    {
        $container = $this->createContainer(['kernel.bundles' => [
            'SecurityBundle' => SecurityBundle::class,
            'CmfRoutingBundle' => CmfRoutingBundle::class,
        ]]);

        $this->extension->load([['publish_workflow' => false]], $container);

        $this->assertTrue($container->hasDefinition('cmf_core.publish_workflow.checker'));
        $this->assertFalse($container->hasAlias('cmf_core.publish_workflow.checker'));
        $this->assertFalse($container->hasDefinition('cmf_core.publish_workflow.checker.default'));
        $this->assertFalse($container->hasDefinition('cmf_core.security.publishable_voter'));
        $this->assertFalse($container->hasDefinition('cmf_core.security.publish_time_period_voter'));
        $this->assertFalse($container->hasDefinition('cmf_core.publish_workflow.request_listener'));
    }

    public function testPublishWorkflowTrueSupported(): void
    {
        $container = $this->createContainer(['kernel.bundles' => [
            'SecurityBundle' => SecurityBundle::class,
            'CmfRoutingBundle' => CmfRoutingBundle::class,
        ]]);

        $this->extension->load([['publish_workflow' => true]], $container);

        $this->assertTrue($container->hasAlias('cmf_core.publish_workflow.checker'));
        $this->assertTrue($container->hasDefinition('cmf_core.publish_workflow.checker.default'));
        $this->assertTrue($container->hasDefinition('cmf_core.security.publishable_voter'));
        $this->assertTrue($container->hasDefinition('cmf_core.security.publish_time_period_voter'));
        $this->assertTrue($container->hasDefinition('cmf_core.publish_workflow.request_listener'));
    }

    public function testPublishWorkflowTrueNotSupported(): void
    {
        $container = $this->createContainer(['kernel.bundles' => [
            'CmfRoutingBundle' => CmfRoutingBundle::class,
        ]]);

        $this->expectException(InvalidConfigurationException::class);
        $this->extension->load([['publish_workflow' => true]], $container);
    }

    private function createContainer(array $parameters): ContainerBuilder
    {
        $parameters = array_merge(['kernel.debug' => false], $parameters);
        $container = new ContainerBuilder(
            new ParameterBag($parameters)
        );

        // The cache_manager service depends on the router service
        $container->setDefinition(
            'router',
            new Definition(Router::class)
        );

        return $container;
    }
}
