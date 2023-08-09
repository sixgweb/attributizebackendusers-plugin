<?php

namespace Sixgweb\AttributizeBackendUsers\Classes;

use October\Rain\Database\Model;
use Sixgweb\Attributize\Classes\AbstractEventHandler;

class EventHandler extends AbstractEventHandler
{
    protected function getTitle(): string
    {
        return 'User Field';
    }
    protected function getModelClass(): string
    {
        return \Backend\Models\User::class;
    }

    protected function getComponentClass(): ?string
    {
        return null;
    }

    protected function getControllerClass(): string
    {
        return \Backend\Controllers\Users::class;
    }

    protected function getComponentModel($component): Model
    {
        return new ($this->getModelClass())();
    }

    protected function getBackendMenuParameters(): array
    {
        return [
            'owner' => 'October.Backend',
            'code' => 'team',
            'url' => \Backend::url('backend/users/fields'),
        ];
    }

    protected function getAllowCreateFileUpload(): bool
    {
        return true;
    }
}
