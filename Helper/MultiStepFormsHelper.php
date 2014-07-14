<?php

/**
 * Main docblock
 *
 * PHP version 5
 *
 * @category  Helper
 * @package   MultiStepFormsBundle
 * @author    Edouard Kombo <edouard.kombo@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @version   GIT: 1.0.0
 * @link      http://creativcoders.wordpress.com
 * @since     0.0.0
 */
namespace EdouardKombo\MultiStepFormsBundle\Helper;

use Symfony\Component\Security\Core\SecurityContext;


/**
 * Any method that can't be directly called in a controller
 *
 * @category Helper
 * @package  MultiStepFormsBundle
 * @author   Edouard Kombo <edouard.kombo@gmail.com>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://creativcoders.wordpress.com
 */
class MultiStepFormsHelper
{
    /**
     *
     * @var array $configuration
     */
    protected $configuration;    
    
    /** 
     * @var \Symfony\Component\Security\Core\SecurityContext 
     */
    protected $securityContext; 
    
    /**
     *
     * @var object $container
     */
    protected $container;     
    
    /**
     * Constructor
     * 
     * @param array           $configuration   Form configuration chosen
     * @param SecurityContext $securityContext Manage security
     * @param object          $container       Services container              
     * 
     */
    public function __construct($configuration, SecurityContext $securityContext, $container)
    {
       $this->configuration     = (array)  $configuration;
       $this->securityContext   = (object) $securityContext;
       $this->container         = (object) $container;
    }

    /**
     * Get chosen configurations
     * 
     * @return array
     */
    public function getConfiguration()
    {
        return (array) $this->configuration;
    }
    
    /**
     * Get role with normal conventions
     * 
     * @param string $role User role
     * 
     * @return string
     */
    public function getFormatedRole($role)
    {
        return (string) 'ROLE_' . strtoupper($role);
    }
    
    /**
     * Increment a value
     * 
     * @param integer $value Value to increment
     * 
     * @return integer
     */
    public function increment($value)
    {
        return (integer) $value + 1;
    }
    
    /**
     * Decrement a value
     * 
     * @param integer $value Value to decrement
     * 
     * @return integer
     */
    public function decrement($value)
    {
        return (integer) $value - 1;
    }    

    /**
     * Get initial role sent in the request
     * 
     * @param string $role User role
     * 
     * @return string
     */
    public function getUnFormatedRole($role)
    {
        return (string) str_replace('ROLE_', '', strtolower($role));
    }
    
    /**
     * Check if user is authenticated
     * 
     * @return boolean
     */
    public function isUserAuthenticated()
    {
        if (!$this->securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            return false;
        } else {
            return true;
        }        
    }
    
    /**
     * Save and log user if not already authenticated
     * 
     * @param object  $form
     * @param integer $currentStep
     * @param string  $role
     * @param object  $entity
     * 
     * @return mixed
     */
    public function saveAndLogUserCommand($form, $currentStep, $role, $entity)
    {
        if (!$this->isUserAuthenticated()) {
            $email      = $form->getData()->getEmail();
            $password   = $form->getData()->getPlainPassword();
            $username   = $form->getData()->getUsername();
            $entity     = $this->userManipulation($username, $email, $password)
                    ->setRoles(array($role));

            $this->sendUserRegistrationMail($entity, $password);
            $this->callAuthenticationListenerCommand($username, $currentStep);
        }
        
        return $entity;
    }
    
    /**
     * Check if user is needed and if he is authenticated, otherwhise return
     * previous entity
     * 
     * @return object
     */
    public function getCurrentUserIfSpecified()
    {
        $config         = $this->configuration;
        $firewallConfig = $config['authentication_firewall'];
        
        if (isset($firewallConfig) && !empty($firewallConfig)) {
            
            if (!$this->isUserAuthenticated()) {
                $return = (object) new $config['entity_namespace']();
                
            } else {
                $return = $this->securityContext->getToken()->getUser();
            }
            
        } else {
            $return = (object) new $config['entity_namespace']();
        }
        
        return $return;
    }
    
    /**
     * User creation manipulation from FOSUserBundle
     * 
     * @param string $username User username
     * @param string $email    User email
     * @param string $password User password
     * 
     * @return object
     */
    public function userManipulation($username, $email, $password)
    {          
        $userManipulator    = $this->container->get(
                'fos_user.util.user_manipulator'
        );
        
        return $userManipulator->create($username, $password,  $email, true, 
                false);        
    }
    
    /**
     * Mail to send to new user
     * 
     * @param object $entity   Current entity
     * @param string $password Password
     * 
     * @return object
     */
    public function sendUserRegistrationMail($entity, $password)
    {
        $config = $this->configuration;
        
        $mailer_service = $config['authentication_mailer_service'];
        if (isset($mailer_service)) {
            $mailer = (object) $this->container->get($mailer_service);
            return $mailer->sendAccountCreationInfo($entity, $password);            
        }    
    }
    
    /**
     * Call to login listener for authenticating new user
     * 
     * @param string  $username
     * @param integer $step
     * 
     * @return mixed
     */
    public function callAuthenticationListenerCommand($username, $step)
    {
        $config         = $this->configuration;        
        $currentForm    = $config['forms_order'][$step];
                
        if ($config['authentication_trigger'] === $currentForm) {        
            return $this->container->get('multistep_forms.login_listener')
                    ->setUsername($username)
                    ->setFirewall($config['authentication_firewall'])
                    ->setUserEntity($config['authentication_entity_provider'])
                    ->secureInteractiveLogin();
        } else {
            return false;
        }
    }    
}