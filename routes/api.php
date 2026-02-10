<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\DriversController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\FactoriesController;
use App\Http\Controllers\ShippingLineController;
use App\Http\Controllers\ShipOrderDataController;
use App\Http\Controllers\OperatingOrderController;
use App\Http\Controllers\PolicyController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'login']);

Route::prefix('auth')->middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {return $request->user();});
    Route::get('/users', [AuthController::class, 'getUsers']);
    Route::post('/users', [AuthController::class, 'createUser']);
    Route::put('/users/{id}', [AuthController::class, 'editUser']);
    Route::delete('/users/{id}', [AuthController::class, 'deleteUser']);

    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::apiResource('vehicles', VehicleController::class);
Route::apiResource('drivers', DriversController::class);
Route::apiResource('clients', ClientsController::class);
Route::apiResource('factories', FactoriesController::class);
Route::apiResource('destinations', DestinationController::class);
Route::apiResource('shipping-lines', ShippingLineController::class);

// Ship order number generation
Route::get('/ship-order-number', [ShipOrderDataController::class, 'generateOrderNumber']);

// Handel ship order data methods
Route::apiResource('/ship-order-data', ShipOrderDataController::class);

// Handel Operating orders data methods
Route::apiResource('/operating-orders', OperatingOrderController::class);

// Handel policies data methods
Route::apiResource('/policies', PolicyController::class);

// Policy number generation
Route::get('/policy-number', [PolicyController::class, 'generatePolicyNumber']);

// Get policies by ship order data
Route::get('/ship-order-data/{shipOrderDataId}/policies', [PolicyController::class, 'getByShipOrderData']);

