<?php
/**
 * Bluebook API - SPA Data Endpoint
 * PHP 8 Compatible
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

include("../includes/config.php");

$action = isset($_GET['action']) ? $_GET['action'] : '';
$response = ['success' => false, 'data' => [], 'message' => ''];

switch ($action) {
    case 'getTypes':
        // Get car types (pickup and sedan)
        $response['success'] = true;
        $response['data'] = [
            ['id' => 1, 'name' => 'รถกระบะ', 'icon' => 'fa-truck-pickup', 'image' => 'pickup.png'],
            ['id' => 2, 'name' => 'รถเก๋ง', 'icon' => 'fa-car', 'image' => 'car.png']
        ];
        break;

    case 'getBrands':
        $cid = isset($_GET['cid']) ? sqlEscape($_GET['cid']) : '';
        $brands = [];

        // Predefined order for common brands
        if ($cid == '1') {
            // Pickup
            $arryCar = ['TOYOTA', 'ISUZU', 'NISSAN', 'MITSUBISHI', 'MAZDA', 'FORD'];
            $sWhere = " AND carBrand NOT IN ('TOYOTA','ISUZU','NISSAN','MITSUBISHI','MAZDA','FORD')";
        } else {
            // Sedan
            $arryCar = ['HONDA', 'TOYOTA', 'NISSAN', 'MITSUBISHI', 'MAZDA', 'FORD', 'BMW', 'MERCEDES-BENZ'];
            $sWhere = " AND carBrand NOT IN ('HONDA','TOYOTA','NISSAN','MITSUBISHI','MAZDA','FORD','BMW','MERCEDES-BENZ')";
        }

        // Get additional brands from database
        $sql = sqlQuery("SELECT carBrand FROM " . DB_TABLE_NAME . " WHERE carID='" . sqlEscape($bluebook_year) . "' AND carType='" . $cid . "' " . $sWhere . " GROUP BY carBrand ORDER BY carBrand ASC");
        if (sqlNumRows($sql) > 0) {
            while ($row = sqlFetch($sql)) {
                $arryCar[] = trim($row['carBrand']);
            }
        }

        foreach ($arryCar as $brand) {
            $iconFile = strtolower($brand) . '.png';
            $hasIcon = file_exists("../icons/" . $iconFile);
            $brands[] = [
                'name' => $brand,
                'icon' => $hasIcon ? $iconFile : ($cid == '1' ? 'pickup.png' : 'car.png')
            ];
        }

        $response['success'] = true;
        $response['data'] = $brands;
        break;

    case 'getModels':
        $cid = isset($_GET['cid']) ? sqlEscape($_GET['cid']) : '';
        $bid = isset($_GET['bid']) ? sqlEscape($_GET['bid']) : '';
        $models = [];

        $sql = sqlQuery("SELECT carModel FROM " . DB_TABLE_NAME . " WHERE carID='" . sqlEscape($bluebook_year) . "' AND carType='" . $cid . "' AND carBrand='" . $bid . "' GROUP BY carModel ORDER BY carModel ASC");
        if (sqlNumRows($sql) > 0) {
            while ($row = sqlFetch($sql)) {
                $models[] = ['name' => trim($row['carModel'])];
            }
        }

        $response['success'] = true;
        $response['data'] = $models;
        break;

    case 'getYears':
        $cid = isset($_GET['cid']) ? sqlEscape($_GET['cid']) : '';
        $bid = isset($_GET['bid']) ? sqlEscape($_GET['bid']) : '';
        $mid = isset($_GET['mid']) ? sqlEscape($_GET['mid']) : '';
        $years = [];

        $sql = sqlQuery("SELECT carYear FROM " . DB_TABLE_NAME . " WHERE carID='" . sqlEscape($bluebook_year) . "' AND carType='" . $cid . "' AND carBrand='" . $bid . "' AND carModel='" . $mid . "' GROUP BY carYear ORDER BY carYear DESC");
        if (sqlNumRows($sql) > 0) {
            while ($row = sqlFetch($sql)) {
                $years[] = ['year' => trim($row['carYear'])];
            }
        }

        $response['success'] = true;
        $response['data'] = $years;
        break;

    case 'getSubModels':
        $cid = isset($_GET['cid']) ? sqlEscape($_GET['cid']) : '';
        $bid = isset($_GET['bid']) ? sqlEscape($_GET['bid']) : '';
        $mid = isset($_GET['mid']) ? sqlEscape($_GET['mid']) : '';
        $yy = isset($_GET['yy']) ? sqlEscape($_GET['yy']) : '';
        $submodels = [];

        $sql = sqlQuery("SELECT ID, carSubModel, carGear, carPrice, car_picture FROM " . DB_TABLE_NAME . " WHERE carID='" . sqlEscape($bluebook_year) . "' AND carType='" . $cid . "' AND carBrand='" . $bid . "' AND carModel='" . $mid . "' AND carYear='" . $yy . "' ORDER BY carSubModel, carGear ASC");
        if (sqlNumRows($sql) > 0) {
            while ($row = sqlFetch($sql)) {
                $submodels[] = [
                    'id' => $row['ID'],
                    'submodel' => trim($row['carSubModel']),
                    'gear' => trim($row['carGear']),
                    'price' => (int) $row['carPrice'],
                    'hasPicture' => !empty(trim($row['car_picture']))
                ];
            }
        }

        $response['success'] = true;
        $response['data'] = $submodels;
        break;

    case 'getPrice':
        $carid = isset($_GET['carid']) ? sqlEscape($_GET['carid']) : '';

        $sql = sqlQuery("SELECT carSubModel, carGear, carPrice, carCode, car_picture FROM " . DB_TABLE_NAME . " WHERE ID='" . $carid . "'");
        if (sqlNumRows($sql) > 0) {
            $row = sqlFetch($sql);
            $carPicture = trim($row['car_picture']);

            $response['success'] = true;
            $response['data'] = [
                'code' => trim($row['carCode']),
                'submodel' => trim($row['carSubModel']),
                'gear' => trim($row['carGear']),
                'price' => (int) $row['carPrice'],
                'picture' => $carPicture ? $url_image . '/' . $carPicture : ''
            ];
        } else {
            $response['message'] = 'Car not found';
        }
        break;

    case 'getVersion':
        $response['success'] = true;
        $response['data'] = [
            'version' => $bluebook_year,
            'webname' => $webname
        ];
        break;

    default:
        $response['message'] = 'Invalid action';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>