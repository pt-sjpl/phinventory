<?

namespace App\Helpers;

class AssetTypeHelper
{
  public static function determineType(?string $asset_type): ?string
  {
    switch ($asset_type) {
      case 'App\Models\Asset':
        return 'Asset';
        break;
      default:
        return null;
        break;
    }
  }
}
