<?php

namespace App\Filament\Pdv\Resources\Users\Pages;

use App\Filament\Pdv\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
