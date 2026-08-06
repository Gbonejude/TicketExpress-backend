<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Withdrawal;

use App\Models\Organizer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreWithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'organizer_id' => ['required', 'string', Rule::exists('organizers', 'id')],
            'requester_phone' => ['required', 'string', 'max:30'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'string', Rule::in(['flooz', 'tmoney'])],
        ];
    }

    /**
     * Le montant demandé ne peut pas dépasser le solde disponible.
     *
     * C'était le trou du dispositif : `amount >= 1` était la seule contrainte,
     * alors que le solde était déjà calculé (`Organizer::availableBalance()`) et
     * même exposé par l'endpoint `earnings`. Rien n'empêchait donc de demander
     * dix fois ses recettes, et l'anomalie n'apparaissait qu'au moment de payer.
     *
     * Le contrôle est ici plutôt que dans le contrôleur pour que le refus
     * revienne comme une erreur de validation sur `amount`, affichable sous le
     * champ du formulaire.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                // Inutile de parler de solde si l'organisateur ou le montant
                // sont déjà invalides.
                if ($validator->errors()->hasAny(['organizer_id', 'amount'])) {
                    return;
                }

                $organizer = Organizer::find($this->input('organizer_id'));

                if ($organizer === null) {
                    return;
                }

                $available = $organizer->availableBalance();

                if ((float) $this->input('amount') > $available) {
                    $validator->errors()->add('amount', sprintf(
                        'Le montant dépasse le solde disponible de cet organisateur (%s FCFA).',
                        number_format($available, 0, ',', ' '),
                    ));
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'organizer_id.required' => 'L\'organisateur est requis.',
            'organizer_id.exists' => 'L\'organisateur n\'existe pas.',
            'requester_phone.required' => 'Le numéro du demandeur est requis.',
            'amount.required' => 'Le montant est requis.',
            'amount.min' => 'Le montant doit être au moins 1.',
            'payment_method.required' => 'La méthode de paiement est requise.',
            'payment_method.in' => 'La méthode de paiement doit être Flooz ou Mix by Yas.',
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'organizer_id' => [
                'description' => 'The ULID of the organizer requesting withdrawal.',
                'example' => '01HXE2K3M4N5P6Q7R8S9T0V1W2',
            ],
            'requester_phone' => [
                'description' => 'Phone number of the person who requested the withdrawal.',
                'example' => '+22890112233',
            ],
            'amount' => [
                'description' => 'Amount to withdraw.',
                'example' => 500000,
            ],
            'payment_method' => [
                'description' => 'Payment method for withdrawal (Flooz or Mix by Yas).',
                'example' => 'flooz',
            ],
        ];
    }
}
