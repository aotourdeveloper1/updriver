<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CostcenterController;
use App\Http\Controllers\TasksController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\CotizacionesController;
use App\Http\Controllers\ProveedoresController;
use App\Http\Controllers\ConductoresController;
use App\Http\Controllers\VehiculosController;
use App\Http\Controllers\ViajesController;
use App\Http\Controllers\FacturacionController;
use App\Http\Controllers\ContabilidadController;
use App\Models\Cotizacion;
use App\Models\GestionesCotizacion;
use App\Models\GestionesPortafolio;
use App\Models\Portafolio;
use App\Models\Centrosdecosto;
use App\Models\Traslado;
use App\Models\Tarifa;
use App\Models\Conductor;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', function (Request $request) {

    $usuario = DB::table('conductores')
    ->where('email',$request->email)
    ->first();

    if($usuario==null) {

        return Response::json([
            'response' => false,
            'message' => 'El correo -'.$request->email.'- no se encuentra registrado.'
        ]);

    }else{

        $credentials = $request->validate([
            'email' => [''],
            'password' => [''],
        ]);
        
        if (Auth::attempt($credentials)) {

            $user = Auth::user();
            
            if(1>2) { //baneado$user->baneado==1

                return Response::json([
                    'response' => false,
                    'message' => 'Este usuario está desactivado. Póngase en contacto con el administrador del sistema o con el personal de soporte técnico.'
                ]);

            }else{
        
                $user->tokens()->delete();

                $token = $user->createToken('auth_token')->plainTextToken;

                Auth::logoutOtherDevices($request->password);
                
                $update = DB::table('conductores')
                ->where('id' , $user->id)
                ->update([
                    'last_login' => date('Y-m-d H:i')
                ]);
                
                $conductor = Conductor::find($user->id);

                return Response::json([
                    'response' => $token,
                    'acceso' => true,
                    'id_usuario' => Auth::user()->id,
                    'conductor' => $conductor
                ]);

            }
            
        }else{
            
            return Response::json([
                'response' => false,
                'message' => 'Encontramos tu usuario, pero parece que la clave que ingresaste no es correcta. Intenta con tu número de identificación. Si continuas con la restricción de ingreso, comunícate con tu proveedor.'
            ]);

        }
    }
    
});

Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('auth/logout', [AuthController::class, 'logout']);

    //Viajes
    Route::post('v1/viajesporentendido', [ViajesController::class, 'viajesporentendido']);
    Route::post('v1/proximosviajes', [ViajesController::class, 'proximosviajes']);
    Route::post('v1/servicioentendido', [ViajesController::class, 'servicioentendido']);
    Route::post('v1/listarpasajeros', [ViajesController::class, 'listarpasajeros']);
    Route::post('v1/escanearqr', [ViajesController::class, 'escanearqr']);
    Route::post('v1/iniciarviaje', [ViajesController::class, 'iniciarviaje']);
    Route::post('v1/viajeactivo', [ViajesController::class, 'viajeactivo']);
    Route::post('v1/usuarioactual', [ViajesController::class, 'usuarioactual']);
    Route::post('v1/esperaejecutivo', [ViajesController::class, 'esperaejecutivo']);
    Route::post('v1/esperaruta', [ViajesController::class, 'esperaruta']);
    Route::post('v1/dejarpasajero', [ViajesController::class, 'dejarpasajero']);
    Route::post('v1/historialdia', [ViajesController::class, 'historialdia']);
    Route::post('v1/historialmes', [ViajesController::class, 'historialmes']);
    Route::post('v1/listarnovedades', [ViajesController::class, 'listarnovedades']);
    Route::post('v1/registrarnovedad', [ViajesController::class, 'registrarnovedad']);
    Route::post('v1/registrarnovedadruta', [ViajesController::class, 'registrarnovedadruta']);
    Route::post('v1/descargarconstancia', [ViajesController::class, 'descargarconstancia']);
    Route::post('v1/pasajerorecogido', [ViajesController::class, 'pasajerorecogido']);
    Route::post('v1/finalizarviaje', [ViajesController::class, 'finalizarviaje']);



    Route::post('auth/inactivateuser', [AuthController::class, 'inactivateuser']);
    Route::post('auth/activateuser', [AuthController::class, 'activateuser']);
    
    Route::post('users/createuser', [UsersController::class, 'createuser']);
    Route::post('users/edituser', [UsersController::class, 'edituser']);
    Route::post('users/listusers', [UsersController::class, 'listusers']);
    Route::post('users/listemploy', [UsersController::class, 'listemploy']);
    Route::post('changepassword', [UsersController::class, 'changepassword']);
    Route::post('permissionsrol', [UsersController::class, 'permissionsrol']);
    Route::post('seeuserol', [UsersController::class, 'seeuserol']);
    Route::post('changeuserrol', [UsersController::class, 'changeuserrol']);
    Route::post('createrol', [UsersController::class, 'createrol']);
    Route::post('editrol', [UsersController::class, 'editrol']);

    Route::post('users/listentity', [UsersController::class, 'listentity']);
    Route::post('users/listentityuser', [UsersController::class, 'listentityuser']);
    Route::post('users/listtypeuser', [UsersController::class, 'listtypeuser']);
    Route::post('users/createprofile', [UsersController::class, 'createprofile']);
    Route::post('users/editprofile', [UsersController::class, 'editprofile']);
    Route::post('users/changestatusprofile', [UsersController::class, 'changestatusprofile']);
    Route::post('users/listprofile', [UsersController::class, 'listprofile']);

    /* CENTRO DE COSTO CONTROLLER */
    Route::post('costcenter/create', [CostcenterController::class, 'create']);
    Route::post('costcenter/siigocreate', [CostcenterController::class, 'siigocreate']);
    Route::post('costcenter/edit', [CostcenterController::class, 'edit']);
    Route::post('costcenter/list', [CostcenterController::class, 'list']);
    Route::post('costcenter/listwithmails', [CostcenterController::class, 'listwithmails']);
    Route::post('costcenter/inactivate', [CostcenterController::class, 'inactivate']);
    Route::post('costcenter/liststatusclients', [CostcenterController::class, 'liststatusclients']);

    Route::post('costcenter/createemails', [CostcenterController::class, 'createemails']);
    Route::post('costcenter/editemail', [CostcenterController::class, 'editemail']);
    Route::post('costcenter/deleteemail', [CostcenterController::class, 'deleteemail']);
    Route::post('costcenter/listemails', [CostcenterController::class, 'listemails']);

    Route::post('costcenter/cities/listcity', [CostcenterController::class, 'listcity']);
    Route::post('costcenter/parameters/createdepartament', [CostcenterController::class, 'createdepartament']);
    Route::post('costcenter/parameters/editdepartament', [CostcenterController::class, 'editdepartament']);
    Route::post('costcenter/parameters/createcity', [CostcenterController::class, 'createcity']);
    Route::post('costcenter/parameters/editcity', [CostcenterController::class, 'editcity']);

    Route::post('costcenter/fee/createfee', [CostcenterController::class, 'createfee']);
    Route::post('costcenter/fee/editfee', [CostcenterController::class, 'editfee']);
    Route::post('costcenter/fee/addfee', [CostcenterController::class, 'addfee']);
    Route::post('costcenter/fee/generateincrease', [CostcenterController::class, 'generateincrease']);
    Route::post('costcenter/fee/listfee', [CostcenterController::class, 'listfee']);
    Route::post('costcenter/fee/listroute', [CostcenterController::class, 'listroute']);
    Route::post('costcenter/fee/changeroutestatus', [CostcenterController::class, 'changeroutestatus']);

    Route::post('costcenter/subcostcenter/createsub', [CostcenterController::class, 'createsub']);
    Route::post('costcenter/subcostcenter/siigocreatesub', [CostcenterController::class, 'siigocreatesub']);
    Route::post('costcenter/subcostcenter/editsub', [CostcenterController::class, 'editsub']);
    Route::post('costcenter/subcostcenter/editcoordsub', [CostcenterController::class, 'editcoordsub']);
    Route::post('costcenter/subcostcenter/listsub', [CostcenterController::class, 'listsub']);
    /* CENTRO DE COSTO CONTROLLER */

    Route::get('users/download', [UsersController::class, 'download']); //Prueba de descargar pdf

    Route::post('tasks/statuslist', [TasksController::class, 'statuslist']);
    Route::post('tasks/statuslistselected', [TasksController::class, 'statuslistselected']);
    Route::post('tasks/creategroup', [TasksController::class, 'creategroup']);
    Route::post('tasks/listgroups', [TasksController::class, 'listgroups']);
    Route::post('tasks/createproject', [TasksController::class, 'createproject']);
    Route::post('tasks/listprojects', [TasksController::class, 'listprojects']);

    Route::post('tasks/createsubproject', [TasksController::class, 'createsubproject']);
    Route::post('tasks/listsubprojects', [TasksController::class, 'listsubprojects']);

    Route::post('tasks/deleteproject', [TasksController::class, 'deleteproject']);
    Route::post('tasks/deletesubproject', [TasksController::class, 'deletesubproject']);

    Route::post('tasks/listresponsible', [TasksController::class, 'listresponsible']);
    Route::post('tasks/createevidenceproject', [TasksController::class, 'createevidenceproject']);
    Route::post('tasks/createevidencesubproject', [TasksController::class, 'createevidencesubproject']);

    Route::post('tasks/listevidenceproject', [TasksController::class, 'listevidenceproject']);
    Route::post('tasks/listevidencesubproject', [TasksController::class, 'listevidencesubproject']);

    Route::post('tasks/editpriority', [TasksController::class, 'editpriority']);
    Route::post('tasks/editprioritysub', [TasksController::class, 'editprioritysub']);

    Route::post('tasks/editstatus', [TasksController::class, 'editstatus']);
    Route::post('tasks/editstatussub', [TasksController::class, 'editstatussub']);

    Route::post('tasks/editresponsible', [TasksController::class, 'editresponsible']);
    Route::post('tasks/editresponsiblesub', [TasksController::class, 'editresponsiblesub']);

    Route::post('tasks/editordengroup', [TasksController::class, 'editordengroup']);
    Route::post('tasks/editordenproject', [TasksController::class, 'editordenproject']);
    Route::post('tasks/editordensubproject', [TasksController::class, 'editordensubproject']);
    
    Route::post('tasks/listprojectsuser', [TasksController::class, 'listprojectsuser']);
    Route::post('tasks/createnotification', [TasksController::class, 'createnotification']);
    
    Route::post('tasks/readnotification', [TasksController::class, 'readnotification']);
    Route::post('tasks/readnotifications', [TasksController::class, 'readnotifications']);
    
    Route::post('tasks/listnotifications', [TasksController::class, 'listnotifications']);

    Route::post('tasks/deletenotification', [TasksController::class, 'deletenotification']);
    Route::post('tasks/deletenotifications', [TasksController::class, 'deletenotifications']);

    Route::post('tasks/listgroupsuser', [TasksController::class, 'listgroupsuser']);

    Route::post('tasks/createblog', [TasksController::class, 'createblog']);
    Route::post('tasks/createbloguser', [TasksController::class, 'createbloguser']);

    Route::post('tasks/createblogusersub', [TasksController::class, 'createblogusersub']);

    Route::post('tasks/listbloguser', [TasksController::class, 'listbloguser']);
    Route::post('tasks/listblogusersub', [TasksController::class, 'listblogusersub']);
    Route::post('tasks/editblog', [TasksController::class, 'editblog']);

    Route::post('tasks/editblog', [TasksController::class, 'editblog']);
    
    Route::post('tasks/editcopy', [TasksController::class, 'editcopy']);

    Route::post('tasks/accesschat', [TasksController::class, 'accesschat']);
    Route::post('tasks/listcopy', [TasksController::class, 'listcopy']);
    Route::post('tasks/listcopysub', [TasksController::class, 'listcopysub']);
    Route::post('tasks/editcopysub', [TasksController::class, 'editcopysub']);

    Route::post('tasks/listall', [TasksController::class, 'listall']);

    /* CONFIG CONTROLLER */
    
    Route::post('config/listheadquarters', [ConfigController::class, 'listheadquarters']);

    Route::post('config/createposition', [ConfigController::class, 'createposition']);
    Route::post('config/createproject', [ConfigController::class, 'createproject']);
    Route::post('config/relationship', [ConfigController::class, 'relationship']);
    Route::post('config/listpositions', [ConfigController::class, 'listpositions']);
    Route::post('config/listpositionprojects', [ConfigController::class, 'listpositionprojects']);

    Route::post('config/listprojectsbyposition', [ConfigController::class, 'listprojectsbyposition']);

    Route::post('config/listaccionsbyuser', [ConfigController::class, 'listaccionsbyuser']);
    Route::post('config/listfirst', [ConfigController::class, 'listfirst']);
    Route::post('config/listprofileusersandaccions', [ConfigController::class, 'listprofileusersandaccions']);

    /* CONFIG CONTROLLER */

    /* Notas START*/
    Route::post('config/createnote', [ConfigController::class, 'createnote']);
    Route::post('config/editnote', [ConfigController::class, 'editnote']);
    Route::post('config/changenotestatus', [ConfigController::class, 'changenotestatus']);
    Route::post('config/deletenote', [ConfigController::class, 'deletenote']);
    Route::post('config/listnotes', [ConfigController::class, 'listnotes']);
    /* Notas END*/

    /* Cotizaciones */
    Route::post('quotes/create', [CotizacionesController::class, 'create']);
    Route::post('quotes/sendquotemail', [CotizacionesController::class, 'sendquotemail']);
    Route::post('quotes/list', [CotizacionesController::class, 'list']);
    Route::post('quotes/listchannels', [CotizacionesController::class, 'listchannels']);
    Route::post('quotes/listvehiclestype', [CotizacionesController::class, 'listvehiclestype']);
    Route::post('quotes/listfeebyclient', [CotizacionesController::class, 'listfeebyclient']);
    Route::post('quotes/listways', [CotizacionesController::class, 'listways']);
    Route::post('quotes/approve', [CotizacionesController::class, 'approve']);
    Route::post('quotes/disapprove', [CotizacionesController::class, 'disapprove']);
    Route::post('quotes/reactivate', [CotizacionesController::class, 'reactivate']);
    Route::post('quotes/newmanagement', [CotizacionesController::class, 'newmanagement']);
    Route::post('quotes/evidence', [CotizacionesController::class, 'evidence']);
    Route::post('quotes/listbyquote', [CotizacionesController::class, 'listbyquote']);
    Route::post('quotes/editfees', [CotizacionesController::class, 'editfees']);
    Route::post('quotes/consultmanagement', [CotizacionesController::class, 'consultmanagement']);
    Route::post('quotes/consultevidence', [CotizacionesController::class, 'consultevidence']);

    Route::post('briefcase/send', [CotizacionesController::class, 'send']);
    Route::post('briefcase/sendfees', [CotizacionesController::class, 'sendfees']);
    Route::post('briefcase/listp', [CotizacionesController::class, 'listp']);
    Route::post('briefcase/newmanagementp', [CotizacionesController::class, 'newmanagementp']);
    Route::post('briefcase/consultmanagementp', [CotizacionesController::class, 'consultmanagementp']);
    Route::post('briefcase/evidencep', [CotizacionesController::class, 'evidencep']);

    Route::post('briefcase/approvep', [CotizacionesController::class, 'approvep']);
    Route::post('briefcase/disapprovep', [CotizacionesController::class, 'disapprovep']);
    Route::post('briefcase/reactivatep', [CotizacionesController::class, 'reactivatep']);
    Route::post('briefcase/listfeetoquotes', [CotizacionesController::class, 'listfeetoquotes']);
    Route::post('briefcase/updateurl', [CotizacionesController::class, 'updateurl']);

    Route::post('pqr/sendemail', [CotizacionesController::class, 'sendemail']);

    Route::post('briefcase/enviaremail', [CotizacionesController::class, 'enviaremail']);

    /* Cotizaciones */

    /* Proveedores */
    Route::post('providers/listinscribe', [ProveedoresController::class, 'listinscribe']);
    Route::post('providers/sendtoreview', [ProveedoresController::class, 'sendtoreview']);

    Route::post('providers/listaccounttoinscribe', [ProveedoresController::class, 'listaccounttoinscribe']);
    Route::post('providers/listdrivertoinscribe', [ProveedoresController::class, 'listdrivertoinscribe']);
    Route::post('providers/listvehicletoinscribe', [ProveedoresController::class, 'listvehicletoinscribe']);
    Route::post('providers/listbyprovider', [ProveedoresController::class, 'listbyprovider']);
    Route::post('providers/approvedocprovider', [ProveedoresController::class, 'approvedocprovider']);
    Route::post('providers/approvedocdriver', [ProveedoresController::class, 'approvedocdriver']);
    Route::post('providers/approvedocvehicle', [ProveedoresController::class, 'approvedocvehicle']);    

    Route::post('providers/sendtocorrect', [ProveedoresController::class, 'sendtocorrect']);
    Route::post('providers/sendtocapacite', [ProveedoresController::class, 'sendtocapacite']);
    Route::post('providers/uploadcapacite', [ProveedoresController::class, 'uploadcapacite']);
    Route::post('providers/approveprovider', [ProveedoresController::class, 'approveprovider']);

    Route::post('providers/inscribedriver', [ProveedoresController::class, 'inscribedriver']);
    Route::post('providers/inscribevehicle', [ProveedoresController::class, 'inscribevehicle']);

    Route::post('providers/updatedatedocument', [ProveedoresController::class, 'updatedatedocument']);

    Route::post('providers/listdriversdate', [ProveedoresController::class, 'listdriversdate']);
    Route::post('providers/savedatestart', [ProveedoresController::class, 'savedatestart']);

    Route::post('providers/drivers/sendfiles', [ProveedoresController::class, 'sendfiles']);
    Route::post('providers/drivers/sendfilesv', [ProveedoresController::class, 'sendfilesv']);

    Route::post('providers/one', [ProveedoresController::class, 'one']); //PENDING
    Route::post('providers/two', [ProveedoresController::class, 'two']); //PENDING
    Route::post('providers/theree', [ProveedoresController::class, 'theree']); //PENDING

    Route::post('providers/create', [ProveedoresController::class, 'create']);
    Route::post('providers/edit', [ProveedoresController::class, 'edit']);
    Route::post('providers/inactivate', [ProveedoresController::class, 'inactivate']);
    Route::post('providers/list', [ProveedoresController::class, 'list']);
    /* Proveedores */

    /* Conductores */
    Route::post('providers/createdriver', [ProveedoresController::class, 'createdriver']);
    Route::post('providers/listdrivers', [ProveedoresController::class, 'listdrivers']);
    Route::post('providers/createvehicle', [ProveedoresController::class, 'createvehicle']);
    Route::post('providers/listvehicles', [ProveedoresController::class, 'listvehicles']);


    Route::post('drivers/edit', [ConductoresController::class, 'edit']);
    Route::post('drivers/inactivate', [ConductoresController::class, 'inactivate']);
    Route::post('drivers/createuserapp', [ConductoresController::class, 'createuserapp']);
    /* Conductores */

    /* Seguridad Social */
    Route::post('drivers/socialsecurity', [ConductoresController::class, 'socialsecurity']);
    /* Seguridad Social */

    /* Vehículos */
    Route::post('vehicles/create', [VehiculosController::class, 'create']);
    Route::post('vehicles/edit', [VehiculosController::class, 'edit']);
    Route::post('vehicles/inactivate', [VehiculosController::class, 'inactivate']);
    Route::post('vehicles/maintenancelock', [VehiculosController::class, 'maintenancelock']);
    Route::post('vehicles/systemslock', [VehiculosController::class, 'systemslock']);
    /* Vehículos */

    /* Viajes start */
    Route::post('trips/createtrip', [ViajesController::class, 'createtrip']);
    Route::post('trips/createmultipletrip', [ViajesController::class, 'createmultipletrip']);
    Route::post('trips/createtripdispo', [ViajesController::class, 'createtripdispo']);
    Route::post('trips/listtrips', [ViajesController::class, 'listtrips']);
    Route::post('trips/showtripdetails', [ViajesController::class, 'showtripdetails']);
    Route::post('trips/edittrip', [ViajesController::class, 'edittrip']);
    Route::post('trips/scheduletripremoval', [ViajesController::class, 'scheduletripremoval']);
    Route::post('trips/deletetrip', [ViajesController::class, 'deletetrip']);
    Route::post('trips/listtripsbyremove', [ViajesController::class, 'listtripsbyremove']);
    Route::post('trips/listbin', [ViajesController::class, 'listbin']);
    Route::post('trips/declinedeletetrip', [ViajesController::class, 'declinedeletetrip']);
    Route::post('trips/showtracking', [ViajesController::class, 'showtracking']);
    Route::post('trips/shownovs', [ViajesController::class, 'shownovs']);
    Route::post('trips/addnov', [ViajesController::class, 'addnov']);
    
    /* Viajes end */

    /* Facturación start */
    Route::post('billing/listtrips', [FacturacionController::class, 'listtrips']);

    Route::post('billing/revisar', [FacturacionController::class, 'revisar']);
    Route::post('billing/listtripsliq', [FacturacionController::class, 'listtripsliq']);    
    Route::post('billing/liquidar', [FacturacionController::class, 'liquidar']);
    Route::post('billing/generarliquidacion', [FacturacionController::class, 'generarliquidacion']);
    Route::post('billing/ofporautorizar', [FacturacionController::class, 'ofporautorizar']);
    Route::post('billing/autorizarliquidacion', [FacturacionController::class, 'autorizarliquidacion']);
    Route::post('billing/ofautorizadas', [FacturacionController::class, 'ofautorizadas']);
    Route::post('billing/anularliquidacion', [FacturacionController::class, 'anularliquidacion']);
    Route::post('billing/validarfacturacion', [FacturacionController::class, 'validarfacturacion']);
    Route::post('billing/generarfactura', [FacturacionController::class, 'generarfactura']);
    Route::post('billing/generarpdffactura', [FacturacionController::class, 'generarpdffactura']);
    Route::post('billing/listinvoices', [FacturacionController::class, 'listinvoices']);
    Route::post('billing/listtripsbyinvoice', [FacturacionController::class, 'listtripsbyinvoice']);
    Route::post('billing/listtripsbyof', [FacturacionController::class, 'listtripsbyof']);
    Route::post('billing/listinvoiceswithoutin', [FacturacionController::class, 'listinvoiceswithoutin']);
    
    /* Facturación end */

    /* Contabilidad start */
    Route::post('finance/newloan', [ContabilidadController::class, 'newloan']);
    Route::post('finance/listloan', [ContabilidadController::class, 'listloan']);
    Route::post('finance/addloantoexist', [ContabilidadController::class, 'addloantoexist']);
    Route::post('finance/listitemsbyloan', [ContabilidadController::class, 'listitemsbyloan']);
    Route::post('finance/edititemloan', [ContabilidadController::class, 'edititemloan']);
    Route::post('finance/deleteitemloan', [ContabilidadController::class, 'deleteitemloan']);
    
    Route::post('finance/searchap', [ContabilidadController::class, 'searchap']);
    Route::post('finance/listapbypayment', [ContabilidadController::class, 'listapbypayment']);
    Route::post('finance/listtripsbyap', [ContabilidadController::class, 'listtripsbyap']);
    
    Route::post('finance/haspayment', [ContabilidadController::class, 'haspayment']);
    Route::post('finance/reviseap', [ContabilidadController::class, 'reviseap']);
    Route::post('finance/newpayment', [ContabilidadController::class, 'newpayment']);

    Route::post('finance/newlot', [ContabilidadController::class, 'newlot']);
    Route::post('finance/listlots', [ContabilidadController::class, 'listlots']);
    Route::post('finance/listpayments', [ContabilidadController::class, 'listpayments']);
    Route::post('finance/changestatus', [ContabilidadController::class, 'changestatus']);
    Route::post('finance/deletelot', [ContabilidadController::class, 'deletelot']);
    Route::post('finance/consults', [ContabilidadController::class, 'consults']);
    Route::post('finance/proccesspayment', [ContabilidadController::class, 'proccesspayment']);
    /* Contabilidad end */

    Route::post('tasks/push', [TasksController::class, 'push']); //Prueba de notificación Pusher
    Route::post('config/departamento', [ConfigController::class, 'departamento']);
    Route::post('config/ciudad', [ConfigController::class, 'ciudad']);

    Route::post('quotes/email', [CotizacionesController::class, 'email']);

});



Route::post('costcenter/cities/listdepartaments', [CostcenterController::class, 'listdepartaments']);
Route::post('costcenter/parameters/listcompanytypes', [CostcenterController::class, 'listcompanytypes']);
Route::post('config/listtypes', [ConfigController::class, 'listtypes']);

Route::post('providers/inscribe', [ProveedoresController::class, 'inscribe']); //PENDING - INSCRIPCIÓN DE PROVEEDORES EN EL PROTAL
Route::post('providers/listbankingentities', [ProveedoresController::class, 'listbankingentities']);

Route::post('providers/submitdocdriver', [ProveedoresController::class, 'submitdocdriver']);
Route::post('providers/submitdocvehicle', [ProveedoresController::class, 'submitdocvehicle']);
Route::post('providers/resenddocuments', [ProveedoresController::class, 'resenddocuments']);

Route::post('config/liststatus', [ConfigController::class, 'liststatus']);

Route::post('/approve', function (Request $request) {

    $cotizacion = Cotizacion::find($request->id);

    if($cotizacion->estado!=24) {

        if($cotizacion->estado==25) { //ACEPTADA

            return Response::json([
                'response' => false,
                'message' => 'Esta cotización ya fue aceptada anteriormente'
            ]);

        }else if($cotizacion->estado==26) { //RECHAZADA

            return Response::json([
                'response' => false,
                'message' => 'Esta cotización fue rechazada anteriormente'
            ]);

        }else if($cotizacion->estado==23) { //VENCIDA

            return Response::json([
                'response' => false,
                'message' => 'Esta cotización se encuentra vencida... comunícate con servicio al cliente!'
            ]);

        }

    }else{

        $cotizacion->estado = 25;
        $cotizacion->save();

        $dataText = 'El cliente ACEPTÓ la cotización mediante el link enviado a su correo.';

        $gestion = new GestionesCotizacion;
        $gestion->texto = $dataText;
        $gestion->fk_cotizaciones = $request->id;
        $gestion->fk_users = $cotizacion->vendedor;
        $gestion->creado = date('Y-m-d H:i');
        $gestion->save();

        $data = [
            'contacto' => $cotizacion->contacto,
            'consecutivo' => $cotizacion->id
        ];
            
        //envío a clientes
        $email = json_decode($cotizacion->enviado_a);
        //$cc = ['comercial@aotour.com.co','b.carrillo@aotour.com.co','facturacion@aotour.com.co'];
        $cc = ['comercial@aotour.com.co','b.carrillo@aotour.com.co','facturacion@aotour.com.co', 'gustelo@aotour.com.co'];
        //$cc = ['sistemas@aotour.com.co','sistemas1@aotour.com.co'];

        Mail::send('email_acept', $data, function($message) use ($email, $cc){
            $message->from('no-reply@aotour.com.co', 'Alertas Cotizaciones');
            $message->to($email)->subject('¡Cotización Aceptada!');
            $message->bcc($cc);
        });

    }

    return Response::json([
        'response' => true
    ]);

});

Route::post('/disapprove', function (Request $request) {

    $cotizacion = Cotizacion::find($request->id);

    if($cotizacion->estado!=24) {

        if($cotizacion->estado==25) { //ACEPTADA

            return Response::json([
                'response' => false,
                'message' => 'Esta cotización ya fue aceptada anteriormente'
            ]);

        }else if($cotizacion->estado==26) { //RECHAZADA

            return Response::json([
                'response' => false,
                'message' => 'Esta cotización fue rechazada anteriormente'
            ]);

        }else if($cotizacion->estado==23) { //VENCIDA

            return Response::json([
                'response' => false,
                'message' => 'Esta cotización se encuentra vencida... comunícate con servicio al cliente!'
            ]);

        }
        
    }else{

        $cotizacion->estado = 26;
        $cotizacion->save();

        $dataText = 'El cliente RECHAZÓ la cotización mediante el link enviado a su correo.';

        $gestion = new GestionesCotizacion;
        $gestion->texto = $dataText;
        $gestion->fk_cotizaciones = $request->id;
        $gestion->fk_users = $cotizacion->vendedor;
        $gestion->creado = date('Y-m-d H:i');
        $gestion->save();

        $data = [
            'contacto' => $cotizacion->contacto,
            'consecutivo' => $cotizacion->id
        ];

        //envío a clientes
        $email = json_decode($cotizacion->enviado_a);
        $cc = ['comercial@aotour.com.co','b.carrillo@aotour.com.co','facturacion@aotour.com.co', 'gustelo@aotour.com.co'];
        //$cc = ['sistemas@aotour.com.co','sistemas1@aotour.com.co'];

        Mail::send('email_rechaz', $data, function($message) use ($email, $cc){
            $message->from('no-reply@aotour.com.co', 'Alertas Cotizaciones');
            $message->to($email)->subject('¡Cotización Rechazada!');
            $message->bcc($cc);
        });
        
    }

    return Response::json([
        'response' => true
    ]);

});

Route::post('/zoomrequest', function (Request $request) {
    
    $id = $request->id;
    $proceso = $request->proceso;

    $fecha = $request->fecha;
        $hora = $request->hora;
        $correo = $request->correo;
        $nombre = $request->nombre;

        $dataText = 'El cliente solicitó una reunión el '.$fecha.' a las '.$hora.', con el correo '.$correo.'.';

        if($proceso==1) {

            $cotizacion = Cotizacion::find($id);
            $cotizacion->meet = 1;
            $cotizacion->save();

            $gestion = new GestionesCotizacion;
            $gestion->texto = $dataText;
            $gestion->fk_cotizaciones = $request->id;
            $gestion->fk_users = $cotizacion->vendedor;
            $gestion->creado = date('Y-m-d H:i');
            
            $texto = 'Cotización';

            $consecutiv = '# '.$id;

        }else{

            $portafolio = Portafolio::find($id);
            $portafolio->meet = 1;
            $portafolio->save();

            $gestion = new GestionesPortafolio;
            $gestion->texto = $dataText;
            $gestion->fk_portafolio = $id;
            $gestion->fk_users = $portafolio->creado_por;
            $gestion->creado = date('Y-m-d H:i');

            $texto = 'Portafolio';

            $consecutiv = '';

        }
        
        $gestion->save();

        $data = [
            'contacto' => $nombre,
            'consecutivo' => $consecutiv,
            'texto' => $texto,
            'fecha' => $fecha,
            'hora' => $hora,
            'correo' => $correo
        ];
            
        //envío a clientes
        $email = 'sistemas@aotour.com.co'; //Correo al que se envía la solicitud de reunión
        $cc = ['comercial@aotour.com.co']; //Correos de copia oculta
        //$cc = ['sistemas@aotour.com.co','sistemas1@aotour.com.co']; //comentar esta línea en producción

        Mail::send('email_zoom', $data, function($message) use ($email, $cc, $texto, $fecha, $hora, $correo){
            $message->from('no-reply@aotour.com.co', 'Solicitud de Reunión de '.$texto);
            $message->to($email)->subject('Se ha solicitado una reunión para el día '.$fecha.' a las '.$hora);
            $message->cc($correo);
            $message->bcc($cc);
        });

        return Response::json([
            'response' => true
        ]);
        /*
    $cotizacion = Cotizacion::find($id);
    $portafolio = Portafolio::find($id);
    
    if($proceso==1 and $cotizacion->meet==1) {
        
        return Response::json([
            'response' => false,
            'message' => 'No se pudo solicitar la reunión, ya que anteriormente fue solicitada... comunícate con servicio al cliente!'
        ]);

    }else if($proceso==2 and $portafolio->meet==1) {
        
        return Response::json([
            'response' => false,
            'message' => 'No se pudo solicitar la reunión, ya que anteriormente fue solicitada... comunícate con servicio al cliente!'
        ]);

    }else{

        

    }*/

});

Route::post('listdocumentstocorrect', function(Request $request) {

    $consultaProveedores = DB::table('proveedores')
    ->select('fk_estado')
    ->where('id',$request->id)
    ->first();

    $queryConductores = "SELECT id, primer_nombre, primer_apellido, sw_global, licencia_conduccion_sw, licencia_conduccion_pdf, seguridad_social_sw, seguridad_social_pdf, numero_documento_sw, numero_documento_pdf, examenes_sw, examenes_pdf, licencia_conduccion_obs, seguridad_social_obs, numero_documento_obs, examenes_obs FROM conductores where fk_proveedor = ".$request->id."";
    $conductores = DB::select($queryConductores);

    $queryVehiculos = "SELECT id, placa, sw_global, tarjeta_operacion_sw, tarjeta_operacion_pdf, tarjeta_propiedad_sw, tarjeta_propiedad_pdf, soat_sw, soat_pdf, tecnomecanica_sw, tecnomecanica_pdf, preventivo_sw, preventivo_pdf, poliza_contractual_sw, poliza_contractual_pdf, poliza_extracontractual_sw, poliza_extracontractual_pdf, tarjeta_operacion_obs, soat_obs, tecnomecanica_obs, preventivo_obs, poliza_contractual_obs, poliza_extracontractual_obs, tarjeta_propiedad_obs FROM vehiculos where fk_proveedor = ".$request->id."";
    $vehiculos = DB::select($queryVehiculos);

    $cuenta = "SELECT p.id, p.razonsocial, p.sw_global, b.nombre as banco, c.numero_cuenta, c.id as id_cuenta, b.id as id_banco, t.nombre, t.id as id_tipo, c.certificacion_pdf, s.nombre as sede from proveedores p left join cuenta_bancaria c on c.id = p.fk_cuenta_bancaria left join bancos b on b.id = c.fk_banco left JOIN tipos t on t.id = c.fk_tipo_cuenta left join sedes s on s.id = p.fk_sede where p.id = ".$request->id."";
    $cuenta = DB::select($cuenta);

    return Response::json([
        'response' => true,
        'conductores' => $conductores,
        'vehiculos' => $vehiculos,
        'proveedor' => $consultaProveedores->fk_estado,
        'cuenta' => $cuenta
    ]);

});

Route::post('costcenterlist', function(Request $request) {

    $centrosdecosto = "select id, razonsocial, nit,codigoverificacion, departamento, ciudad, tipoempresa, direccion, ciudad, fk_sede, email, telefono, pn, creado_por, inactivo, recargo_nocturno, desde, hasta, tarifa_aotour as tarifa_cliente, tarifa_aotour_proveedor as tarifa_proveedor, nombre_contacto, apellido_contacto, siigo from centrosdecosto";
    $centrosdecosto = DB::select($centrosdecosto);

    return Response::json([
        'response' => true,
        'centrosdecosto' => $centrosdecosto
    ]);

});

Route::post('listusers', function(Request $request) {

    $usuarios = DB::table('users')
    ->leftjoin('tipo_usuario', 'tipo_usuario.id', '=', 'users.fk_tipo_usuario')
    ->select('users.*', 'tipo_usuario.nombre as nombre_tipo_usuario')
    ->get();

    return Response::json([
        'response' => true,
        'users' => $usuarios
    ]);

});

Route::post('sendemail', function(Request $request) {

    $data = [
        'titulo' => $request->titulo,
        'texto' => $request->texto,
        'id' => $request->id,
        'solicitante' => $request->solicitante
    ];

    $email = $request->email; // 'sdgm2207@gmail.com'
    $copias = $request->copias; //['sistemas@aotour.com.co','sistemas1@aotour.com.co]

    if($request->estado==3) {

        Mail::send('pqr_emails.pqr', $data, function($message) use ($email, $copias, $request){
            $message->from('no-reply@aotour.com.co', ''.$request->nombre.'');
            $message->to($email)->subject(''.$request->asunto.'');
            $message->bcc($copias);
            if(count($request->url)>0) {
                for ($i=0; $i < count($request->url) ; $i++) {
                    $message->attach($request->url[$i]);
                }
                //$message->attach($request->url);
            }
        });

    }else{

        Mail::send('pqr_emails.email_pqr', $data, function($message) use ($email, $copias, $request){
            $message->from('no-reply@aotour.com.co', ''.$request->nombre.'');
            $message->to($email)->subject(''.$request->asunto.'');
            $message->bcc($copias);
            if(count($request->url)>0) {
                for ($i=0; $i < count($request->url) ; $i++) {
                    $message->attach($request->url[$i]);
                }
                //$message->attach($request->url);
            }
        });
    }

    return Response::json([
        'response' => true
    ]);
    
});

//Transfer to AUTONET
Route::post('listdrivers', function(Request $request) {

    $conductores = DB::table('conductores')
    ->get();

    return Response::json([
        'response' => true,
        'conductores' => $conductores
    ]);
    
});

Route::post('clients', function(Request $request) {

    $clientes = $request->clientes;

    foreach ($clientes as $cli) {
        
        if( $cli['localidad']=='barranquilla' ) {
            $sede = 1;
        }else if( $cli['localidad']=='bogota' ) {
            $sede = 2;
        }else if( $cli['localidad']=='provisional' ) {
            $sede = 3;
        }

        if( $cli['tipoempresa']=='S.A.S' ) {
            $tipoEmpresa = 15;
        }else if( $cli['tipoempresa']=='S.A' ) {
            $tipoEmpresa = 16;
        }else if( $cli['tipoempresa']=='OTROS' ) {
            $tipoEmpresa = 53;
        }else if( $cli['tipoempresa']=='L.T.D.A' ) {
            $tipoEmpresa = 19;
        }else if( $cli['tipoempresa']=='P.N' ) {
            $tipoEmpresa = 20;
        }else if( $cli['tipoempresa']=='S.C.A' ) {
            $tipoEmpresa = 17;
        }

        $dep = DB::table('departamentos')
        ->where('nombre',$cli['departamento'])
        ->first();

        $ciu = DB::table('ciudades')
        ->where('nombre',$cli['ciudad'])
        ->first();

        if( intval($cli['inactivo'])==1 ) {
            $inac = 13;
        }else if( intval($cli['inactivo_total'])==1 ) {
            $inac = 14;
        }else{
            $inac = 12;
        }

        $centro = new Centrosdecosto;
        $centro->id = $cli['id'];
        $centro->nit = $cli['nit'];
        $centro->codigoverificacion = $cli['codigoverificacion'];
        $centro->razonsocial = $cli['razonsocial'];
        $centro->tipoempresa = $tipoEmpresa;
        $centro->direccion = $cli['direccion'];
        $centro->departamento = $dep->id;
        $centro->ciudad = $ciu->id;
        $centro->fk_sede = $sede;
        $centro->email = $cli['email'];
        $centro->telefono = $cli['telefono'];
        if( $cli['id']==100 ){
            $centro->pn = 1;
        }else{
            $centro->pn = 0;
        }
        $centro->creado_siigo_por = 170;
        $centro->creado_por = 2;
        $centro->created_at = $cli['created_at'];
        $centro->updated_at = $cli['updated_at'];
        $centro->inactivo = $inac;
        if( $cli['tarifa_aotour']==1 ) {
            $centro->tarifa_aotour = 1;
        }else{
            $centro->tarifa_aotour = 0;
        }

        if( $cli['tarifa_aotour_proveedor']==1 ) {
            $centro->tarifa_aotour_proveedor = 1;
        }else{
            $centro->tarifa_aotour_proveedor = 0;
        }
        
        $centro->recargo_nocturno = $cli['recargo_nocturno'];
        $centro->desde = $cli['desde'];
        $centro->hasta = $cli['hasta'];
        $centro->siigo = 1;
        $centro->save();

    }

    return Response::json([
        'response' => true
    ]);
    
});

Route::post('trayectos', function(Request $request) {

    $trayectos = $request->trayectos;

    foreach ($trayectos as $tra) {

        if( $tra['ciudad']=="BARRANQUILLA" ) {
            $sede = 1;
        }else if( $tra['ciudad']=="BOGOTA" ) {
            $sede = 2;
        }else if( $tra['ciudad']=="PROVISIONAL" ) {
            $sede = 3;
        }

        $traslado = new Traslado;
        $traslado->id = $tra['id'];
        $traslado->nombre = $tra['nombre'];
        $traslado->fk_sede = $sede;
        $traslado->estado = 1;
        $traslado->created_at = $tra['created_at'];
        $traslado->updated_at = $tra['updated_at'];
        $traslado->save();

    }

    return Response::json([
        'response' => true
    ]);
    
});

Route::post('tarifasv', function(Request $request) {

    $tarifas = $request->tarifas;

    foreach ($tarifas as $tari) {

        $tarifa = new Tarifa;
        $tarifa->id = $tari['id'];
        $tarifa->cliente_auto = $tari['cliente_auto'];
        $tarifa->cliente_van = $tari['cliente_van'];
        $tarifa->proveedor_auto = $tari['proveedor_auto'];
        $tarifa->proveedor_van = $tari['proveedor_van'];
        $tarifa->estado = $tari['estado'];
        $tarifa->centrodecosto_id = $tari['centrodecosto_id'];
        $tarifa->trayecto_id = $tari['trayecto_id'];
        $tarifa->created_at = $tari['created_at'];
        $tarifa->updated_at = $tari['updated_at'];
        $tarifa->save();

    }

    return Response::json([
        'response' => true
    ]);
    
});

Route::post('conductoresupnet', function(Request $request) {

    $conductores = $request->conductores;

    foreach ($conductores as $cond) {

        $nombre_comp = explode(' ',$cond['nombre_completo']);
        $cant_nombre = count($nombre_comp);

        if($cant_nombre===3){
            $primerNombre = $nombre_comp[0];
            $primerApellido = $nombre_comp[1].' '.$nombre_comp[2];
        }else if($cant_nombre>=4){
            $primerNombre = $nombre_comp[0].' '.$nombre_comp[1];
            $primerApellido = $nombre_comp[2].' '.$nombre_comp[3];
        }else{
            $primerNombre = $nombre_comp[0];
            if(isset($nombre_comp[1])){
                $primerApellido = $nombre_comp[1];
            }
        }

        $dep = DB::table('departamentos')
        ->where('nombre',$cond['departamento'])
        ->first();

        if( $cond['departamento']=='CUNDINAMARCA' ) {
            $depa = 2;
        }else{
            $depa = $dep->id;
        }

        $ciu = DB::table('ciudades')
        ->where('nombre',$cond['ciudad'])
        ->first();

        $tipol = DB::table('tipos')
        ->where('nombre',$cond['tipodelicencia'])
        ->first();

        if( $cond['genero']=="MASCULINO" ) {
            $genero = 16;
        }else {
            $genero = 17;
        }

        if( $cond['bloqueado_total']==1 ) {
            $estado = 49;
        }else if( $cond['bloqueado']==1 ) {
            $estado = 51;
        }else {
            $estado = 50;
        }

        $condu = new Conductor;
        $condu->id = $cond['id'];
        $condu->primer_nombre = $primerNombre;
        $condu->primer_apellido = $primerApellido;
        $condu->fecha_vinculacion = $cond['fecha_vinculacion'];
        $condu->fecha_de_nacimiento = $cond['fecha_nacimiento'];
        $condu->fk_departamento = $depa;
        $condu->fk_ciudad = $ciu->id;
        $condu->fk_tipo_documento = 11;
        $condu->numero_documento = $cond['cc'];
        $condu->celular = $cond['celular'];
        $condu->direccion = $cond['direccion'];
        $condu->fk_tipo_licencia = $tipol->id;
        $condu->fecha_licencia_expedicion = $cond['fecha_licencia_expedicion'];
        $condu->fecha_licencia_vigencia = $cond['fecha_licencia_vigencia'];
        $condu->fk_genero = $genero;
        $condu->experiencia = $cond['experiencia'];
        $condu->accidentes = $cond['accidentes'];
        $condu->descripcion_accidente = $cond['descripcion_accidente'];
        $condu->fk_proveedor = $cond['proveedores_id'];
        $condu->usuario_id = $cond['usuario_id'];
        $condu->fk_estado = $estado;
        $condu->fecha_inicio = date('Y-m-d');
        $condu->soporte_capacitacion = 1;
        $condu->licencia_conduccion_sw = 1;
        $condu->licencia_conduccion_pdf = 1;
        $condu->seguridad_social_sw = 1;
        $condu->seguridad_social_pdf = 1;
        $condu->numero_documento_sw = 1;
        $condu->numero_documento_pdf = 1;
        $condu->examenes_sw = 1;
        $condu->examenes_pdf = 1;
        $condu->sw_global = 1;
        $condu->save();

    }

    return Response::json([
        'response' => true
    ]);
    
});
