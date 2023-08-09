<?php

namespace Sixgweb\AttributizeBackendUsers;

use Event;
use Backend;
use System\Classes\PluginBase;
use Sixgweb\Attributize\Models\Settings;
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
        $this->fixFieldableTranslatableModelInifiteLoop(); //Needed before Event::subscribe (see method)
        $this->useGridLayoutOnBackendUserFields();
        $this->extendBackendUsersController();
        $this->extendAttributizeSettings();

        Event::subscribe(EventHandler::class);
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

    /**
     * Add Backend Users settings to attributize settings model
     *
     * @return void
     */
    protected function extendAttributizeSettings()
    {
        Event::listen('backend.form.extendFields', function ($form) {
            if (!$form->model instanceof Settings) {
                return;
            }

            //Don't extend repeaters
            if ($form->isNested) {
                return;
            }

            $form->addTabFields([
                'backenduser[add_export_features]' => [
                    'label' => 'sixgweb.attributizebackendusers::lang.enable_export',
                    'type' => 'checkbox',
                    'comment' => 'sixgweb.attributizebackendusers::lang.enable_export_description',
                    'tab' => 'sixgweb.attributizebackendusers::lang.backend_users'
                ],
            ]);
        });
    }

    /**
     * This is only required to trigger the Translator init() method before Fieldable behavior added to Backend\Models\User.
     * If the init() method is called after Fieldable is added to the Backend\User model, via the EventHandler,
     * an infinite loop between the Fieldable and TranslatableModel behaviors occurs.  Just leave it. - 6G
     *
     * @return void
     */
    private function fixFieldableTranslatableModelInifiteLoop()
    {
        if (class_exists('RainLab\Translate\Classes\Translator')) {
            $translator = \RainLab\Translate\Classes\Translator::instance();
        }
    }

    /**
     * Backend user fields are span-left and span-right.  This breaks the bootstrap layout, so we
     * change the span and spanClass values to use bootstrap grid.
     *
     * @return void
     */
    private function useGridLayoutOnBackendUserFields()
    {
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
    }

    /**
     * Set active settings menu item, if in fields editor.
     * Add export functionality to Backend Users controller, if enabled in settings.
     */
    private function extendBackendUsersController()
    {
        \Backend\Controllers\Users::extend(function ($controller) {

            //Set active setting item
            if ($controller->getAction() == 'fields') {
                \System\Classes\SettingsManager::setContext('Sixgweb.AttributizeBackendUsers', 'attributizefields');
            }

            //Export Functionality if Setting Enabled
            if (Settings::get('backenduser.add_export_features', false)) {
                //Add view path for export button addition.
                $controller->addViewPath(plugins_path() . '/sixgweb/attributizebackendusers/partials/');

                if (!isset($controller->importExportConfig)) {
                    $controller->implement[] = 'Backend.Behaviors.ImportExportController';
                    $controller->addDynamicProperty('importExportConfig', [
                        'export' => [
                            'useList' => [
                                'raw' => true,
                            ],
                            'fileName' => 'export-backend-users-' . date('Y-m-d'),
                        ]
                    ]);
                }
            }
        });
    }
}
