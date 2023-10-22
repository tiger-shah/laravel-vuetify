<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use App\Core\RequestRules;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public static function defaultRules(): RequestRules
    {
        return new RequestRules([
            'first_name'=> ['string', 'min:3'],
            'last_name' => ['string', 'min:3'],
            'username'  => ['string', 'min:6'],
            'email'     => ['email', 'max:50'],
            'mobile'    => ['digits:11', 'regex:/^(09)/'],
            'gender'    => ['in:' . User::data('Genders')->implode('name', ',')],
            'birthday'  => ['date_format:Y-m-d'],
            'password'  => [Password::min(6)->letters()->numbers()],
        ]);
    }

    public function rules(): array
    {
        switch ($this->route()->getName()) {
            case 'api.register':
                return $this->registerRules();
            case 'api.login':
                return $this->loginRules();
            case 'account.setting.update':
                return $this->settingRules();
        }
    }

    public function registerRules(): array
    {
        return $this->defaultRules()
            ->only(['first_name', 'last_name', 'email', 'password'])
            ->addRuleToField('email', 'unique:users')
            ->addRequired()
            ->toArray();
    }

    public function loginRules(): array
    {
        return $this->defaultRules()
            ->only(['email'])
            ->union(['password' => ['string', 'min:3']])
            ->addRequired()
            ->toArray();
    }

    public function settingRules(): array
    {
        return $this->defaultRules()
            ->forget(['username', 'password', 'mobile'])
            ->addRequired()
            ->toArray();
    }

    public function validated($key = null, $default = null): array
    {
        $transformedData = [];
        if ($this->has('gender')) {
            $transformedData['gender'] = User::data('Genders')->getKeyByName($this->gender);
        }

        return array_merge(parent::validated($key, $default), $transformedData);
    }
}
