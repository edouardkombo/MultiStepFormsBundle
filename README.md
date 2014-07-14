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
                authentication_entity_provider: 'HeadooUserBundle:User'

                #Optional: Your mailer service, in case of user registration form
                #If not specified, no mail will be sent to user
                #authentication_mailer_service: 'your_mailer_servce'

You can also download the InterfaceFactory source directly from the GIT checkout:

    https://github.com/edouardkombo/InterfaceFactory


Documentation
-------------

All additional documentation is available in code docblocks.

Contributing
-------------

If you do contribute code to InterfaceFactory, please make sure it conforms to the PSR coding standard. The easiest way to contribute is to work on a checkout of the repository, or your own fork, rather than an installed version.

Issues
------

Bug reports and feature requests can be submitted on the [Github issues tracker](https://github.com/edouardkombo/InterfaceFactory/issues).

For further informations, contact me directly at edouard.kombo@gmail.com.

