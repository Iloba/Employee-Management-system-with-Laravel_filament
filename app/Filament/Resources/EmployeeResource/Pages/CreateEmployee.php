<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\EmployeeResource;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    // protected function getCreatedNotificationTitle(): ?string{
    //     return 'Employee Created';
    // }

    protected function getCreatedNotification(): ?Notification{
        return Notification::make()
        ->success()
        ->title("Employee Created")
        ->body("Employee Created Successfully");
    }
}
