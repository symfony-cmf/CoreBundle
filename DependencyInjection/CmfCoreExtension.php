<?php

namespace Symfony\Cmf\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class CmfCoreExtension extends Extension implements PrependExtensionInterface
{
    /**
     * Allow an extension to prepend the extension configurations.
     *
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        // process the configuration of SymfonyCmfCoreExtension
        $configs = $container->getExtensionConfig($this->getAlias());
        $parameterBag = $container->getParameterBag();
        $configs = $parameterBag->resolveValue($configs);
        $config = $this->processConfiguration(new Configuration(), $configs);
        if (isset($config['multilang']['locales'])) {
            $prependConfig = array('multilang' => $config['multilang']);
            foreach ($container->getExtensions() as $name => $extension) {
                switch ($name) {
                    case 'cmf_routing':
                        $container->prependExtensionConfig($name, array('dynamic' => $prependConfig['multilang']));
                        break;
                    case 'cmf_content':
                    case 'cmf_menu':
                    case 'cmf_simple_cms':
                        $container->prependExtensionConfig($name, $prependConfig);
                        break;
                }
            }
        }

        if (isset($config['persistence']['phpcr'])) {
            $bundles = $container->getParameter('kernel.bundles');
            if (!isset($bundles['SonataDoctrinePHPCRAdminBundle'])) {
                $config['persistence']['phpcr']['use_sonata_admin'] = false;
            }
            $persistenceConfig = $config['persistence']['phpcr'];

            foreach ($container->getExtensions() as $name => $extension) {
                $prependConfig = array();

                switch ($name) {
                    case 'cmf_block':
                        $prependConfig = array(
                            'use_sonata_admin' => $persistenceConfig['use_sonata_admin'],
                            'content_basepath' => $persistenceConfig['basepath'].'/content',
                            'block_basepath' => $persistenceConfig['basepath'].'/content',
                            'manager_name' => $persistenceConfig['manager_name'],
                        );
                        break;
                    case 'cmf_blog':
                        $prependConfig = array(
                            'use_sonata_admin' => $persistenceConfig['use_sonata_admin'],
                        );
                        break;
                    case 'cmf_content':
                        $prependConfig = array(
                            'persistence' => array(
                                'phpcr' => array(
                                    'enabled' => $persistenceConfig['enabled'],
                                    'use_sonata_admin' => $persistenceConfig['use_sonata_admin'],
                                    'content_basepath' => $persistenceConfig['basepath'].'/content',
                                )
                            )
                        );
                        break;
                    case 'cmf_create':
                        $prependConfig = array(
                            'phpcr_odm' => true,
                            'image' => array(
                                'static_basepath' => $persistenceConfig['basepath'].'/content/static',
                            ),
                        );
                        break;
                    case 'cmf_menu':
                        $prependConfig = array(
                            'persistence' => array(
                                'phpcr' => array(
                                    'enabled' => $persistenceConfig['enabled'],
                                    'use_sonata_admin' => $persistenceConfig['use_sonata_admin'],
                                    'content_basepath' => $persistenceConfig['basepath'].'/content',
                                    'menu_basepath' => $persistenceConfig['basepath'].'/menu',
                                    'manager_name' => $persistenceConfig['manager_name'],
                                )
                            )
                        );
                        break;
                    case 'cmf_routing':
                        $prependConfig = array(
                            'dynamic' => array(
                                'enabled' => true,
                                'persistence' => array(
                                    'phpcr' => array(
                                        'enabled' => $persistenceConfig['enabled'],
                                        'use_sonata_admin' => $persistenceConfig['use_sonata_admin'],
                                        'content_basepath' => $persistenceConfig['basepath'].'/content',
                                        'route_basepath' => $persistenceConfig['basepath'].'/routes',
                                        'manager_name' => $persistenceConfig['manager_name'],
                                    )
                                )
                            )
                        );
                        break;
                    case 'cmf_simple_cms':
                        $prependConfig = array(
                            'use_sonata_admin' => $persistenceConfig['use_sonata_admin'],
                            'basepath' => $persistenceConfig['basepath'].'/simple',
                            'manager_name' => $persistenceConfig['manager_name'],
                        );
                        break;
                }

                if ($prependConfig) {
                    $container->prependExtensionConfig($name, $prependConfig);
                }
            }
        }
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if ($config['publish_workflow']['enabled']) {
            $checker = $this->loadPublishWorkflow($config['publish_workflow'], $loader, $container);
            if (!empty($config['phpcr']['enabled'])) {
                $container->setParameter($this->getAlias() . '.manager_name', $config['persistence']['phpcr']['manager_name']);
            }
        } else {
            $loader->load('no_publish_workflow.xml');
            $checker = 'cmf_core.publish_workflow.checker.always';
        }
        $container->setAlias('cmf_core.publish_workflow.checker', $checker);

        if (isset($config['multilang'])) {
            $container->setParameter($this->getAlias() . '.multilang.locales', $config['multilang']['locales']);
            $loader->load('translatable.xml');
        } else {
            $loader->load('translatable-disabled.xml');
        }

        $this->setupFormTypes($container, $loader);
    }

    /**
     * Setup the cmf_core_checkbox_url_label form type if the routing bundle is there
     *
     * @param array $config
     * @param ContainerBuilder $container
     * @param LoaderInterface $loader
     */
    public function setupFormTypes(ContainerBuilder $container, LoaderInterface $loader)
    {
        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['CmfRoutingBundle'])) {
            $loader->load('form_type.xml');

            // if there is twig, register our form type with twig
            if ($container->hasParameter('twig.form.resources')) {
                $resources = $container->getParameter('twig.form.resources');
                $container->setParameter('twig.form.resources', array_merge($resources, array('CmfCoreBundle:Form:checkbox_url_label_form_type.html.twig')));
            }
        }
    }

    /**
     * @param $config
     * @param XmlFileLoader $loader
     * @param ContainerBuilder $container
     *
     * @return string the name of the workflow checker service to alias
     *
     * @throws InvalidConfigurationException
     */
    private function loadPublishWorkflow($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $container->setParameter($this->getAlias().'.publish_workflow.view_non_published_role', $config['view_non_published_role']);
        $loader->load('publish_workflow.xml');

        if (!$config['request_listener']) {
            $container->removeDefinition($this->getAlias() . '.publish_workflow.request_listener');
        } elseif (!class_exists('Symfony\Cmf\Bundle\RoutingBundle\Routing\DynamicRouter')) {
            throw new InvalidConfigurationException('The "publish_workflow.request_listener" may not be enabled unless "Symfony\Cmf\Bundle\RoutingBundle\Routing\DynamicRouter" is available.');
        }

        if (!class_exists('Sonata\AdminBundle\Admin\AdminExtension')) {
            $container->removeDefinition($this->getAlias() . '.admin_extension.publish_workflow.publishable');
            $container->removeDefinition($this->getAlias() . '.admin_extension.publish_workflow.time_period');
        }

        return $config['checker_service'];
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    public function getNamespace()
    {
        return 'http://cmf.symfony.com/schema/dic/core';
    }
}
