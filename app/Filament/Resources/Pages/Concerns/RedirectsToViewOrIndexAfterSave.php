<?php

namespace App\Filament\Resources\Pages\Concerns;

use Illuminate\Database\Eloquent\Model;

trait RedirectsToViewOrIndexAfterSave
{
    protected function getRedirectUrl(): ?string
    {
        $resource = static::getResource();
        $record = $this->getRecord();

        if (
            $record instanceof Model
            && $resource::hasPage('view')
            && $resource::canView($record)
        ) {
            return $this->getResourceUrl('view', $this->getRedirectUrlParameters());
        }

        return $this->getResourceUrl(parameters: $this->getRedirectUrlParameters());
    }
}
