<?php

namespace App\Http\Controllers\Api\FF;

use App\Events\CheckoutAccepted;
use App\Events\CheckoutDeclined;
use App\Helpers\ApiFF;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SettingsController;
use App\Http\Transformers\AccessoriesTransformer;
use App\Http\Transformers\AssetsTransformer;
use App\Http\Transformers\ConsumablesTransformer;
use App\Http\Transformers\LicenseSeatsTransformer;
use App\Mail\CheckoutAcceptanceResponseMail;
use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\CheckoutAcceptance;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\LicenseSeat;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class AcceptanceController extends Controller
{
    public function index(Request $request, $userId)
    {
        // 1) Resolve the user
        $user = User::findOrFail($userId);

        // 2) Parse and normalize filter param (defaults to ['asset'])
        $raw = $request->query('filter', '');
        $types = $raw !== ''
            ? array_map('trim', explode(',', $raw))
            : ['asset'];
        $types = array_map('strtolower', $types);

        // 3) Map string → model classes
        $typeMap = [
            'asset'       => Asset::class,
            'licenseseat' => LicenseSeat::class,
            'licenseSeat' => LicenseSeat::class, // support camel‑case too
            'accessory'   => Accessory::class,
            'consumable'  => Consumable::class,
        ];

        // 4) Pick the valid classes, default back to Asset if none matched
        $selected = [];
        foreach ($types as $t) {
            if (isset($typeMap[$t])) {
                $selected[$typeMap[$t]] = true;
            }
        }
        if (empty($selected)) {
            $selected = [Asset::class => true];
        }

        // 5) Base query + restrict to those checkoutable types
        $query = CheckoutAcceptance::forUser($user)
            ->pending()
            ->whereIn('checkoutable_type', array_keys($selected));

        // 6) Only eager‑load the relations each selected type needs
        $relations = [
            Asset::class      => ['model.category', 'model.fieldset.fields'],
            LicenseSeat::class => ['license.category'],
            Accessory::class  => ['category'],
            Consumable::class => ['category'],
        ];

        $acceptances = $query->with([
            'checkoutable' => function (MorphTo $morph) use ($selected, $relations) {
                $toLoad = [];
                foreach (array_keys($selected) as $class) {
                    $toLoad[$class] = $relations[$class] ?? [];
                }
                $morph->morphWith($toLoad);
            },
        ])->get();

        // 7) Prepare your transformers
        $transformers = [
            Asset::class       => [new AssetsTransformer,      'transformAsset'],
            LicenseSeat::class => [new LicenseSeatsTransformer, 'transformLicenseSeat'],
            Accessory::class   => [new AccessoriesTransformer, 'transformAccessory'],
            Consumable::class  => [new ConsumablesTransformer, 'transformConsumable'],
        ];

        // 8) Map to the final payload, injecting a "type" field
        $rows = $acceptances->map(function ($acceptance) use ($transformers) {
            $item  = $acceptance->checkoutable;
            $class = get_class($item);

            if (! isset($transformers[$class])) {
                return null; // or handle unexpected
            }

            [$tx, $method] = $transformers[$class];
            $payload = $tx->$method($item);

            // add a JSON discriminator
            $payload['type'] = lcfirst(class_basename($class));

            return [
                'id'          => $acceptance->id,
                'assigned_at' => $acceptance->created_at,
                'checkoutable' => $payload,
            ];
        })->filter()->values();

        // 9) Return JSON
        return response()->json([
            'total' => $rows->count(),
            'rows'  => $rows,
        ]);
    }

    public function acceptBulk(Request $request, $user)
    {
        $data = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:checkout_acceptances,id',
            'note'  => 'nullable|string',
        ]);
        $user = User::findOrFail($user);
        try {
            DB::beginTransaction();

            $acceptances = CheckoutAcceptance::whereIn('id', $data['ids'])->forUser($user)->pending()->get();

            foreach ($acceptances as $acceptance) {
                $item = $acceptance->checkoutable_type::find($acceptance->checkoutable_id);
                $display_model = '';
                $pdf_view_route = '';
                $pdf_filename = 'accepted-eula-' . date('Y-m-d-h-i-s') . '.pdf';
                $sig_filename = '';

                $assigned_user = User::find($acceptance->assigned_to_id);
                // this is horrible
                switch ($acceptance->checkoutable_type) {
                    case 'App\Models\Asset':
                        $pdf_view_route = 'account.accept.accept-asset-eula';
                        $asset_model = AssetModel::find($item->model_id);
                        if (!$asset_model) {
                            throw new Exception(trans('admin/models/message.does_not_exist'));
                        }
                        $display_model = $asset_model->name;
                        break;

                    case 'App\Models\Accessory':
                        $pdf_view_route = 'account.accept.accept-accessory-eula';
                        $accessory = Accessory::find($item->id);
                        $display_model = $accessory->name;
                        break;

                    case 'App\Models\LicenseSeat':
                        $pdf_view_route = 'account.accept.accept-license-eula';
                        $license = License::find($item->license_id);
                        $display_model = $license->name;
                        break;

                    case 'App\Models\Component':
                        $pdf_view_route = 'account.accept.accept-component-eula';
                        $component = Component::find($item->id);
                        $display_model = $component->name;
                        break;

                    case 'App\Models\Consumable':
                        $pdf_view_route = 'account.accept.accept-consumable-eula';
                        $consumable = Consumable::find($item->id);
                        $display_model = $consumable->name;
                        break;
                }

                $branding_settings = SettingsController::getPDFBranding();

                $path_logo = "";

                // Check for the PDF logo path and use that, otherwise use the regular logo path
                if (!is_null($branding_settings->acceptance_pdf_logo)) {
                    $path_logo = public_path() . '/uploads/' . $branding_settings->acceptance_pdf_logo;
                } elseif (!is_null($branding_settings->logo)) {
                    $path_logo = public_path() . '/uploads/' . $branding_settings->logo;
                }

                $data = [
                    'item_tag' => $item->asset_tag,
                    'item_model' => $display_model,
                    'item_serial' => $item->serial,
                    'item_status' => $item->assetstatus?->name,
                    'eula' => $item->getEula(),
                    'note' => $request->note,
                    'check_out_date' => Carbon::parse($acceptance->created_at)->format('Y-m-d'),
                    'accepted_date' => Carbon::parse($acceptance->accepted_at)->format('Y-m-d'),
                    'assigned_to' => $assigned_user->present()->fullName,
                    'company_name' => $branding_settings->site_name,
                    'signature' => ($sig_filename) ? storage_path() . '/private_uploads/signatures/' . $sig_filename : null,
                    'logo' => $path_logo,
                    'date_settings' => $branding_settings->date_display_format,
                ];

                if ($pdf_view_route != '') {
                    Log::debug($pdf_filename . ' is the filename, and the route was specified.');
                    $pdf = Pdf::loadView($pdf_view_route, $data);
                    Storage::put('private_uploads/eula-pdfs/' . $pdf_filename, $pdf->output());
                }

                $acceptance->accept($sig_filename, $item->getEula(), $pdf_filename, $request->note);

                try {
                    // Send Email
                    // $acceptance->notify(new AcceptanceAssetAcceptedNotification($data));
                } catch (\Exception $e) {
                    // Log::warning($e);
                }
                event(new CheckoutAccepted($acceptance));

                if ($acceptance->alert_on_response_id) {
                    try {
                        $recipient = User::find($acceptance->alert_on_response_id);

                        if ($recipient) {
                            Mail::to($recipient)->send(new CheckoutAcceptanceResponseMail(
                                $acceptance,
                                $recipient,
                                $request->input('asset_acceptance') === 'accepted',
                            ));
                        }
                    } catch (Exception $e) {
                        Log::warning($e);
                    }
                }
            }

            DB::commit();
            return ApiFF::api_success(message: trans('admin/users/message.accepted'));
        } catch (\Throwable $th) {
            DB::rollBack();
            //throw $th;
            return ApiFF::api_error(message: $th->getMessage(), code: 500);
        }
    }

    public function declineBulk(Request $request, $user)
    {
        $data = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:checkout_acceptances,id',
            'note'  => 'nullable|string',
        ]);
        $user = User::findOrFail($user);
        try {
            DB::beginTransaction();

            $acceptances = CheckoutAcceptance::whereIn('id', $data['ids'])->forUser($user)->pending()->get();

            foreach ($acceptances as $acceptance) {
                $item = $acceptance->checkoutable_type::find($acceptance->checkoutable_id);
                $display_model = '';
                $pdf_view_route = '';
                $pdf_filename = 'accepted-eula-' . date('Y-m-d-h-i-s') . '.pdf';
                $sig_filename = '';

                /**
                 * Check for the eula-pdfs directory
                 */
                if (! Storage::exists('private_uploads/eula-pdfs')) {
                    Storage::makeDirectory('private_uploads/eula-pdfs', 775);
                }

                // Format the data to send the declined notification
                $branding_settings = SettingsController::getPDFBranding();

                // This is the most horriblest
                switch ($acceptance->checkoutable_type) {
                    case 'App\Models\Asset':
                        $asset_model = AssetModel::find($item->model_id);
                        $display_model = $asset_model->name;
                        $assigned_to = User::find($acceptance->assigned_to_id)->present()->fullName;
                        break;

                    case 'App\Models\Accessory':
                        $accessory = Accessory::find($item->id);
                        $display_model = $accessory->name;
                        $assigned_to = User::find($acceptance->assigned_to_id)->present()->fullName;
                        break;

                    case 'App\Models\LicenseSeat':
                        $assigned_to = User::find($acceptance->assigned_to_id)->present()->fullName;
                        break;

                    case 'App\Models\Component':
                        $assigned_to = User::find($acceptance->assigned_to_id)->present()->fullName;
                        break;

                    case 'App\Models\Consumable':
                        $consumable = Consumable::find($item->id);
                        $display_model = $consumable->name;
                        $assigned_to = User::find($acceptance->assigned_to_id)->present()->fullName;
                        break;
                }

                $data = [
                    'item_tag' => $item->asset_tag,
                    'item_model' => $display_model,
                    'item_serial' => $item->serial,
                    'item_status' => $item->assetstatus?->name,
                    'note' => $request->note,
                    'declined_date' => Carbon::parse($acceptance->declined_at)->format('Y-m-d'),
                    'signature' => ($sig_filename) ? storage_path() . '/private_uploads/signatures/' . $sig_filename : null,
                    'assigned_to' => $assigned_to,
                    'company_name' => $branding_settings->site_name,
                    'date_settings' => $branding_settings->date_display_format,
                ];

                if ($pdf_view_route != '') {
                    Log::debug($pdf_filename . ' is the filename, and the route was specified.');
                    $pdf = Pdf::loadView($pdf_view_route, $data);
                    Storage::put('private_uploads/eula-pdfs/' . $pdf_filename, $pdf->output());
                }

                $acceptance->decline($sig_filename, $request->note);
                try {
                    // ini send email
                    // $acceptance->notify(new AcceptanceAssetDeclinedNotification($data));
                } catch (\Throwable $th) {
                    //throw $th;
                    // Log::warning($th);
                }
                event(new CheckoutDeclined($acceptance));
            }
            DB::commit();
            return ApiFF::api_success(message: trans('admin/users/message.declined'));
        } catch (\Throwable $th) {
            DB::rollBack();
            //throw $th;
            return ApiFF::api_error(message: $th->getMessage(), code: 500);
        }
    }
}
