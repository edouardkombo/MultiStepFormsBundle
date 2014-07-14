MultiStep Forms Bundle
======================

About
-----

This bundle functionality is to help you manage multiple steps forms in a single controller with a single view.
This bundle has been made for Symfony > 2.5.


Requirements
------------

Require PHP version 5.3 or greater.


Installation
------------

Register the bundle in your composer.json

    {
        "require": {
            "edouardkombo/multi-step-forms-bundle": "dev-master"
        }
    }

Now, install the vendor

    php composer.phar install


Register MultiStepFormsBundle namespace in your app/appKernel.php

    new EdouardKombo\MultiStepFormsBundle\EdouardKomboMultiStepFormsBundle(),


Add MultiStepFormsBundle routes in your app/config/routing.yml, we will see further how to customize routes

    edouard_kombo_multi_step_forms:
    resource: "@EdouardKomboMultiStepFormsBundle/Resources/config/routing.yml"
    prefix:   /{_locale}/registration/step 


Now, add the config parameters inside your app/config/config.yml.
The default configs are designed for a multistep registration form, but you can easily extend them to any kind of forms you want.

    edouard_kombo_multi_step_forms:
        multistep_forms:
            #Create your own param here, name it how you want, (user_registration) is just for demo
            user_registration:

                #Mandatory: Main entity where to save your forms datas
                entity_namespace: 'your_form_entity_namespace'
                
                #Mandatory: Form types in order of execution
                forms_order: ['namespace_of_first_form', 'namespace_of_second_form', 'namespace_of_third_form']
            
                #Mandatory: The three below form types will be redirected to a single action controller that will save datas
                #Of course, you can customize the way you want by overriding
                actions_order: ['edouard_kombo_multi_step_forms_create', 'edouard_kombo_multi_step_forms_create', 'edouard_kombo_multi_step_forms_create']
            
                #Mandatory: Each form will be render in a single view template specified in "indexAction", in the main controller
                #The last parameter is the route to be redirected to when the process is finished
                redirect_order: ['edouard_kombo_multi_step_forms_show', 'edouard_kombo_multi_step_forms_show', 'edouard_kombo_multi_step_forms_show', 'frontend_payment_choice']
                
                #Mandatory: This option is mandatory for routes
                allowed_roles: ['ROLE_USER', 'ROLE_MANAGER']
                
                #Optional: Form entity that will trigger authentication in case of user registration form
                authentication_trigger: 'form_type_namespace_that_triggers_authentication'
                
                #Optional: Your specified firewall (in security.yml), in case of user registration form
                authentication_firewall: 'main'
                
                #Optional: Where Doctrine will find the user, just after subsscription, for authentication
                authentication_entity_provider: 'VendorNameBundle:Entity'

                #Optional: Your mailer service, in case of user registration form
                #If not specified, no mail will be sent to user
                #authentication_mailer_service: 'your_mailer_servce'


Documentation
-------------

All the magic of MultiStepFormsBundle is written in one controller, one listener and one helper.
You will only need two routes inside the global route you defined.

Example: You want to setup a multistep registration form.
    
1. Create a "UserStepRegistrationController.php" controller in your userBundle and override the EdouardKombo\MultiStepFormsBundle\Controller\MultiStepFormsController.php class with it.
Make sure to target a single template view in your bundle.

2. Create a "LoginListener" in your userBundle and override the EdouardKombo\MultiStepFormsBundle\Listener\LoginListener.php class with it.

3. Create a "MultiStepFormsHelper" in your userBundle and override the EdouardKombo\MultiStepFormsBundle\Helper\MultiStepFormsHelper.php class with it. 

4. Override your services and inject the correct parameter specified in app/config/config.yml, here "user registration".

    parameters:
        multistep_forms.login_listener.class: VendorName\UserBundle\Listener\LoginListener
        multistep_forms.controller.class:     VendorName\UserBundle\Controller\MultiStepFormsController
        multistep_forms.helper.class:         VendorName\UserBundle\Helper\MultiStepFormsHelper

    services:
        multistep_forms.helper:
            class: %multistep_forms.helper.class%
            arguments:
                - %multistep_forms.CONFIG_PARAMETER% #Here it will be "user_registration"
                - @security.context
                - @service_container

        multistep_forms.login_listener:
            class: %multistep_forms.login_listener.class%
            arguments:
                - @doctrine.orm.entity_manager
                - @service_container
                - @security.context

        multistep_forms.controller:
            class: %multistep_forms.controller.class%     

5. Define your routes. Only two routes are needed, show and create. Routes have this format: /show/{user_role}/{step}
    - {user_role}: is the role you want the user to be registered (it has no incidence for other forms but is mandatory).
    - {step}: current step, depending on the configurations

Define your global route in app/config/routing.yml

    frontend_multistep_registration:
        resource: "@VendorUserBundle/Resources/config/routing/multistep_registration.xml"
        prefix:   /{_locale}/registration/step


In your multistep_registration.xml
    
    <route id="frontend_multistep_registration_show" pattern="/show/{user_role}/{step}">
        <default key="_controller">VendorUserBundle:UserStepRegistration:index</default>
        <default key="user_role">manager</default>
        <requirement key="step">\d+</requirement>
        <requirement key="user_role">[a-zA-Z]+</requirement>         
    </route> 

    <route id="frontend_multistep_registration_create" pattern="/create/{user_role}/{step}">
        <default key="_controller">VendorUserBundle:UserStepRegistration:save</default>
        <default key="user_role">manager</default>
        <requirement key="_method">POST</requirement>
        <requirement key="step">\d+</requirement>
        <requirement key="user_role">[a-zA-Z]+</requirement>        
    </route>        

 6. Change the bundle configs in app/config/congif.yml and that's ok, you're ready to go.


Contributing
-------------

If you want to help me improve this bundle, please make sure it conforms to the PSR coding standard. The easiest way to contribute is to work on a checkout of the repository, or your own fork, rather than an installed version.

Issues
------

Bug reports and feature requests can be submitted on the [Github issues tracker](https://github.com/edouardkombo/MultiStepFormsBundle/issues).

For further informations, contact me directly at edouard.kombo@gmail.com.

