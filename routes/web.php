<?php

use App\Http\Controllers\abonoController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\configuracionController;
use App\Http\Controllers\fiadorController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\permissionController;
use App\Http\Controllers\prestamosController;
use App\Http\Controllers\reportesController;
use App\Http\Controllers\roleController;
use App\Http\Controllers\sucursalController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\userNegocioController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Auth::routes();
Route::post('login2', [LoginController::class, 'authenticate'])->name('login2');
Route::get('/restringido', [configuracionController::class,'restringidoAdminPage'])->name('restringido');

Route::middleware(['auth','adminMiddleware','accessTimeAdminMiddleware','checkUserActive'])->group(function () {
    Route::get('logs', [LogViewerController::class, 'index']);
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // SUCURSALES
    Route::controller(sucursalController::class)->group(function () {
        Route::resource('sucursales', sucursalController::class);
        Route::get('sucursales/ajax/lista', 'getListSucursales')->name('sucursales.getList');
    });


    Route::post('/importClientes', [UserController::class, 'importarClientes'])->name('user.clientes-importar');

    Route::controller(UserController::class)->group(function () {

        Route::get('user/indexUsuarios', 'indexUsuarios')->name('user.indexUsuarios');

        //CLIENTES
        Route::get('user/clientes/index', 'indexClientes')->name('user.clientes.index');
        Route::get('user/clientes/create', 'createClientes')->name('user.clientes.create');
        Route::post('user/clientes/store', 'storeCliente')->name('user.clientes.store');
        Route::get('user/clientes/edit/{id}', 'editCliente')->name('user.clientes.edit');
        Route::put('user/clientes/update/{id}', 'updateCliente')->name('user.clientes.update');
        Route::delete('user/clientes/destroy/{id}', 'destroyCliente')->name('user.clientes.destroy');

        Route::get('user/clientes/eliminarDocumento/{id}', 'eliminarDocumento')->name('user.clientes.eliminarDocumento');
        Route::get('user/clientes/eliminarFoto/{id}', 'eliminarFoto')->name('user.clientes.eliminarFoto');

        //AXIOS
        Route::get('user/clientes/getDatosCliente/{id}', 'getDatosCliente')->name('user.clientes.getDatosCliente');
        Route::get('user/clientes/getListClientes', 'getClientesArray')->name('user.clientes.getListClientes');
        //

        //AGENTES
        Route::get('user/agentes/index', 'indexAgentes')->name('user.agentes.index');
        Route::get('user/agentes/create', 'createAgente')->name('user.agentes.create');
        Route::post('user/agentes/store', 'storeAgente')->name('user.agentes.store');
        Route::get('user/agentes/edit/{id}', 'editAgente')->name('user.agentes.edit');
        Route::put('user/agentes/update/{id}', 'updateAgente')->name('user.agentes.update');
        Route::delete('user/agentes/destroy/{id}', 'destroyAgente')->name('user.agentes.destroy');

        Route::get('user/agentes/asignados/{id}', 'clientesAsignados')->name('user.agentes.asignados');
        Route::get('user/admins/asignados/{id}', 'asignarUsuarios')->name('user.admins.asignados');
        Route::post('user/admins/asignar/{id}', 'asignar')->name('user.admins.asignar');
        Route::delete('user/admins/quitarAsignacion/{id}', 'quitarAsignacion')->name('user.admins.quitarAsignacion');

        //ADMINS
        Route::resource('user', UserController::class);

        //AXIOS
        Route::get('user/administrativos/getListAdministrativos', 'getAdministrativos')->name('user.getListAdministrativos');
        Route::get('user/agentes/getAgentes', 'getAgentes')->name('user.agentes.getAgentes');

        //

        Route::post('user/contrasenya/cambiarContrasenya/{id}', 'cambiarContrasenyaUser')->name('user.cambiarContrasenyaUser');
    });

    Route::controller(fiadorController::class)->group(function (){
        Route::resource('fiador', fiadorController::class);

        //AXIOS
        Route::get('fiador/getListFiadoresUser/{id}', 'getListFiadoresUser')->name('fiador.getListFiadoresUser');
        Route::get('fiador/getDatos/{id}', 'getDatos')->name('fiador.getDatos');
        Route::post('fiador/storeFiador/{id}', 'storeFiador')->name('fiador.storeFiador');
    });

    Route::controller(configuracionController::class)->group(function (){
        Route::get('configuracion/index', 'indexConfiguracion')->name('configuracion.index');
        //DOCUMENTOS
        Route::get('configuracion/documentos/index', 'indexDocumentos')->name('configuracion.documentos.index');
        Route::post('configuracion/documentos/store', 'storeTipoDocumento')->name('configuracion.documentos.store');
        Route::put('configuracion/documentos/update/{id}', 'updateTipoDocumento')->name('configuracion.documentos.update');
        Route::delete('configuracion/documentos/destroy/{id}', 'destroyTipoDocumento')->name('configuracion.documentos.destroy');

        Route::get('configuracion/feriados/index', 'indexFeriados')->name('configuracion.feriados.indexFeriados');
        Route::post('configuracion/feriados/store', 'storeFeriados')->name('configuracion.feriados.storeFeriados');
        Route::put('configuracion/feriados/update/{id}', 'updateFeriados')->name('configuracion.feriados.updateFeriados');
        Route::delete('configuracion/feriados/destroy/{id}', 'destroyFeriados')->name('configuracion.feriados.destroyFeriados');
        Route::get('configuracion/feriados/get-prestamos-ids', 'getPrestamosParaRecalcular')->name('configuracion.feriados.getPrestamosIds');
        Route::post('configuracion/feriados/recalcular-planes', 'recalcularPlanesPorFeriados')->name('configuracion.feriados.recalcularPlanes');
        //AXIOS
        Route::get('configuracion/documentos/getDatos/{id}', 'getDatos')->name('configuracion.documentos.getDatos');
        Route::get('configuracion/feriados/getFeriados', 'getFeriados')->name('configuracion.feriados.getFeriados');
        //

        //**********************************
        Route::get('configuracion/tiposNegocios/index', 'indexTiposNegocios')->name('configuracion.tiposNegocios.index');
        Route::post('configuracion/tiposNegocios/store', 'storeTiposNegocios')->name('configuracion.tiposNegocios.store');
        Route::put('configuracion/tiposNegocios/update/{id}', 'updateTiposNegocios')->name('configuracion.tiposNegocios.update');
        Route::delete('configuracion/tiposNegocios/destroy/{id}', 'destroyTiposNegocios')->name('configuracion.tiposNegocios.destroy');
        //AXIOS
        Route::get('configuracion/tiposNegocios/getDatos/{id}', 'getDatosTipoNegocio')->name('configuracion.tiposNegocios.getDatos');
        Route::get('configuracion/tiposNegocios/getListTiposNegocios', 'getListTiposNegocios')->name('configuracion.tiposNegocios.getListTiposNegocios');
        //

        Route::get('configuracion/tipoCambio/index', 'indexTipoCambio')->name('configuracion.tipoCambio.index');
        Route::post('configuracion/tipoCambio/import', 'importTipoCambio')->name('configuracion.tipoCambio.import');

        Route::get('configuracion/onlineUsers/index', 'onlineUsers')->name('configuracion.onlineUsers.index');
        Route::get('configuracion/activacion/sistema', 'activacion_sistema')->name('configuracion.configuracion.activacion_sistema')->middleware('can:Establecer Horas Activas Sistema');
        Route::post('configuracion/updactivacion/sistema/{id}', 'updactivacion_sistema')->name('configuracion.configuracion.updactivacion_sistema')->middleware('can:Establecer Horas Activas Sistema');


        //**********************************
    });

    Route::controller(prestamosController::class)->group(function (){
        Route::resource('prestamos', prestamosController::class);

        Route::get('prestamos/solicitudes/index', 'indexSolicitudes')->name('prestamos.solicitudes.indexSolicitudes');
        Route::get('prestamos/solicitudes/cambiarEstado/{id}/{estado}', 'cambiarEstadoSolicitud')->name('prestamos.solicitudes.cambiarEstadoSolicitud');
        Route::get('prestamos/solicitudes/prestamoDesembolsado/{id}', 'prestamoDesembolsado')->name('prestamos.solicitudes.prestamoDesembolsado');
        Route::put('prestamos/solicitudes/updateSolicitud/{id}', 'updateSolicitud')->name('prestamos.solicitudes.updateSolicitud');

        Route::post('prestamos/editarFechaCuota/{id}', 'editarFechaCuota')->name('prestamos.editarFechaCuota')->middleware('can:Modificar Fechas de Cuotas');
        //REPRESTAMO

        Route::post('prestamos/represtamo/store/{id}', 'storeReprestamo')->name('prestamos.represtamo.store');
        Route::post('prestamos/modificarUsuario/{id}', 'actualizarVendedorCobradorPrestamo')->name('prestamo.actualizar.usuario');


        Route::get('prestamos/simulador/index', 'simulador')->name('prestamo.simulador');

        //ABONOS
        Route::post('prestamos/abono/store/{id}', 'registrarAbono')->name('prestamos.abono.store');
        Route::get('prestamos/abono/getList/{id}', 'getListAbonos')->name('prestamos.abono.getListAbonos');

        Route::post('prestamos/clasificar/{id}', 'clasificar_prestamo')->name('prestamos.clasificar_prestamo');

        Route::delete('prestamos/eliminar/{id}', 'eliminar')->name('prestamos.eliminar')->middleware('can:Eliminar Desembolsos');

        //AXIOS
        Route::get('prestamos/getInfoNegocio/{id}', 'getInformacionNegocio')->name('prestamos.getInfoNegocio');
    });

    Route::controller(userNegocioController::class)->group(function (){
        Route::resource('negocio', userNegocioController::class);

        //AXIOS
        Route::get('negocio/getListNegociosCliente/{id}', 'getListNegociosCliente')->name('negocio.getListNegociosCliente');
        Route::get('negocio/getInfoNegocio/{id}', 'getInformacionNegocio')->name('negocio.getInfoNegocio');

        Route::post('negocio/storeNegocioCliente/{id}', 'storeNegocioCliente')->name('negocio.storeNegocioCliente');
    });

    Route::controller(permissionController::class)->group(function (){
        Route::resource('permisos', permissionController::class);
        Route::post('permisos/asignarPermisosRol/{id}', 'asignarPermisosRol')->name('permisos.asignarPermisosRol');
        //AXIOS
        Route::get('permisos/getPermiso/{id}', 'getPermiso')->name('permisos.getPermiso');
    });

    Route::controller(roleController::class)->group(function (){
        Route::resource('roles', roleController::class);
        Route::get('roles/detallePermisos/{id}', 'detallePermisos')->name('roles.detallePermisos');

        //AXIOS
        Route::get('roles/getRol/{id}', 'getRol')->name('roles.getRol');
        Route::post('roles/asignarRol/{id}', 'asignarRol')->name('roles.asignarRol');
        Route::get('roles/desAsignarRol/{id}/{userId}', 'desAsignarRol')->name('roles.desAsignarRol');
    });

    Route::controller(abonoController::class)->group(function (){
        Route::resource('abonos', abonoController::class);

        Route::get('abonos/lista/getListClientes', 'getListClientes')->name('abonos.getListClientes');
        Route::post('abonos/cliente/getInfoCliente', 'getInfoCliente')->name('abonos.getInfoCliente');
        Route::post('abonos/cuotas/getCuotasPrestamo', 'getCuotasPendientesPrestamos')->name('abonos.getCuotasPrestamo');
        Route::post('abonos/cuotas/getDetalleCuota', 'getDetalleCuota')->name('abonos.getDetalleCuota');

        Route::post('abonos/anular/anularAbono/{id}', 'anularAbono')->name('abonos.anularAbono')->middleware('can:Anular Abonos');

        Route::get('printRecibo/{abonoId}', 'printRecibo')->name('abonos.printRecibo');
    });

    Route::controller(reportesController::class)->group(function (){
        Route::get('reportes/index', 'index')->name('reportes.index');
        Route::get('reportes/listaClientes/index', 'listClientes')->name('reportes.listaClientes.index');
        Route::get('reportes/listaCuotas/index', 'listaCuotas')->name('reportes.listaCuotas.index');
        Route::get('reportes/detalleColocacion/index', 'detalleColocacion')->name('reportes.detalleColocacion.index');
        Route::get('reportes/cuotasVencidas/index', 'cuotasVencidas')->name('reportes.cuotasVencidas.index');
        Route::get('reportes/prestamosVencidos/index', 'prestamosVencidos')->name('reportes.prestamosVencidos.index');
        Route::get('reportes/creditosVencidos/index', 'creditosVencidos')->name('reportes.creditosVencidos.index');

        Route::get('reportes/arqueo/list', 'listaArqueo')->name('reportes.arqueo.list');
        Route::get('reportes/arqueo/index/{id}', 'arqueo')->name('reportes.arqueo.index');
        Route::get('reportes/arqueo/nuevo', 'nuevoArqueo')->name('reportes.arqueo.nuevoArqueo');
        Route::post('reportes/arqueo/store', 'storeArqueo')->name('reportes.arqueo.storeArqueo');
        Route::post('reportes/arqueo/saveUpdate', 'saveOrUpdateAquero')->name('reportes.arqueo.saveUpdate');
        Route::get('reportes/arqueo/detalleCuotas/{id}/{id2}/{id3}', 'arqueoDetalleCuotas')->name('reportes.arqueo.arqueoDetalleCuotas');
        Route::get('reportes/arqueo/exportarArqueo/{id}', 'exportarArqueo')->name('reportes.arqueo.exportarArqueo');
        Route::delete('reportes/arqueo/eliminar/{id}', 'eliminarArqueo')->name('reportes.arqueo.eliminar');

        Route::get('reportes/asignacionClientes/index', 'asignacionClientes')->name('reportes.asignacionClientes.index');
        Route::get('reportes/asignacionClientes/reasignar', 'reasignarCobradorPrestamo')->name('reportes.asignacionClientes.reasignar');
        Route::post('reportes/asignacionClientes/transferirCartera', 'transferirCarteraCompleta')->name('reportes.asignacionClientes.transferirCartera');

        Route::get('reportes/cobrosDia', 'cobrosDia')->name('reportes.cobrosDia');

        Route::get('reportes/colocacionV2', 'colocacionV2')->name('reportes.colocacionV2');

        Route::get('reportes/cobranza', 'cobranza')->name('reportes.cobranza');
        Route::get('reportes/saldoCartera', 'saldoCartera')->name('reportes.saldoCartera');

        Route::get('reportes/estadoClientes', 'estadoClientes')->name('reportes.estadoClientes');
        Route::get('reportes/estadoCuentaCliente', 'estadoCuentaClientes')->name('reportes.estadoCuentaCliente');

        Route::get('reportes/planPago', 'planPago')->name('reportes.planPago');
        Route::get('reportes/plan-pago/prestamos/{clienteId}', 'getPrestamosCliente')->name('reportes.planPago.prestamos');


        Route::get('reportes/antiguedad_saldos', 'antiguedad_saldos')->name('reportes.antiguedad_saldos');
        Route::get('reportes/cartera_diaria', 'carteraDiaria')->name('reportes.cartera_diaria');
        Route::get('reportes/colocacion-vs-recuperacion', 'colocacionVsRecuperacion')->name('reportes.colocacionVsRecuperacion');
        Route::get('reportes/colocacion-vs-recuperacion/html', 'colocacionVsRecuperacion')->name('reportes.colocacionVsRecuperacion.html');
        // Nuevas rutas HTML para vistas de reportes
        Route::get('reportes/listaClientes/html', 'listClientes')->name('reportes.listaClientes.html');
        Route::get('reportes/listaCuotas/html', 'listaCuotas')->name('reportes.listaCuotas.html');
        Route::get('reportes/detalleColocacion/html', 'detalleColocacion')->name('reportes.detalleColocacion.html');
        Route::get('reportes/cuotasVencidas/html', 'cuotasVencidas')->name('reportes.cuotasVencidas.html');
        Route::get('reportes/prestamosVencidos/html', 'prestamosVencidos')->name('reportes.prestamosVencidos.html');
        Route::get('reportes/creditosVencidos/html', 'creditosVencidos')->name('reportes.creditosVencidos.html');
        Route::get('reportes/asignacionClientes/html', 'asignacionClientes')->name('reportes.asignacionClientes.html');
        Route::get('reportes/cobrosDia/html', 'cobrosDia')->name('reportes.cobrosDia.html');
        Route::get('reportes/cobranza/html', 'cobranza')->name('reportes.cobranza.html');
        Route::get('reportes/saldoCartera/html', 'saldoCartera')->name('reportes.saldoCartera.html');
        Route::get('reportes/estadoClientes/html', 'estadoClientes')->name('reportes.estadoClientes.html');
        Route::get('reportes/antiguedad_saldos/html', 'antiguedad_saldos')->name('reportes.antiguedad_saldos.html');
        Route::get('reportes/cartera_diaria/html', 'carteraDiaria')->name('reportes.cartera_diaria.html');
    });


});
