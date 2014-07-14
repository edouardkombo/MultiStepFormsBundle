<?php

namespace EdouardKombo\MultiStepFormsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class EdouardKomboMultiStepFormsExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $multistep_forms = $config['multistep_forms'];
        
        foreach ($multistep_forms as $key => $var) {
            $container->setParameter("multistep_forms.$key", $multistep_forms[$key]);
            
            foreach ($multistep_forms[$key] as $property => $value) {   
                $container->setParameter("multistep_forms.$key.$property", $multistep_forms[$key][$property]);
            }
        }
        
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
