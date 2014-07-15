<?php

/**
 * Main docblock
 *
 * PHP version 5
 *
 * @category  Controller
 * @package   MultiStepFormsBundle
 * @author    Edouard Kombo <edouard.kombo@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @version   GIT: 1.0.0
 * @link      http://creativcoders.wordpress.com
 * @since     0.0.0
 */
namespace EdouardKombo\MultiStepFormsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Main controller for multistep forms
 *
 * @category Listener
 * @package  MultiStepFormsBundle
 * @author   Edouard Kombo <edouard.kombo@gmail.com>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT License
 * @link     http://creativcoders.wordpress.com
 */
class MultiStepFormsController extends Controller
{
    /**
     * Main method that displays forms view
     * 
     * @return object
     * @throws AccessDeniedException
     */
    public function indexAction()
    {
        $helper = (object)  $this->get('multistep_forms.helper');
        $config = (array)   $helper->getConfiguration();
        $role   = (string)  $this->getRequest()->get('user_role');
        $step   = (integer) $this->getRequest()->get('step');
        
        $currentStep    = $helper->decrement($step);
        $formated_role  = $helper->getFormatedRole($role);
        $entity         = (object) $helper->getCurrentUserIfSpecified();       
        $formType       = (string) $config['forms_order'][$currentStep];
        $form           = (object) $this->createForm(new $formType(), $entity);
        
        if (!in_array($formated_role, $config['allowed_roles'])) {
            throw new AccessDeniedException("Access denied");
        }
  
        return $this->container->get('templating')->renderResponse(
            'EdouardKomboMultiStepFormsBundle:Registration:step.html.twig', array(
                'action_url'    => $config['actions_order'][$currentStep],
                'step'          => $step,
                'user_role'     => $role,
                'form'          => $form->createView()
            )
        );        
    }
    
    /**
     * Main method that prepare datas for saving
     * 
     * @return object
     */
    public function saveAction()
    {
        $helper         = (object)  $this->get('multistep_forms.helper');
        $config         = (array)   $helper->getConfiguration();
        $step           = (integer) $this->getRequest()->get('step');
        $role           = (string)  $this->getRequest()->get('user_role'); 
        $currentStep    = $helper->decrement($step);
        
        $entity = (object) $helper->getCurrentUserIfSpecified();
        $form   = (object) $this->createForm(new $config['forms_order'][$currentStep](), $entity );
        
        $form->bind($this->getRequest());
        
        if ($form->isValid()) {      
            $this->createAction($helper, $config, $form, $entity, $step, $role);   
        }
            
        return $this->container->get('templating')->renderResponse(
            'EdouardKomboMultiStepFormsBundle:Registration:step.html.twig', array(
                'action_url'    => $config['actions_order'][$currentStep],
                'step'          => $step,
                'user_role'     => $role,
                'form'          => $form->createView()
            )
        );             
    }
    
    /**
     * Method that save datas
     * 
     * @param object  $helper   Helper methods
     * @param array   $config   Parameters
     * @param object  $form     Form objects
     * @param object  $entity   Entity object
     * @param integer $step     Current step
     * @param string  $role     User role
     * 
     * @return object
     */
    public function createAction($helper, $config, $form, $entity, $step, $role)
    {      
        $nextStep       = $helper->increment($step);
        $currentStep    = $helper->decrement($step);
        
        if (isset($config['authentication_trigger'])) {         
            $entity = $helper->saveAndLogUserCommand($form, $currentStep, $role,
                    $entity);
        }             
        
        $this->createForm(new $config['forms_order'][$currentStep](), $entity)
            ->bind($this->getRequest());

        $em = $this->getDoctrine()->getManager();
        $em->persist($entity);
        $em->flush();            

        $redirectUrl = $this->generateUrl($config['redirect_order'][$step], 
            array(
                'step'      => $nextStep,
                'user_role' => $helper->getUnformatedRole($role)
            )
        );            

        return $this->redirect($redirectUrl);        
    }
}