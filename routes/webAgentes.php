<?php

use App\Http\Controllers\agenteControllers\agenteClienteController;
use App\Http\Controllers\agenteControllers\agenteDesembolsosController;
use App\Http\Controllers\agenteControllers\HomeControllerAgenteController;
use App\Http\Controllers\configuracionController;
use Illuminate\Support\Facades\Route;


Route::get('/cobrador_restringido', [configuracionController::class,'restringidoCobradorPage'])->name('cobrador.restringido');

Route::middleware(['auth','accessCobradorTimeAdminMiddleware','checkUserActive'])->group(function () {
    Route::controller(HomeControllerAgenteController::class)->group(function () {
        Route::get('/agente/home', 'index')->name('agentes.homeAgentes');
        Route::get('/agente/recaudo', 'recaudo')->name('agentes.recaudo');
        Route::get('/agente/misClientes', 'misClientes')->name('agentes.misClientes');
        Route::get('/agente/misClientes/prestamos/{id}', 'prestamosClientes')->name('agentes.prestamosClientes');
        Route::get('/agente/arqueo/index', 'arqueoIndex')->name('agentes.arqueo.index');
        Route::post('/agente/arqueo/saveOrUpdate', 'saveOrUpdateAqueroAgente')->name('agentes.arqueo.saveOrUpdate');

        Route::get('/agente/registroClientes', 'registroClientes')->name('agentes.registroClientes');
        Route::get('/agente/registroClientes/nuevo', 'indexNuevoCliente')->name('agentes.indexNuevoCliente');
        Route::post('/agente/registroClientes/store', 'storeNuevoCliente')->name('agentes.storeNuevoCliente');
        Route::get('/agente/registroClientes/edit/{id}', 'editNuevoCliente')->name('agentes.editNuevoCliente');
        Route::put('/agente/registroClientes/update/{id}', 'updateNuevoCliente')->name('agentes.updateNuevoCliente');
        Route::post('/agente/registroClientes/destroy/{id}', 'destroyNuevoCliente')->name('agentes.destroyNuevoCliente');

        Route::post('/agente/registroClientes/nuevaSolicitud/{id}', 'nuevaSolicitud')->name('agentes.nuevaSolicitud');
        Route::delete('/agente/registroClientes/destroySolicitud/{id}', 'destroySolicitud')->name('agentes.destroySolicitud');


        Route::get('/agente/reportes/index', 'reportesIndex')->name('agentes.reportes.index');
        Route::get('/agente/reportes/estadoCuentaCliente', 'estadoCuentaClientes')->name('agentes.reportes.estadoCuentaCliente');
        Route::get('/agente/reportes/planPago', 'planPago')->name('agentes.reportes.planPago');
        Route::get('/agente/reportes/plan-pago/prestamos/{clienteId}', 'getPrestamosCliente')->name('agentes.reportes.planPago.prestamos');


        Route::get('/agente/cuotas/index', 'cuotas')->name('agentes.reportes.cuotas');
        Route::get('/agente/recuperacion/index', 'colocacion')->name('agentes.reportes.recuperacion');
        Route::get('/agente/cobrosDia/index', 'cobrosDia')->name('agentes.reportes.cobrosDia');

        Route::get('/agente/abonos/index', 'abonos')->name('agentes.abonos.index');
        Route::get('/agente/abonos/create', 'createAbono')->name('agentes.abonos.createAbono');
        Route::get('/agente/abonos/show/{id}', 'verAbono')->name('agentes.abonos.show');
        Route::post('/agente/abonos/anular/anularAbono/{id}', 'anularAbono')->name('agentes.abonos.anularAbono');


        Route::get('/agente/user/clientes/getDatosCliente/{id}', 'getDatosCliente')->name('agentes.user.clientes.getDatosCliente');
        Route::get('/agente/user/clientes/getListClientes', 'getListClientes')->name('agentes.user.clientes.getListClientes');
        Route::get('/agente/user/clientes/getListClientesExternos', 'getListClientesExternos')->name('agentes.user.clientes.getListClientesExternos');
        Route::post('/agente/abonos/cliente/getInfoCliente', 'getInfoCliente')->name('agentes.abonos.getInfoCliente');
        Route::post('/agente/abonos/cuotas/getCuotasPrestamo', 'getCuotasPendientesPrestamos')->name('agentes.abonos.getCuotasPrestamo');
        Route::post('/agente/abonos/cuotas/getDetalleCuota', 'getDetalleCuota')->name('agentes.abonos.getDetalleCuota');
        Route::post('/agente/abonos/storeAbono', 'storeAbono')->name('agentes.abonos.storeAbono');
        Route::get('/agente/printRecibo/{abonoId}', 'printRecibo')->name('agentes.abonos.printRecibo');
    });

    Route::controller(agenteClienteController::class)->group(function () {
        Route::get('/agente/user/clientes/index', 'index')->name('agentes.user.clientes.index');
        Route::get('/agente/user/clientes/create', 'create')->name('agentes.user.clientes.create');
        Route::post('/agente/user/clientes/store', 'store')->name('agentes.user.clientes.store');
        Route::get('/agente/user/clientes/edit/{id}', 'edit')->name('agentes.user.clientes.edit');
        Route::put('/agente/user/clientes/update/{id}', 'update')->name('agentes.user.clientes.update');
        Route::delete('/agente/user/clientes/destroy/{id}', 'destroy')->name('agentes.user.clientes.destroy');

        Route::get('/agente/user/negocios/create', 'createNegocio')->name('agentes.user.negocios.create');
        Route::post('/agente/user/negocios/store', 'storeNegocio')->name('agentes.user.negocios.store');
        Route::get('/agente/user/negocios/edit/{id}', 'editNegocio')->name('agentes.user.negocios.edit');
        Route::put('/agente/user/negocios/update/{id}', 'updateNegocio')->name('agentes.user.negocios.update');

        Route::get('/agente/user/fiador/create', 'createFiador')->name('agentes.user.fiador.create');
        Route::post('/agente/user/fiador/store', 'storeFiador')->name('agentes.user.fiador.store');
        Route::get('/agente/user/fiador/edit/{id}', 'editFiador')->name('agentes.user.fiador.edit');
        Route::put('/agente/user/fiador/update/{id}', 'updateFiador')->name('agentes.user.fiador.update');

    });


    Route::controller(agenteDesembolsosController::class)->group(function () {
        Route::get('/agente/user/desembolso/index', 'index')->name('agentes.user.desembolso.index');
        Route::get('/agente/user/desembolso/create', 'create')->name('agentes.user.desembolso.create');
        Route::post('/agente/user/desembolso/store', 'store')->name('agentes.user.desembolso.store');
        Route::get('/agente/user/desembolso/show/{id}', 'show')->name('agentes.user.desembolso.show');
        Route::delete('/agente/user/desembolso/destroy/{id}', 'destroy')->name('agentes.user.desembolso.destroy');
        Route::put('/agente/user/desembolso/update/{id}', 'update')->name('agentes.user.desembolso.update');

        Route::get('agente/user/getAgentes', 'getAgentes')->name('agentes.user.getAgentes');
        Route::get('agente/user/getListAdministrativos', 'getAdministrativos')->name('agente.user.getListAdministrativos');
        Route::get('agente/abono/getList/{id}', 'getListAbonos')->name('agente.abono.getListAbonos');

        Route::get('agente/clientes/getListClientes', 'getClientesArray')->name('agente.user.clientes.getListClientes');
        Route::get('agente/clientes/getListNegociosCliente/{id}', 'getListNegociosCliente')->name('agente.negocio.getListNegociosCliente');
        Route::get('agente/clientes/getDatosCliente/{id}', 'getDatosCliente')->name('agente.clientes.getDatosCliente');
        Route::get('agente/negocio/getInfoNegocio/{id}', 'getInformacionNegocio')->name('agente.negocio.getInfoNegocio');
        Route::post('agente/negocio/storeNegocioCliente/{id}', 'storeNegocioCliente')->name('agente.negocio.storeNegocioCliente');
        Route::get('agente/configuracion/tiposNegocios/getListTiposNegocios', 'getListTiposNegocios')->name('agente.configuracion.tiposNegocios.getListTiposNegocios');
        Route::get('agente/fiador/getListFiadoresUser/{id}', 'getListFiadoresUser')->name('agente.fiador.getListFiadoresUser');
        Route::get('agente/fiador/getDatos/{id}', 'getDatos')->name('agente.fiador.getDatos');
        Route::post('agente/fiador/storeFiador/{id}', 'storeFiador')->name('agente.fiador.storeFiador');

        Route::get('agente/simulador/index', 'simulador')->name('agentes.simulador');
        Route::get('agente/feriados/getFeriados', 'getFeriados')->name('agente.feriados.getFeriados');


    });

});
