<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SocialAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\VerificationApiController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CommandController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\OptionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PanierController;
use App\Http\Controllers\WritingController;
use App\Models\User;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\AnnonceController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\DeliveryRatingController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TypeProductController;
use Laravel\Socialite\Facades\Socialite;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Auth::routes(['verify' => true]);
\Illuminate\Support\Facades\Broadcast::routes();
/*
Route::post('/broadcasting/auth', function (Request $request){
    return Auth::user();
})->middleware('jwt.verify'); */
/**
 * public api
 */
/**
 * public Supplier
 */
Route::post('/loginClient', [AuthController::class, "loginClient"]);
Route::post('/loginSupplier', [AuthController::class, "loginSupplier"]);
Route::post('/loginDelivery', [AuthController::class, "loginDelivery"]);
Route::post('/loginSuperAdmin', [AuthController::class, "loginAdmin"]);
Route::post('/refreshtoken', [AuthController::class, "refreshtoken"]);
Route::post('/register', [AuthController::class, "doRegister"]);
Route::post('addSupplier', [SupplierController::class, 'create']);
Route::post('addClient', [ClientController::class, 'create']);
Route::post('addDelivery', [DeliveryController::class, 'create']);
Route::post('addSuperAdmin', [AdminController::class, 'create']);
Route::get('allCategoryParent', [CategoryController::class, 'allCategoryParent']);
Route::get('getCategory/{per_page}', [CategoryController::class, 'all']);

Route::group(['middleware' => ['jwt.verify']], function () {
    Route::get('getAllSupplier/{per_page}', [SupplierController::class, 'all']);
    Route::post('updateSupplier/{id}', [SupplierController::class, 'update']);
    Route::delete('deleteSupplier/{id}', [SupplierController::class, 'delete']);
    Route::post('resetPWSupplier', [SupplierController::class, 'resetPWSupplier']);
    Route::post('verifySmsResetPWSupp', [SupplierController::class, 'verifySmsResetPW']);
    Route::post('statusSupplier', [SupplierController::class, 'statusSupplier']);
    Route::delete('deleteSupplier/{id}', [SupplierController::class, 'deleteSupplier']);
    Route::post('addimagesupplier', [SupplierController::class, 'addImage']);
    Route::post('updateimagesupplier', [SupplierController::class, 'updateimagesupplier']);

    Route::post('updatesupplierpassword/{id}', [SupplierController::class, 'updateSupplierPW']);
    Route::post('supplieraccceptrefusecommand', [SupplierController::class, 'supplierAccceptRefuseCommand']);

    /**
     * public Product
     */
    Route::post('createPrivateProduct', [ProductController::class, 'createPrivateProduct']);
    Route::post('createPublicProduct', [ProductController::class, 'createPublicProduct']);
    Route::post('productToSupplier', [ProductController::class, 'productToSupplier']);

    Route::get('getAllProduct/{per_page}', [ProductController::class, 'all']);
    Route::get('getProduct/{id}', [ProductController::class, 'getProduct']);
    Route::post('updateProduct/{id}', [ProductController::class, 'update']);
    Route::get('getdispoHourProductsSupplier/{id}', [ProductController::class, 'getdispoHourProductsSupplier']);
    Route::post('getdispoHourProductsSupplierByTag/{id}', [ProductController::class, 'getdispoHourProductsSupplierByTag']);

    Route::get('getAllPublicProduct/{per_page}', [ProductController::class, 'getAllPublicProduct']);

    Route::delete('destroyProduct/{id}', [ProductController::class, 'delete']);

    Route::put('ProductsSupplierNotAvailable/{id}', [ProductController::class, 'ProductsSupplierNotAvailable']);

    /**
     * public Category
     */
    Route::post('addCategory', [CategoryController::class, 'create']);
    Route::get('getCategoryChildren/{id}', [CategoryController::class, 'getCategoryChildren']);
    Route::get('getCategoryParent/{id}', [CategoryController::class, 'getCategoryParent']);
    Route::get('getcategorybyid/{id}', [CategoryController::class, 'categorybyid']);

    Route::get('getCategorysupplier/{id}/{per_page}', [CategoryController::class, 'getCategorysupplier']);
    Route::post('updateCategory/{id}', [CategoryController::class, 'update']);
    Route::delete('deleteCategory/{id}', [CategoryController::class, 'delete']);
    Route::get('getCategorysupplierDelivery/{id}/{per_page}', [CategoryController::class, 'getCategorysupplierDelivery']);
    Route::get('getCategorysupplierTakeaway/{id}/{per_page}', [CategoryController::class, 'getCategorysupplierTakeaway']);

    /**
     * public typeProduct
     */
    Route::post('addtypeProduct', [TypeProductController::class, 'create']);
    Route::get('getAllTypeProduct/{per_page}', [TypeProductController::class, 'getAllTypeProduct']);
    Route::get('getTypeProductByid/{id}', [TypeProductController::class, 'getTypeProductByid']);
    Route::put('updateTypeProduct/{id}', [TypeProductController::class, 'update']);
    Route::delete('deleteTypeProduct/{id}', [TypeProductController::class, 'delete']);
    /**
     * public tags
     */
    Route::post('addTag', [TagController::class, 'create']);
    Route::get('getAllTags/{per_page}', [TagController::class, 'getAllTags']);
    Route::get('getTagByid/{id}', [TagController::class, 'getTagByid']);
    Route::get('getSupplierTags/{id}', [TagController::class, 'getSupplierTags']);
    Route::get('getlisttags', [TagController::class, 'getAll']);
    Route::put('updateTag/{id}', [TagController::class, 'update']);
    Route::delete('deleteTag/{id}', [TagController::class, 'delete']);


    /**
     * public Option
     */
    Route::post('addOption', [OptionController::class, 'create']);
    Route::get('getOptionByid/{id}', [OptionController::class, 'getOptionByid']);
    Route::get('getProductOptions/{id}/{per_page}', [OptionController::class, 'getProductOptions']);
    Route::put('updateOption/{id}', [OptionController::class, 'update']);
    Route::post('getsupplieroptions', [OptionController::class, 'getsupplierOptions']);

    Route::delete('deleteOption/{id}', [OptionController::class, 'delete']);

    /**
     * public Menu
     */
    Route::post('getmenu', [MenuController::class, 'getSupplierMenu']);
    Route::post('add_product_to_submenu', [MenuController::class, 'AddProductToSubMenu']);
    Route::post('add_submenu', [MenuController::class, 'AddSubMenu']);
    Route::post('update_submenu', [MenuController::class, 'updateSubMenu']);
    Route::post('update_submenu_products', [MenuController::class, 'updateSubMenuProducts']);
    Route::post('addMenu', [MenuController::class, 'create']);
    Route::get('getMenuProducts/{id}/{per_page}', [MenuController::class, 'getMenuProducts']);
    Route::get('getMenuById/{id}', [MenuController::class, 'getMenuByid']);
    Route::post('updateMenu/{id}', [MenuController::class, 'update']);
    Route::delete('deleteMenu/{id}', [MenuController::class, 'delete']);
    Route::post('getmenuBysupplierid', [MenuController::class, 'getMenuBySupplierId']);
    Route::post('update_submenuposition', [MenuController::class, 'updateSubMenuPosition']);

    /**
     * public Client
     */

    Route::post('addImage', [ClientController::class, 'addImage']);
    Route::post('updateimage', [ClientController::class, 'updateImage']);

    Route::post('addfavorite', [ClientController::class, 'addfavorite']);
    Route::post('deletefavorite', [ClientController::class, 'deletefavorite']);

    Route::get('getClientCommands/{id}/{per_page}', [ClientController::class, 'getClientCommands']);
    Route::get('getClient/{id}', [ClientController::class, 'getClient']);

    Route::delete('destroyClient/{id}', [ClientController::class, 'delete']);
    Route::delete('deleteClient/{id}', [ClientController::class, 'deleteClient']);

    Route::get('getClientFavorits', [ClientController::class, 'getClientFavorits']);
    Route::post('statusClient', [ClientController::class, 'statusClient']);
    Route::put('updateClienPW/{id}', [ClientController::class, 'updateClienPW']);
    Route::get('resetPWClient', [ClientController::class, 'resetPWClient']);
    Route::post('verifySmsResetPW', [ClientController::class, 'verifySmsResetPW']);
    Route::get('ClientGetSupplier/{per_page}', [ClientController::class, 'ClientGetSupplier']);
    Route::post('ClientGetSupplierByCategory/{per_page}', [ClientController::class, 'ClientGetSupplierByCategory']);

    /**
     * public Delivery
     */
    Route::get('getAllDelivery/{per_page}', [DeliveryController::class, 'all']);
    Route::post('updateDelivery/{id}', [DeliveryController::class, 'update']);
    Route::delete('deleteDelivery/{id}', [DeliveryController::class, 'delete']);
    Route::post('acceptCommand', [DeliveryController::class, 'acceptCommand']);
    Route::get('notifCommand', [DeliveryController::class, 'notifCommand']);
    Route::get('rejectCommand', [DeliveryController::class, 'rejectCommand']);
    Route::get('ListCommandDelivered/{per_page}', [DeliveryController::class, 'ListCommandDelivered']);
    Route::get('ListCommandRejected/{per_page}', [DeliveryController::class, 'ListCommandRejected']);
    Route::get('gainCommands', [DeliveryController::class, 'gainCommands']);
    Route::get('CommandDelivered', [DeliveryController::class, 'CommandDelivered']);
    Route::get('generateInvoicePDF', [DeliveryController::class, 'generateInvoicePDF']);
    Route::get('statisDeliv', [DeliveryController::class, 'statisDeliv']);
    Route::post('hoursWork', [DeliveryController::class, 'hoursWork']);
    Route::get('getDeliveryById/{id}', [DeliveryController::class, 'getByid']);
    Route::post('addimagedelivery', [DeliveryController::class, 'addImage']);
    Route::post('updateimagedelivery', [DeliveryController::class, 'updateImage']);
    Route::post('statusdelivery', [DeliveryController::class, 'statusDelivery']);
    Route::post('setDeliveryAvailable', [DeliveryController::class, 'setAvailability']);

    /**
     * public DeliveryRating
     */
    Route::post('createDeliveryRating', [DeliveryRatingController::class, 'createDeliveryRating']);

    /**
     * public SuperAdmin
     */
    Route::post('addadmin', [AdminController::class, 'createAdmin']);
    Route::put('updateAdmin/{id}', [AdminController::class, 'updateAdmin']);
    Route::get('alladmin/{per_page}', [AdminController::class, 'all']);
    Route::delete('deleteAdmin/{id}', [AdminController::class, 'deleteAdmin']);
    Route::post('confirmemailsupplier/{id}', [AdminController::class, 'ConfirmEmailSupplier']);
    Route::get('getByid/{id}', [AdminController::class, 'getByid']);
    Route::post('updateadmin/{id}', [AdminController::class, 'updateAdmin']);

    /**
     * public Notification
     */
    Route::post('createNotif', [NotificationController::class, 'createNotif']);
    Route::get('getNotif', [NotificationController::class, 'getNotif']);

    /**
     * public panier
     */
    Route::post('create', [PanierController::class, 'create']);
    Route::post('addProduct/{id}', [PanierController::class, 'addProduct']);
    Route::get('getPanier/{id}', [PanierController::class, 'getPanier']);
    Route::delete('deleteProduct/{id}', [PanierController::class, 'deleteProduct']);

    /**
     * public Command
     */
    Route::get('fetchallcommand', [CommandController::class, 'fetchAll']);
    Route::get('getAllCommand/{per_page}', [CommandController::class, 'all']);
    Route::put('updateCommand/{id}', [CommandController::class, 'update']);
    Route::get('getCommandsByKeyClientDelivery', [CommandController::class, 'getCommandsByKeyClientDelivery']);
    Route::delete('deleteCommand/{id}', [CommandController::class, 'delete']);
    Route::delete('commandStatus/{id}', [CommandController::class, 'commandStatus']);
    Route::get('getCommandPanier/{id}', [CommandController::class, 'getCommandPanier']);
    Route::get('getCommand/{id}', [CommandController::class, 'getCommand']);
    Route::post('commandassignedadmin', [CommandController::class, 'CommandAssignedAdmin']);
    Route::post('validatecommand', [CommandController::class, 'validateCommand']);
    Route::post('authorizecommand', [CommandController::class, 'AuthorizeCommand']);
    Route::post('progressingcommand', [CommandController::class, 'ProgressingCommand']);

    /**
     * public Coupon
     *
     */
    Route::post('addCoupon', [CouponController::class, 'create']);
    Route::put('updateCoupon/{id}', [CouponController::class, 'update']);
    Route::get('getAllCoupon/{per_page}', [CouponController::class, 'getAll']);
    Route::get('getCoupon/{id}', [CouponController::class, 'getByid']);
    Route::delete('deleteCoupon/{id}', [CouponController::class, 'delete']);

    /**
     * public Discount
     *
     */

    Route::post('createDiscount', [DiscountController::class, 'create']);
    Route::get('getDiscountByid/{id}', [DiscountController::class, 'getByid']);
    Route::get('getAllDiscount/{per_page}', [DiscountController::class, 'getAll']);
    Route::put('updateDiscount/{id}', [DiscountController::class, 'update']);
    Route::delete('deleteDiscount/{id}', [DiscountController::class, 'delete']);

    /**
     * public Messsage
     */
    Route::post('sendMessage', [MessageController::class, 'sendMessage']);
    Route::post('getclientmessage', [MessageController::class, 'getClientMessage']);

    /**
     * email verify routes
     */
    Route::get('email/verify/{id}', [VerificationApiController::class, "verify"])->name('verificationapi.verify');
    Route::get('email/resend', [VerificationApiController::class, "resend"])->name('verificationapi.resend');
    /**
     * sms verify routes
     */
    Route::post('/smsverify/{id}', [VerificationApiController::class, "smsverify"]);
    Route::post('/vocalverify/{id}', [VerificationApiController::class, "vocalverify"]);
    Route::get('/verify/{id}', [VerificationApiController::class, "verifySmscode"]);
    Route::post('/toOrange/{id}', [VerificationApiController::class, "toOrange"]);

    /**
     * forget pw routes
     */
    Route::post('forget-password', [ForgotPasswordController::class, 'submitForgetPasswordForm'])->name('forget.password.post');
    Route::post('reset-password', [ForgotPasswordController::class, 'submitResetPasswordForm'])->name('reset.password.post');


    Route::get('/home', 'HomeController@index')->middleware('verified');

    // Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    //     return $request->user();
    // });

    Route::get('profile', function () {
        // Only verified users may enter...
    })->middleware('verified');

    Route::get('/', function () {
        $user = User::first();
    });
    Route::get('/get_admins/{per_page}', [AdminController::class, 'all']);


    Route::post('/logout', [AuthController::class, "logout"]);
    Route::get('/get_user', [AuthController::class, 'get_user']);
    // Route::get('/get_admins/{per_page}', [AdminController::class, 'all']);
    Route::get('getSuppDistances', [LocationController::class, 'getSuppDistances']);
    Route::get('filter', [FilterController::class, 'FilterFournisseur']);


    Route::get('getAllClient/{per_page}', [ClientController::class, 'all']);
    Route::get('getlistclients', [ClientController::class, 'allClient']);

    Route::get('getAllSupplier/{per_page}', [SupplierController::class, 'all']);
    Route::get('getAllDelivery/{per_page}', [DeliveryController::class, 'all']);
    Route::get('getavailabledelivery', [DeliveryController::class, 'fetchAvailableDelivery']);
    Route::get('getSupplierById/{id}', [SupplierController::class, 'getById']);
    Route::get('getSupplierProducts/{per_page}', [ProductController::class, 'getSupplierProduct']);
    Route::post('getSupplierProductsClean', [ProductController::class, 'getSuppliersProductClean']);

    // Route::middleware(['jwt.verify', 'role:superadmin'])->group(function () {
    //     Route::get('/private', function () {
    //         return 'Admin';
    //     });
    Route::post('createreply', [MessageController::class, 'createReply']);
    Route::post('getlastpostiondelivery/{id}', [AdminController::class, 'getLastPostionDelivery']);


    // Route::middleware(['jwt.verify', 'role:client'])->group(function () {
    // });
    Route::get('getAllClient', [ClientController::class, 'all']);
    Route::post('addCommand', [CommandController::class, 'create']);
    Route::post('index', [ClientController::class, 'index']);
    Route::post('get_home_page_data', [ClientController::class, 'init']);
    Route::post('updateClient/{id}', [ClientController::class, 'update']);
    /*
         * payement endpoints
         */

    Route::prefix('payment')->group(function () {
        Route::get('{command}/make', [\App\Http\Controllers\PaymentController::class, 'makePayment']);
        Route::post('balance/recharge', [\App\Http\Controllers\PaymentController::class, 'RechargeBalance']);
    });

    /*
        * Clients Message endpoint
        */
    Route::post('createmessage', [MessageController::class, 'createMessage']);

    // Route::middleware(['jwt.verify', 'role:supplier'])->group(function () {
    // });
    // Route::middleware(['jwt.verify', 'role:delivery'])->group(function () {
    // });
    Route::post('sendposition', [DeliveryController::class, 'sendDeliveryPosition']);

    Route::post('createAnnonce', [AnnonceController::class, 'create']);
    Route::get('showAnnonces', [AnnonceController::class, 'getAllAnnonces']);
    Route::get('showAnnonce/{id}', [AnnonceController::class, 'show']);
    Route::delete('deleteAnnonce/{id}', [AnnonceController::class, 'destroy']);
    Route::put('editAnnonce/{id}', [AnnonceController::class, 'edit']);
    Route::delete('deleteAllAnnonces', [AnnonceController::class, 'destroyAllAnnonces']);

    Route::get('localisationAdsress', [LocationController::class, 'GetLocationWithAdresse']);
    Route::get('localisationPosition', [LocationController::class, 'GetCurrentLocation']);
    Route::post('createAdresse/{id}', [AddressController::class, 'create']);
    Route::get('show/{id}', [AddressController::class, 'show']);
    Route::get('GetClientAddress/{id}', [AddressController::class, 'GetClientAddress']);

    Route::get('showAddresses', [AddressController::class, 'getAll']);
    Route::delete('deleteAddresse/{id}', [AddressController::class, 'destroy']);
    Route::put('updateAdresse/{id}', [AddressController::class, 'update']);

    //cree comment
    Route::post('createWriting', [WritingController::class, 'storeWriting']);
    Route::get('getAllWriting/{per_page}', [WritingController::class, 'getAll']);
    Route::put('updateWriting/{id}', [WritingController::class, 'update']);
    Route::delete('deleteWriting/{id}', [WritingController::class, 'destroy']);
    Route::get('showWriting/{id}', [WritingController::class, 'show']);

    /*
 * verify payement
 */
    Route::get('paymentgetway/verify/payment', [\App\Http\Controllers\PaymentController::class, 'verifyPayment']);
});
/*
* adsArea
*/
Route::post('adsarea/create', [\App\Http\Controllers\AreaController::class, 'create']);
Route::get('adsarea/get/{id}', [\App\Http\Controllers\AreaController::class, 'adsareabyid']);
Route::get('adsarea/all/{per_page}', [\App\Http\Controllers\AreaController::class, 'all']);
Route::post('adsarea/update/{id}', [\App\Http\Controllers\AreaController::class, 'update']);
Route::delete('adsarea/delete/{id}', [\App\Http\Controllers\AreaController::class, 'delete']);

/*
* ads
*/
Route::post('ads/create', [\App\Http\Controllers\AdsController::class, 'create']);
Route::get('ads/get/{id}', [\App\Http\Controllers\AdsController::class, 'adsbyid']);
Route::get('/ads/all/{per_page}', [\App\Http\Controllers\AdsController::class, 'all']);
Route::post('/ads/update/{id}', [\App\Http\Controllers\AdsController::class, 'update']);

/*
* login with social media
*/

Route::post('social/signin', [SocialAuthController::class, 'signInWithSocial']);
