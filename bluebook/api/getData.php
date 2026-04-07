<?php
/**
 * Bluebook API - SPA Data Endpoint
 * PHP 8 Compatible
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
include("../includes/config.php");
$action = isset($_GET['action']) ? $_GET['action'] : '';
$response = array('success' => false, 'data' => array(), 'message' => '');
function bluebookFetchAll($sql, $types = '', $params = array())
{
    global $objConnect;
    $stmt = mysqli_prepare($objConnect, $sql);
    if (!$stmt) {
        return array();
    }
    if (!empty($types) && !empty($params)) {
        $bindParams = array($types);
        foreach ($params as $key => $value) {
            $bindParams[] = &$params[$key];
        }
        call_user_func_array('mysqli_stmt_bind_param', $bindParams);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = array();
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_free_result($result);
    }
    mysqli_stmt_close($stmt);
    return $rows;
}
function bluebookFetchOne($sql, $types = '', $params = array())
{
    $rows = bluebookFetchAll($sql, $types, $params);
    return !empty($rows) ? $rows[0] : null;
}
function bluebookGetCid()
{
    $cid = isset($_GET['cid']) ? trim($_GET['cid']) : '';
    return ($cid === '1') ? '1' : '2';
}
switch ($action) {
    case 'getTypes':
        // Get car types (pickup and sedan)
        $response['success'] = true;
        $response['data'] = array(
            array('id' => 1, 'name' => json_decode('"\u0e23\u0e16\u0e01\u0e23\u0e30\u0e1a\u0e30"'), 'icon' => 'fa-truck-pickup', 'image' => 'pickup.png'),
            array('id' => 2, 'name' => json_decode('"\u0e23\u0e16\u0e40\u0e01\u0e4b\u0e07"'), 'icon' => 'fa-car', 'image' => 'car.png')
        );
        break;
    case 'getBrands':
        $cid = bluebookGetCid();
        $brands = array();
        if ($cid == '1') {
            $arryCar = array('TOYOTA', 'ISUZU', 'NISSAN', 'MITSUBISHI', 'MAZDA', 'FORD');
            $sWhere = " AND carBrand NOT IN ('TOYOTA','ISUZU','NISSAN','MITSUBISHI','MAZDA','FORD')";
        } else {
            $arryCar = array('HONDA', 'TOYOTA', 'NISSAN', 'MITSUBISHI', 'MAZDA', 'FORD', 'BMW', 'MERCEDES-BENZ');
            $sWhere = " AND carBrand NOT IN ('HONDA','TOYOTA','NISSAN','MITSUBISHI','MAZDA','FORD','BMW','MERCEDES-BENZ')";
        }
        $rows = bluebookFetchAll(
            "SELECT carBrand FROM " . DB_TABLE_NAME . " WHERE carID = ? AND carType = ? " . $sWhere . " GROUP BY carBrand ORDER BY carBrand ASC",
            'ss',
            array($bluebook_year, $cid)
        );
        foreach ($rows as $row) {
            $arryCar[] = trim($row['carBrand']);
        }
        foreach ($arryCar as $brand) {
            $iconFile = strtolower($brand) . '.png';
            $hasIcon = file_exists("../icons/" . $iconFile);
            $brands[] = array(
                'name' => $brand,
                'icon' => $hasIcon ? $iconFile : ($cid == '1' ? 'pickup.png' : 'car.png')
            );
        }
        $response['success'] = true;
        $response['data'] = $brands;
        break;
    case 'getModels':
        $cid = bluebookGetCid();
        $bid = isset($_GET['bid']) ? trim($_GET['bid']) : '';
        $models = array();
        $rows = bluebookFetchAll(
            "SELECT carModel FROM " . DB_TABLE_NAME . " WHERE carID = ? AND carType = ? AND carBrand = ? GROUP BY carModel ORDER BY carModel ASC",
            'sss',
            array($bluebook_year, $cid, $bid)
        );
        foreach ($rows as $row) {
            $models[] = array('name' => trim($row['carModel']));
        }
        $response['success'] = true;
        $response['data'] = $models;
        break;
    case 'getYears':
        $cid = bluebookGetCid();
        $bid = isset($_GET['bid']) ? trim($_GET['bid']) : '';
        $mid = isset($_GET['mid']) ? trim($_GET['mid']) : '';
        $years = array();
        $rows = bluebookFetchAll(
            "SELECT carYear FROM " . DB_TABLE_NAME . " WHERE carID = ? AND carType = ? AND carBrand = ? AND carModel = ? GROUP BY carYear ORDER BY carYear DESC",
            'ssss',
            array($bluebook_year, $cid, $bid, $mid)
        );
        foreach ($rows as $row) {
            $years[] = array('year' => trim($row['carYear']));
        }
        $response['success'] = true;
        $response['data'] = $years;
        break;
    case 'getSubModels':
        $cid = bluebookGetCid();
        $bid = isset($_GET['bid']) ? trim($_GET['bid']) : '';
        $mid = isset($_GET['mid']) ? trim($_GET['mid']) : '';
        $yy = isset($_GET['yy']) ? trim($_GET['yy']) : '';
        $submodels = array();
        $rows = bluebookFetchAll(
            "SELECT ID, carSubModel, carGear, carPrice, car_picture FROM " . DB_TABLE_NAME . " WHERE carID = ? AND carType = ? AND carBrand = ? AND carModel = ? AND carYear = ? ORDER BY carSubModel, carGear ASC",
            'sssss',
            array($bluebook_year, $cid, $bid, $mid, $yy)
        );
        foreach ($rows as $row) {
            $submodels[] = array(
                'id' => $row['ID'],
                'submodel' => trim($row['carSubModel']),
                'gear' => trim($row['carGear']),
                'price' => (int) $row['carPrice'],
                'hasPicture' => trim($row['car_picture']) !== ''
            );
        }
        $response['success'] = true;
        $response['data'] = $submodels;
        break;
    case 'getPrice':
        $carid = isset($_GET['carid']) ? (int) $_GET['carid'] : 0;
        $row = bluebookFetchOne(
            "SELECT carSubModel, carGear, carPrice, carCode, car_picture FROM " . DB_TABLE_NAME . " WHERE ID = ?",
            'i',
            array($carid)
        );
        if ($row) {
            $carPicture = trim($row['car_picture']);
            $response['success'] = true;
            $response['data'] = array(
                'code' => trim($row['carCode']),
                'submodel' => trim($row['carSubModel']),
                'gear' => trim($row['carGear']),
                'price' => (int) $row['carPrice'],
                'picture' => $carPicture ? $url_image . '/' . $carPicture : ''
            );
        } else {
            $response['message'] = 'Car not found';
        }
        break;
    case 'getVersion':
        $response['success'] = true;
        $response['data'] = array(
            'version' => $bluebook_year,
            'webname' => $webname
        );
        break;
    default:
        $response['message'] = 'Invalid action';
}
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
