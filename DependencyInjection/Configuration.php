<?php

namespace EdouardKombo\MultiStepFormsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('edouard_kombo_multi_step_forms');

        $rootNode
            ->children()
                ->arrayNode('multistep_forms')
                    ->requiresAtLeastOneElement()              
                    ->prototype('array')
                        ->children()
                            ->scalarNode('entity_namespace')
                                ->defaultNull()
                            ->end()                                 
                            ->variableNode('forms_order')
                                ->defaultNull()
                            ->end()
                            ->variableNode('actions_order')
                                ->defaultNull()
                            ->end()
                            ->variableNode('redirect_order')
                                ->defaultNull()
                            ->end()                
                            ->variableNode('allowed_roles')
                                ->defaultNull()
                            ->end()                
                            ->variableNode('authentication_trigger')
                                ->defaultNull()
                            ->end()
                            ->variableNode('authentication_firewall')
                                ->defaultNull()
                            ->end()                
                            ->variableNode('authentication_entity_provider')
                                ->defaultNull()
                            ->end()
                            ->variableNode('authentication_mailer_service')
                                ->defaultNull()
                            ->end()                 
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;        
        
        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
