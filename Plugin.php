<?php

namespace Sixgweb\AttributizeBackendUsers;

use Event;
use Backend;
use System\Classes\PluginBase;
use Sixgweb\AttributizeBackendUsers\Classes\EventHandler;

/**
 * Plugin Information File
 *
 * @link https://docs.octobercms.com/3.x/extend/system/plugins.html
 */
class Plugin extends PluginBase
{

    public $require = [
        'Sixgweb.Attributize',
    ];

    /**
     * pluginDetails about this plugin.
     */
    public function pluginDetails()
    {
        return [
            'name' => 'AttributizeBackendUsers',
            'description' => 'Backend User Integration with Attributize',
            'author' => 'Sixgweb',
            'icon' => 'icon-users'
        ];
    }

    /**
     * boot method, called right before the request route.
     */
    public function boot()
    {
        //What is this grossness?  This is only required to trigger the Translator init() method.
        //If the init() method is called after Fieldable in added to the Backend\User model, via the EventHandler,
        // an infinite loop between the Fieldable and TranslatableModel behaviors occurs.  Just leave it. - 6G
        if (class_exists('RainLab\Translate\Classes\Translator')) {
            $translator = \RainLab\Translate\Classes\Translator::instance();
        }

        Event::subscribe(EventHandler::class);

        /**
         * Backend Users Controller uses span-left and span-right.  This breaks the bootstrap layout, so we
         * change the span and spanClass values to use bootstrap grid.
         */
        Event::listen('sixgweb.attributize.backend.form.extendAllFields', function ($formWidget, $fieldableFields) {
            if ($formWidget->getController() instanceof \Backend\Controllers\Users) {
                foreach ($formWidget->getFields() as $key => $field) {
                    switch ($field->span) {
                        case 'left':
                        case 'right':
                            $field->span('row');
                            $field->spanClass('col-6');
                            break;
                        case 'full':
                            $field->span('row');
                            $field->spanClass('col-12');
                            break;
                    }
                }
            }
        });

        \Backend\Controllers\Users::extend(function ($controller) {
            if ($controller->getAction() == 'fields') {
                \System\Classes\SettingsManager::setContext('Sixgweb.AttributizeBackendUsers', 'attributizefields');
            }
        });
    }

    public function registerSettings()
    {
        return [
            'attributizefields' => [
                'label' => 'sixgweb.attributizebackendusers::lang.backend_user_fields',
                'description' => 'sixgweb.attributizebackendusers::lang.backend_user_fields_description',
                'category' => \System\Classes\SettingsManager::CATEGORY_TEAM,
                'icon' => ' icon-check-square',
                'url' => Backend::url('backend/users/fields'),
                'permissions' => [
                    'admins.manage',
                    'sixgweb.attributize.manage_fields'
                ],
                'order' => 600
            ],
        ];
    }
}
