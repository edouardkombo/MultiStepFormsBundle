<?php

/**
 * Main docblock
 *
 * PHP version 5
 *
 * @category  Listener
 * @package   MultiStepFormsBundle
 * @author    Edouard Kombo <edouard.kombo@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @version   GIT: 1.0.0
 * @link      http://creativcoders.wordpress.com
 * @since     0.0.0
 */
namespace EdouardKombo\MultiStepFormsBundle\Listener;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Listen to interactive login event and log in a user manually
 *
 * @category Listener
 * @package  MultiStepFormsBundle
 * @author   Edouard Kombo <edouard.kombo@gmail.com>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://creativcoders.wordpress.com
 */
class LoginListener
{
    /** 
     * @var \Symfony\Component\Security\Core\SecurityContext 
     */
    protected $securityContext;
        
    /**
     *
     * @var string $username
     */
    protected $username;
    
    /**
     *
     * @var object $em
     */
    protected $em;
    
    /**
     *
     * @var string $firewall
     */
    protected $firewall; 
    
    /**
     *
     * @var string $userEntity
     */
    protected $userEntity;     
    
    /**
     *
     * @var object $container
     */
    protected $container;    
    
    /**
     * Constructor
     * 
     * @param \Doctrine\ORM\EntityManager $em              Doctrine ORM
     * @param object                      $container       Container Object
     * @param SecurityContext             $securityContext Security management
     * 
     * @return \EdouardKombo\MultiStepFormsBundle\Listener\LoginListener
     */
    public function __construct(\Doctrine\ORM\EntityManager $em, $container, 
            SecurityContext $securityContext)
    {
        $this->em               = (object) $em; 
        $this->container        = (object) $container;
        $this->securityContext  = (object) $securityContext;
        
        return $this;
    }
    
    /**
     * Set the username
     * 
     * @param string $username Username of the current user
     * 
     * @return \EdouardKombo\MultiStepFormsBundle\Listener\LoginListener
     */
    public function setUsername($username)
    {
        $this->username = (string) $username;
        return $this;
    }
    
    /**
     * Set the firewall
     * 
     * @param string $firewall Actual security firewall
     * 
     * @return \EdouardKombo\MultiStepFormsBundle\Listener\LoginListener
     */
    public function setFirewall($firewall)
    {
        $this->firewall = (string) $firewall;
        return $this;
    }
    
    /**
     * Define the user entity
     * 
     * @param string $userEntity Project User entity
     * 
     * @return \EdouardKombo\MultiStepFormsBundle\Listener\LoginListener
     */
    public function setUserEntity($userEntity)
    {
        $this->userEntity = (string) $userEntity;
        return $this;
    }    
    
    /**
     * Login a user and dispatch the event.
     * 
     * @return object
     * @throws UsernameNotFoundException
     */
    public function secureInteractiveLogin()
    {
        if (!$this->securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            
            $repository  = $this->em->getRepository($this->userEntity);       
            $user        = $repository->findOneByUsername($this->username);

            $request     = $this->container->get('request');
                
            if (!$user) {
                throw new UsernameNotFoundException("User not found");

            } else {
                $token = new UsernamePasswordToken($user, null, $this->firewall, 
                    $user->getRoles());

                $this->securityContext->setToken($token);

                $event = new InteractiveLoginEvent($request, $token);
                return $this->container->get("event_dispatcher")->dispatch(
                    "security.interactive_login", 
                    $event
                );
            }            
        } else {
            $user = $event->getAuthenticationToken()->getUser();
        }
        
        return $user;
    }    
}