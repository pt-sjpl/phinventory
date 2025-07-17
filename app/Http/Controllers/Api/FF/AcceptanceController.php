<?php

namespace App\Http\Controllers\Api\FF;

use App\Helpers\ApiFF;
use App\Helpers\AssetTypeHelper;
use App\Http\Controllers\Controller;
use App\Models\CheckoutAcceptance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class AcceptanceController extends Controller
{
    public function index(Request $request, $user)
    {
        $user = User::findOrFail($user);
        $acceptances = CheckoutAcceptance::with([
            'checkoutable.model' => fn($query) => $query->with(['category', 'fieldset.fields']),
        ])->forUser($user)->pending()->get();

        return Response::json([
            'total' =>  $acceptances->count(),
            'rows' => $acceptances->map(function ($acceptance) {
                $result = [
                    'id' => $acceptance->id,
                    'assigned_at' => $acceptance->created_at,
                    'name' => $acceptance->checkoutable?->name,
                    'tag' => $acceptance->checkoutable?->asset_tag,
                    'company' => $acceptance->checkoutable?->company?->name,
                    'type' => AssetTypeHelper::determineType(asset_type: $acceptance->checkoutable_type),
                    'model' => $acceptance->checkoutable?->model?->name,
                    'category' => $acceptance->checkoutable?->model?->category?->name,
                ];
                $result['fieldsets'] = $acceptance
                    ->checkoutable
                    ?->model
                    ?->fieldset
                    ?->fields
                    ->mapWithKeys(function ($field) use ($acceptance) {
                        $key = Str::slug($field->name, '_');
                        $column = "_snipeit_{$key}_{$field->id}";
                        return [
                            $key => $acceptance->checkoutable?->$column,
                        ];
                    })
                    ->toArray();
                return $result;
            })->values(),
        ]);
    }

    public static function acceptBulk(Request $request, $user)
    {
        $data = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:checkout_acceptances,id',
        ]);
        $user = User::findOrFail($user);
        try {
            DB::beginTransaction();
            CheckoutAcceptance::whereIn('id', $data['ids'])->forUser($user)->pending()->update([
                'accepted_at' => Carbon::now(),
            ]);
            DB::commit();
            return ApiFF::api_success(message: 'Assets accepted');
        } catch (\Throwable $th) {
            DB::rollBack();
            //throw $th;
            return ApiFF::api_error(message: $th->getMessage(), code: 500);
        }
    }

    public static function declineBulk(Request $request, $user)
    {
        $data = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:checkout_acceptances,id',
        ]);
        $user = User::findOrFail($user);
        try {
            DB::beginTransaction();
            CheckoutAcceptance::whereIn('id', $data['ids'])->forUser($user)->pending()->update([
                'declined_at' => Carbon::now(),
            ]);
            DB::commit();
            return ApiFF::api_success(message: 'Assets declined');
        } catch (\Throwable $th) {
            DB::rollBack();
            //throw $th;
            return ApiFF::api_error(message: $th->getMessage(), code: 500);
        }
    }
}
