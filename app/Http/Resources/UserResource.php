<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id'                  => $this->id,
            'name'                => $this->name,
            'account_type'        => $this->determineAccountType(),
            'email'               => $this->email,
            'is_email_verified'   => $this->email_verified_at !== null,
            //'roles' => $this->roles->pluck('name'),
            //'permissions' => $this->getAllPermissions()->pluck('name'),
            'has_password'        => $this->password !== null,
            'email_verified_at'   => $this->email_verified_at,
            'password_updated_at' => $this->password_updated_at,
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,


        ];
        return $data;

    }
}
