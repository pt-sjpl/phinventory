<?php

namespace App\Http\Controllers\Api\FF;

use App\Events\CheckoutAccepted;
use App\Events\CheckoutDeclined;
use App\Helpers\ApiFF;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SettingsController;
use App\Http\Transformers\AssetsTransformer;
use App\Mail\CheckoutAcceptanceResponseMail;
use App\Models\Accessory;
use App\Models\AssetModel;
use App\Models\CheckoutAcceptance;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class AcceptanceController extends Controller
{
    public function index(Request $request, $user)
    {
        $user = User::findOrFail($user);
        $acceptances = CheckoutAcceptance::with([
            'checkoutable.model' => fn($query) => $query->with(['category', 'fieldset.fields']),
        ])->forUser($user)->pending()->get();

        return Response::json([
            'total' => $acceptances->count(),
            'rows'  => $acceptances->map(function ($acceptance) {
                $result = [
                    'id'          => $acceptance->id,
                    'assigned_at' => $acceptance->created_at,
                    'asset'       => (new AssetsTransformer)->transformAsset($acceptance->checkoutable),
                ];
                return $result;
            })->values(),
        ]);
    }

    public static function acceptBulk(Request $request, $user)
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

    public static function declineBulk(Request $request, $user)
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
