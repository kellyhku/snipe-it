<?php

namespace App\Helpers;

use DB;
use App\Models\Statuslabel;
use App\Models\Location;
use App\Models\Department;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\User;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Depreciation;
use App\Models\CustomFieldset;
use App\Models\CustomField;
use App\Models\Component;
use App\Models\Accessory;
use App\Models\Consumable;
use App\Models\Asset;
use App\Models\Setting;
use Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class Helper
{


    /**
     * Simple helper to invoke the markdown parser
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.0]
     * @return String
     */
    public static function parseEscapedMarkedown($str)
    {
        $Parsedown = new \Parsedown();

        if ($str) {
            return $Parsedown->text(e($str));
        }
    }


    /**
     * The importer has formatted number strings since v3,
     * so the value might be a string, or an integer.
     * If it's a number, format it as a string.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.0]
     * @return String
     */
    public static function formatCurrencyOutput($cost)
    {
        if (is_numeric($cost)) {
            return number_format($cost, 2, '.', '');
        }
        // It's already been parsed.
        return $cost;
    }


    /**
     * Static colors for pie charts.
     * This is inelegant, and could be refactored later.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.3]
     * @return Array
     */
    public static function chartColors()
    {
        $colors = [
            '#f56954',
            '#00a65a',
            '#f39c12',
            '#00c0ef',
            '#3c8dbc',
            '#d2d6de',
            '#3c8dbc',
            '#3c8dbc',
            '#3c8dbc',

        ];
        return $colors;
    }


    /**
     * Static background (highlight) colors for pie charts
     * This is inelegant, and could be refactored later.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.2]
     * @return Array
     */
    public static function chartBackgroundColors()
    {
        $colors = [
            '#f56954',
            '#00a65a',
            '#f39c12',
            '#00c0ef',
            '#3c8dbc',
            '#d2d6de',
            '#3c8dbc',
            '#3c8dbc',
            '#3c8dbc',

        ];
        return $colors;
    }


    /**
     * Format currency using comma for thousands until local info is property used.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.7]
     * @return String
     */
    public static function ParseFloat($floatString)
    {
        $LocaleInfo = localeconv();
        $floatString = str_replace(",", "", $floatString);
        $floatString = str_replace($LocaleInfo["decimal_point"], ".", $floatString);
        // Strip Currency symbol
        // If no currency symbol is set, default to $ because Murica
        $currencySymbol = $LocaleInfo['currency_symbol'];
        if (empty($currencySymbol)) {
            $currencySymbol = '$';
        }

        $floatString = str_replace($currencySymbol, '', $floatString);
        return floatval($floatString);
    }


    /**
     * Get the list of models in an array to make a dropdown menu
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.5]
     * @return Array
     */
    public static function modelList()
    {
        $models = AssetModel::with('manufacturer')->get();
        $model_array[''] = trans('general.select_model');
        foreach ($models as $model) {
            $model_array[$model->id] = $model->present()->modelName();
        }
        return $model_array;
    }

    /**
     * Get the list of companies in an array to make a dropdown menu
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.5]
     * @return Array
     */
    public static function companyList()
    {
        $company_list = array('' => trans('general.select_company')) + DB::table('companies')
                ->orderBy('name', 'asc')
                ->pluck('name', 'id')
                ->toArray();
        return $company_list;
    }


    /**
     * Get the list of categories in an array to make a dropdown menu
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.5]
     * @return Array
     */
    public static function categoryList($category_type = null)
    {
        $categories = Category::orderBy('name', 'asc')
            ->whereNull('deleted_at')
            ->orderBy('name', 'asc');
        if (!empty($category_type)) {
            $categories = $categories->where('category_type', '=', $category_type);
        }
        $category_list = array('' => trans('general.select_category')) + $categories->pluck('name', 'id')->toArray();
        return $category_list;
    }


    /**
     * Get the list of categories in an array to make a dropdown menu
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.5]
     * @return Array
     */
    public static function departmentList()
    {
        $departments = Department::orderBy('name', 'asc')
            ->whereNull('deleted_at')
            ->orderBy('name', 'asc');

        return array('' => trans('general.select_department')) + $departments->pluck('name', 'id')->toArray();

    }


    /**
     * Get the list of suppliers in an array to make a dropdown menu
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.5]
     * @return Array
     */
    public static function suppliersList()
    {
        $supplier_list = array('' => trans('general.select_supplier')) + Supplier::orderBy('name', 'asc')
                ->orderBy('name', 'asc')
                ->pluck('name', 'id')->toArray();
        return $supplier_list;
    }


    /**
     * Get the list of status labels in an array to make a dropdown menu
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.5]
     * @return Array
     */
    public static function statusLabelList()
    {
        $statuslabel_list = array('' => trans('general.select_statuslabel')) + Statuslabel::orderBy('deployable', 'desc')
                ->pluck('name', 'id')->toArray();
        return $statuslabel_list;
    }


    /**
     * Get the list of locations in an array to make a dropdown menu
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.5]
     * @return Array
     */
    public static function locationsList()
    {
        $location_list = array('' => trans('general.select_location')) + Location::orderBy('name', 'asc')
                ->pluck('name', 'id')->toArray();
        return $location_list;
    }


    /**
     * Get the list of manufacturers in an array to make a dropdown menu
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.5]
     * @return Array
     */
    public static function manufacturerList()
    {
        $manufacturer_list = array('' => trans('general.select_manufacturer')) +
            Manufacturer::orderBy('name', 'asc')
                ->pluck('name', 'id')->toArray();
        return $manufacturer_list;
    }

    /**
     * Get the list of status label types in an array to make a dropdown menu
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.5]
     * @return Array
     */
    public static function statusTypeList()
    {
        $statuslabel_types =
            array('' => trans('admin/hardware/form.select_statustype'))
            + array('deployable' => trans('admin/hardware/general.deployable'))
            + array('pending' => trans('admin/hardware/general.pending'))
            + array('undeployable' => trans('admin/hardware/general.undeployable'))
            + array('archived' => trans('admin/hardware/general.archived'));
        return $statuslabel_types;
    }

    /**
     * Get the list of managers in an array to make a dropdown menu
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.5]
     * @return Array
     */
    public static function managerList()
    {
        $manager_list = array('' => trans('general.select_user')) +
            User::where('deleted_at', '=', null)
                ->orderBy('last_name', 'asc')
                ->orderBy('first_name', 'asc')->get()
                ->pluck('complete_name', 'id')->toArray();

        return $manager_list;
    }

    /**
     * Get the list of depreciations in an array to make a dropdown menu
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.5]
     * @return Array
     */
    public static function depreciationList()
    {
        $depreciation_list = ['' => 'Do Not Depreciate'] + Depreciation::orderBy('name', 'asc')
                ->pluck('name', 'id')->toArray();
        return $depreciation_list;
    }

    /**
     * Get the list of category types in an array to make a dropdown menu
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.5]
     * @return Array
     */
    public static function categoryTypeList()
    {
        $category_types = array('' => '', 'accessory' => 'Accessory', 'asset' => 'Asset', 'consumable' => 'Consumable', 'component' => 'Component');
        $category_types = static::translateCategoriesType($category_types);
        return $category_types;
    }

    public static function translateCategoriesType($categories)
    {
        $translate = [
            'accessory' => '附属品',
            'asset' => '资产',
            'consumable' => '消耗品',
            'component' => '组件',
        ];

        if (is_array($categories)) {
            return array_merge($categories, $translate);
        }

        return array_key_exists($categories, $translate) ? $translate[$categories] : $categories;


    }


    /**
     * Get the list of users in an array to make a dropdown menu
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.5]
     * @return Array
     */
    public static function usersList()
    {
        $users_list = array('' => trans('general.select_user')) +
            Company::scopeCompanyables(User::where('deleted_at', '=', null))
                ->where('show_in_list', '=', 1)
                ->orderBy('last_name', 'asc')
                ->orderBy('first_name', 'asc')->get()
                ->pluck('complete_name', 'id')->toArray();

        return $users_list;
    }

    /**
     * Get the list of assets in an array to make a dropdown menu
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.5]
     * @return Array
     */
    public static function assetsList()
    {
        $assets_list = array('' => trans('general.select_asset')) + Asset::orderBy('name', 'asc')
                ->whereNull('deleted_at')
                ->pluck('name', 'id')->toArray();
        return $assets_list;
    }

    /**
     * Get the detailed list of assets in an array to make a dropdown menu
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.5]
     * @return array
     */
    public static function detailedAssetList()
    {
        $assets = array('' => trans('general.select_asset')) + Company::scopeCompanyables(Asset::with('assignedTo', 'model'), 'assets.company_id')->get()->pluck('detailed_name', 'id')->toArray();
        return $assets;
    }


    /**
     * Get the list of custom fields in an array to make a dropdown menu
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v2.5]
     * @return Array
     */
    public static function customFieldsetList()
    {
        $customfields = array('' => trans('admin/models/general.no_custom_field')) + CustomFieldset::pluck('name', 'id')->toArray();
        return $customfields;
    }

    /**
     * Get the list of custom field formats in an array to make a dropdown menu
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.4]
     * @return Array
     */
    public static function predefined_formats()
    {
        $keys = array_keys(CustomField::$PredefinedFormats);
        $stuff = array_combine($keys, $keys);
        return $stuff + ["" => trans('admin/custom_fields/general.custom_format')];
    }

    /**
     * Get the list of barcode dimensions
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.3]
     * @return Array
     */
    public static function barcodeDimensions($barcode_type = 'QRCODE')
    {
        if ($barcode_type == 'C128') {
            $size['height'] = '-1';
            $size['width'] = '-10';
        } elseif ($barcode_type == 'PDF417') {
            $size['height'] = '-3';
            $size['width'] = '-10';
        } else {
            $size['height'] = '-3';
            $size['width'] = '-3';
        }
        return $size;
    }

    /**
     * Generates a random string
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return Array
     */
    public static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }


    /**
     * This nasty little method gets the low inventory info for the
     * alert dropdown
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return Array
     */
    public static function checkLowInventory()
    {
        $consumables = Consumable::withCount('consumableAssignments')->whereNotNull('min_amt')->get();
        $accessories = Accessory::withCount('users')->whereNotNull('min_amt')->get();
        $components = Component::withCount('assets')->whereNotNull('min_amt')->get();

        $avail_consumables = 0;
        $items_array = array();
        $all_count = 0;

        foreach ($consumables as $consumable) {
            $avail = $consumable->qty - $consumable->consumable_assignment_count;  //$consumable->numRemaining();
            if ($avail < ($consumable->min_amt) + \App\Models\Setting::getSettings()->alert_threshold) {
                if ($consumable->qty > 0) {
                    $percent = number_format((($avail / $consumable->qty) * 100), 0);
                } else {
                    $percent = 100;
                }

                $items_array[$all_count]['id'] = $consumable->id;
                $items_array[$all_count]['name'] = $consumable->name;
                $items_array[$all_count]['type'] = 'consumables';
                $items_array[$all_count]['percent'] = $percent;
                $items_array[$all_count]['remaining'] = $avail;
                $items_array[$all_count]['min_amt'] = $consumable->min_amt;
                $all_count++;
            }


        }

        foreach ($accessories as $accessory) {
            $avail = $accessory->qty - $accessory->users_count;
            if ($avail < ($accessory->min_amt) + \App\Models\Setting::getSettings()->alert_threshold) {

                if ($accessory->qty > 0) {
                    $percent = number_format((($avail / $accessory->qty) * 100), 0);
                } else {
                    $percent = 100;
                }

                $items_array[$all_count]['id'] = $accessory->id;
                $items_array[$all_count]['name'] = $accessory->name;
                $items_array[$all_count]['type'] = 'accessories';
                $items_array[$all_count]['percent'] = $percent;
                $items_array[$all_count]['remaining'] = $avail;
                $items_array[$all_count]['min_amt'] = $accessory->min_amt;
                $all_count++;
            }

        }

        foreach ($components as $component) {
            $avail = $component->qty - $component->assets_count;
            if ($avail < ($component->min_amt) + \App\Models\Setting::getSettings()->alert_threshold) {
                if ($component->qty > 0) {
                    $percent = number_format((($avail / $component->qty) * 100), 0);
                } else {
                    $percent = 100;
                }

                $items_array[$all_count]['id'] = $component->id;
                $items_array[$all_count]['name'] = $component->name;
                $items_array[$all_count]['type'] = 'components';
                $items_array[$all_count]['percent'] = $percent;
                $items_array[$all_count]['remaining'] = $avail;
                $items_array[$all_count]['min_amt'] = $component->min_amt;
                $all_count++;
            }

        }


        return $items_array;


    }


    /**
     * Check if the file is an image, so we can show a preview
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @param File $file
     * @return String | Boolean
     */
    public static function checkUploadIsImage($file)
    {
        $finfo = @finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
        $filetype = @finfo_file($finfo, $file);
        finfo_close($finfo);

        if (($filetype == "image/jpeg") || ($filetype == "image/jpg") || ($filetype == "image/png") || ($filetype == "image/bmp") || ($filetype == "image/gif")) {
            return $filetype;
        }

        return false;
    }

    /**
     * Walks through the permissions in the permissions config file and determines if
     * permissions are granted based on a $selected_arr array.
     *
     * The $permissions array is a multidimensional array broke down by section.
     * (Licenses, Assets, etc)
     *
     * The $selected_arr should be a flattened array that contains just the
     * corresponding permission name and a true or false boolean to determine
     * if that group/user has been granted that permission.
     *
     * @author [A. Gianotto] [<snipe@snipe.net]
     * @param array $permissions
     * @param array $selected_arr
     * @since [v1.0]
     * @return Array
     */
    public static function selectedPermissionsArray($permissions, $selected_arr = array())
    {


        $permissions_arr = array();

        foreach ($permissions as $permission) {

            for ($x = 0; $x < count($permission); $x++) {
                $permission_name = $permission[$x]['permission'];

                if ($permission[$x]['display'] === true) {

                    if ($selected_arr) {
                        if (array_key_exists($permission_name, $selected_arr)) {
                            $permissions_arr[$permission_name] = $selected_arr[$permission_name];
                        } else {
                            $permissions_arr[$permission_name] = '0';
                        }
                    } else {
                        $permissions_arr[$permission_name] = '0';
                    }
                }


            }


        }

        return $permissions_arr;
    }

    /**
     * Introspects into the model validation to see if the field passed is required.
     * This is used by the blades to add a required class onto the HTML element.
     * This isn't critical, but is helpful to keep form fields in sync with the actual
     * model level validation.
     *
     * This does not currently handle form request validation requiredness :(
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return boolean
     */
    public static function checkIfRequired($class, $field)
    {
        $rules = $class::rules();
        foreach ($rules as $rule_name => $rule) {
            if ($rule_name == $field) {
                if (strpos($rule, 'required') === false) {
                    return false;
                } else {
                    return true;
                }

            }
        }
    }


    /**
     * Check to see if the given key exists in the array, and trim excess white space before returning it
     *
     * @author Daniel Melzter
     * @since 3.0
     * @param $array array
     * @param $key string
     * @param $default string
     * @return string
     */
    public static function array_smart_fetch(array $array, $key, $default = '')
    {
        array_change_key_case($array, CASE_LOWER);
        return array_key_exists(strtolower($key), array_change_key_case($array)) ? e(trim($array[$key])) : $default;
    }


    /**
     * Gracefully handle decrypting the legacy data (encrypted via mcrypt) and use the new
     * decryption method instead.
     *
     * This is not currently used, but will be.
     *
     * @author A. Gianotto
     * @since 3.6
     * @param CustomField $field
     * @param String $string
     * @return string
     */
    public static function gracefulDecrypt(CustomField $field, $string)
    {

        if ($field->isFieldDecryptable($string)) {

            try {
                Crypt::decrypt($string);
                return Crypt::decrypt($string);

            } catch (DecryptException $e) {
                return 'Error Decrypting: ' . $e->getMessage();
            }

        }
        return $string;

    }


    public static function formatStandardApiResponse($status, $payload = null, $messages = null)
    {

        $array['status'] = $status;
        $array['messages'] = $messages;
        if (($messages) && (count($messages) > 0)) {
            $array['messages'] = $messages;
        }
        ($payload) ? $array['payload'] = $payload : $array['payload'] = null;
        return $array;
    }


    /*
    Possible solution for unicode fieldnames
    */
    public static function make_slug($string)
    {
        return preg_replace('/\s+/u', '_', trim($string));
    }


    public static function getFormattedDateObject($date, $type = 'datetime', $array = true)
    {

        if ($date == '') {
            return null;
        }

        $settings = Setting::getSettings();
        $tmp_date = new \Carbon($date);

        if ($type == 'datetime') {
            $dt['datetime'] = $tmp_date->format('Y-m-d H:i:s');
            $dt['formatted'] = $tmp_date->format($settings->date_display_format . ' ' . $settings->time_display_format);
        } else {
            $dt['date'] = $tmp_date->format('Y-m-d');
            $dt['formatted'] = $tmp_date->format($settings->date_display_format);
        }

        if ($array == 'true') {
            return $dt;
        }
        return $dt['formatted'];

    }


}
