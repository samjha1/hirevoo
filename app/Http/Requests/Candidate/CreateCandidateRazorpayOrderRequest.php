<?php



namespace App\Http\Requests\Candidate;



use App\Services\CandidatePlanService;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;



class CreateCandidateRazorpayOrderRequest extends FormRequest

{

    public function authorize(): bool

    {

        return $this->user()?->isCandidate() ?? false;

    }



    /** @return array<string, mixed> */

    public function rules(): array

    {

        $slugs = app(CandidatePlanService::class)->purchasableSlugs();



        return [

            'plan_key' => [

                'required',

                'string',

                Rule::in($slugs),

            ],

        ];

    }

}


