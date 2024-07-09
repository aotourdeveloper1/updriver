<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="url" content="{{url('/')}}">
    <title>Upnet | Usuarios</title>
    <link href="{{url('images/logo.png')}}" rel="icon" type="image/x-icon" />
    @include('scripts.styles')
    <link rel="stylesheet" href="{{url('js/bootstrap/css/datatables.css')}}">
    <link rel="stylesheet" href="{{url('js/datatables/media/css/dataTables.bootstrap.css')}}">
    <link rel="stylesheet" href="{{url('js/bootstrap-toggle-master\css\bootstrap-toggle.min.css')}}">
    <link href="{{url('js/font-awesome-new/css/fontawesome.css')}}" rel="stylesheet">
    <link href="{{url('js/font-awesome-new/css/brands.css')}}" rel="stylesheet">
    <link href="{{url('js/font-awesome-new/css/solid.css')}}" rel="stylesheet">
  </head>
  <body>
      @include('admin.menu')
      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <table id="example" class="table table-bordered table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>Usuario</th>
              <th>Nombre Completo</th>
              <th>Ultima entrada</th>
              <th>Informacion</th>
            </tr>
          </thead>
          <tbody>
            @foreach($usuarios as $usuario)
            <tr>
              <td>{{$usuario->id}}</td>
              <td>{{$usuario->username}}</td>
              <td>{{$usuario->first_name.' '.$usuario->last_name}}</td>
              <td>{{$usuario->last_login}}</td>
              <td>
                <a data-id="{{$usuario->id}}" class="btn btn-primary btn-list-table bolder cambiar_contrasena">CAMBIAR CONTRASEÑA <i class="fa fa-unlock-alt" aria-hidden="true"></i></a>
                <a data-id="{{$usuario->id}}" class="btn btn-info btn-list-table bolder asignar_roles">roles <i class="fa fa-male" aria-hidden="true"></i></a>
                <a data-id="{{$usuario->id}}" data-option="0" class="btn btn-danger btn-list-table bolder banear_usuario">BLOQUEAR USUARIO <i class="fa fa-lock" aria-hidden="true"></i></a>
                <a data-id="{{$usuario->id}}" data-option="1" class="btn btn-success btn-list-table bolder banear_usuario">DESBLOQUEAR USUARIO <i class="fa fa-unlock-alt" aria-hidden="true"></i></a>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
        <button class="btn btn-default btn-icon input-font" data-toggle="modal" data-target=".mymodal">AGREGAR<i class="fa fa-plus icon-btn"></i></button>
        <div class="btn-group dropup">
            <button class="btn btn-default btn-icon dropdown-toggle input-font" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                ROLES<i class="icon-btn fa fa-bars"></i>
            </button>
            <ul class="dropdown-menu">
                <li><a style="cursor: pointer;" class="input-font" data-toggle="modal" data-target=".mymodal2" id="modal_crear">CREAR</a></li>
                <li><a style="cursor: pointer;" class="input-font" data-toggle="modal" data-target=".mymodal3" id="ver_roles">VER</a></li>
            </ul>
        </div>
      </div>

      <div class="modal fade mymodal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-usuarios">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <strong>NUEVO USUARIO</strong>
            </div>
            <form id="formulario">
              <div class="modal-body">
                <div class="row">
                  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                    <label for="nombres">Nombres</label>
                    <input class="form-control input-font" type="text" name="nombres">
                  </div>
                  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 ">
                    <label for="apellidos">Apellidos</label>
                    <input class="form-control input-font" type="text" name="apellidos">
                  </div>
                  <div class='col-lg-6 col-md-6 col-sm-6 col-xs-6'>
                    <label for="contrasena">Contraseña</label>
                    <input class="form-control" name="contrasena" type="password">
                  </div>
                  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 usuario_contrasena">
                    <div class="has-feedback">
                      <label class="control-label" for="repetir_contrasena">Repetir Contraseña</label>
                      <input name="repetir_contrasena" type="password" class="form-control" id="inputSuccess2" aria-describedby="inputSuccess2Status">
                      <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                    </div>
                  </div>
                  <div class='col-lg-6 col-md-6 col-sm-6 col-xs-6'>
                    <label for="contrasena">Roles</label>
                    <select class="form-control input-font" name="rol" id="rol">
                        <option value="0">-</option>
                        @foreach($roles as $rol)
                            <option value="{{$rol->id}}">{{$rol->nombre_rol}}</option>
                        @endforeach
                    </select>
                  </div>
                  <div class='col-lg-6 col-md-6 col-sm-6 col-xs-6'>
                    <label for="contrasena">Localidad</label>
                    <select class="form-control input-font" name="localidad" id="localidad">
                        <option value="0">-</option>
                        <option value="1">Barranquilla</option>
                        <option value="2">Bogota</option>
                        <option value="3">Administrador</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button id="guardar" type="button" class="btn btn-primary btn-icon">GUARDAR<i class="fa fa-save icon-btn"></i></button>
                <button type="button" class="btn btn-danger btn-icon" data-dismiss="modal">CERRAR<i class="fa fa-close icon-btn"></i></button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="modal fade mymodal2" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-roles">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <strong>NUEVO ROL</strong>
            </div>
            <form id="formulario_roles">
              <div class="modal-body" style="padding-top: 0; overflow-y: auto; height: 600px;" id="roles" >

              </div>
              <div class="modal-footer">
                <button id="crear_rol" type="button" class="btn btn-primary btn-icon">GUARDAR<i class="fa fa-save icon-btn"></i></button>
                <button type="button" class="btn btn-danger btn-icon" data-dismiss="modal">CERRAR<i class="fa fa-close icon-btn"></i></button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="modal fade mymodal3" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
          <div class="modal-dialog modal-roles">
              <div class="modal-content">
                  <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                      <strong>ROLES</strong>
                  </div>
                  <form id="listado_roles">

                          <div class="modal-body" id="ver_roles" style="overflow-y: auto; height: 500px;">
                               <table id="tb_roles" class="table table-bordered hidden">
                                   <thead>
                                       <th>NOMBRE</th>
                                       <th>PERMISOS</th>
                                       <th>CREADO</th>
                                   </thead>
                                   <tbody>

                                   </tbody>
                               </table>
                          </div>

                      <div class="modal-footer">
                          <button type="button" class="btn btn-danger btn-icon" data-dismiss="modal">CERRAR<i class="fa fa-close icon-btn"></i></button>
                      </div>
                  </form>
              </div>
          </div>
      </div>

      <div class="modal fade mymodal4" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
          <div class="modal-dialog modal-roles">
              <div class="modal-content">
                  <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                      <strong>EDITAR ROL</strong>
                  </div>
                  <form id="edicion_roles">
                      <div class="modal-body" id="edit_roles" style="overflow-y: auto; height: 500px;">

                      </div>
                      <div class="modal-footer">
                          <button id="actualizar_rol" type="button" class="btn btn-primary btn-icon">GUARDAR<i class="fa fa-refresh icon-btn"></i></button>
                          <button type="button" class="btn btn-danger btn-icon" data-dismiss="modal">CERRAR<i class="fa fa-close icon-btn"></i></button>
                      </div>
                  </form>
              </div>
          </div>
      </div>

      <div class="contenedor_informacion_usuario hidden">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
          <div class="panel panel-primary">
            <div class="panel-heading">
              <strong>EDITAR USUARIO</strong><i id="cerrar_alerta" style="float: right; font-weight:100; margin-top: 2px; cursor: pointer;" class="fa fa-close"></i>
            </div>
            <div class="panel-body">
              <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                  <label class="obligatorio" for="nombres">Contraseña</label>
                  <input class="form-control" type="password" name="editar_contrasena" value="">
                </div>
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 editar_contrasena">
                  <div class="has-feedback">
                    <label class="control-label" for="editar_repetir_contrasena">Repetir Contraseña</label>
                    <input name="editar_repetir_contrasena" type="password" class="form-control" id="inputSuccess2" aria-describedby="inputSuccess2Status">
                    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                  </div>
                </div>
              </div>
            </div>
            <div class="panel-footer">
              <button id="cambiar_contrasena" class="btn btn-primary btn-icon" type="button" name="button">GUARDAR<i class="fa fa-refresh icon-btn"></i></button>
            </div>
          </div>
        </div>
      </div>

      <div class="contenedor_informacion_rol hidden">
        <div class="col-xs-12">
            <div class="panel panel-default">
                <div class="panel-heading"><strong>ROL DE USUARIO</strong><i id="cerrar_alerta" style="float: right; font-weight:100; margin-top: 2px; cursor: pointer;" class="fa fa-close"></i></div>
                <div class="panel-body">
                    <div class="form-group">
                        <label for="">Rol del Usuario</label>
                        <select type="text" class="form-control input-font" id="rol_usuario">
                            <option value="0">-</option>
                            @foreach($roles as $roles)
                                <option value="{{$roles->id}}">{{$roles->nombre_rol}}</option>
                            @endforeach
                        </select>
                    </div>
                    <button id="guardar_rol_usuario" class="btn btn-primary btn-icon">GUARDAR<i class="fa fa-save icon-btn"></i></button>
                </div>
            </div>
        </div>
      </div>

      <div style="left: 40%" class="errores-modal bg-danger text-danger hidden model">
          <i style="cursor: pointer; position: absolute;right: 5px;top: 4px;" class="fa fa-close cerrar"></i>
          <ul>
          </ul>
      </div>

      @include('scripts.scripts')

      <script src="{{url('js/datatables/media/js/jquery.datatables.js')}}"></script>
      <script src="{{url('js/bootstrap-toggle-master\js\bootstrap-toggle.min.js')}}"></script>
      <script src="{{url('jquery/usuarios.js')}}"></script>
      <script>

        $(function(){

          function rellenarRoles(json, div){

            $(div).html('');
            //CONTANDO CANTIDAD DE MODULOS
            var count = Object.keys(json).length;

            //ARRAY DE MODULOS
            var modulos = Object.keys(json);

            //RENDERIZANDO EL ROL
            $(div).append('<div class="col-xs-12"><div class="row">' +
                                    '<div class="col-xs-3">' +
                                          '<div class="row">'+
                                              '<div class="form-group">' +
                                                  '<label class="obligatorio">Nombre del rol</label>' +
                                                  '<input class="form-control input-font nombre_rol" name="nombre_rol">' +
                                              '</div>' +
                                          '</div>' +
                                    '</div>' +
                                '</div>');

            for (var i = 0; i < count; i++) {

                //AGREGANDO MODULOS AL ROL(HTML)
                $(div).append('<h6><strong>'+modulos[i].toUpperCase()+'</strong></h6>');

                contar = 0;

                //CONTANDO LA CANTIDAD DE SUB-MODULOS
                contar = Object.keys(data[modulos[i]]).length;

                //ARRAY DE SUB-MODULOS
                var subModulos = Object.keys(data[modulos[i]]);

                //RENDERIZANDO LOS SUBMODULOS

                for (var k = 0; k < contar; k++) {

                    //LETRA CAPITAL PARA SUB-MODULOS
                    str = subModulos[k].toLowerCase().replace(/\b[a-z]/g, function(letter) {
                        return letter.toUpperCase();
                    });

                    //AGREGANDO SUB-MODULOS
                    $(div).append('<fieldset style="margin-bottom: 5px;" class="'+subModulos[k]+'"><legend style="margin-bottom: 8px; font-weight: 500;">'+str+'</legend><div class="row"></div></fieldset>');

                    cont = 0;

                    //CONTANDO LA CANTIDAD DE OPCIONES POR SUBMODULO
                    cont = Object.keys(data[modulos[i]][subModulos[k]]).length;
                    var opciones = Object.keys(data[modulos[i]][subModulos[k]]);
                    var aopciones = Object.values(data[modulos[i]][subModulos[k]]);

                    //RENDERIZANDO LAS OPCIONES
                    for (var m = 0; m < cont; m++) {

                        checked = aopciones[m];

                        if (checked==='on'){
                            checkedVal = 'checked';
                        }else{
                            checkedVal = '';
                        }

                        $(div+' .'+subModulos[k]+' .row')
                            .append('<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">'+
                                    '<label class="label-toggle checkbox-inline">'+
                                        '<span style="vertical-align: -webkit-baseline-middle; font-size: 11px; font-weight: bolder;">'+opciones[m].toUpperCase()+'</span>'+
                                        '<input type="checkbox" '+checkedVal+' name="'+modulos[i]+'.'+subModulos[k]+'.'+opciones[m]+'" class="roles_checkbox" data-toggle="toggle" data-size="small" data-on="Si" data-off="No">'+
                                    '</label></div>');

                        $("[data-toggle='toggle']").bootstrapToggle('destroy');
                        $("[data-toggle='toggle']").bootstrapToggle();
                    }
                }
            }
          }

          var data = {
                  "portalusuarios": {
                      "admin": {
                        "ver": null
                      },
                      "qrusers":{
                        "ver": null
                      },
                      "bancos":{
                        "ver": null
                      },
                      "ejecutivo":{
                        "ver": null
                      },
                      "gestiondocumental":{
                        "ver": null
                      }
                  },
                  "portalproveedores": {
                      "documentacion": {
                        "ver": null,
                        "creacion": null
                      },
                      "cuentasdecobro":{
                        "ver": null,
                        "creacion": null,
                        "historial": null
                      }
                  },
                  "escolar": {
                      "gestion": {
                        "ver": null,
                      }
                  },
                  "transporteescolar": {
                      "gestionusuarios": {
                        "ver": null,
                        "creacion": null
                      }
                  },
                  "transportes": {
                      "plan_rodamiento": {
                        "ver": null
                      },
                  },
                  "barranquilla": {
                      "transportesbq": {
                        "ver": null
                      },
                      "serviciosbq": {
                          "ver": null,
                          "creacion": null,
                          "edicion": null,
                          "eliminacion": null
                      },
                      "reconfirmacionbq": {
                          "ver": null,
                          "reconfirmar": null
                      },
                      "novedadbq": {
                          "ver": null,
                          "crear": null
                      },
                      "reportesbq": {
                          "ver": null,
                          "crear": null
                      },
                      "encuestabq": {
                          "ver": null,
                          "crear": null
                      },
                      "constanciabq": {
                          "crear": null,
                          "edicion": null
                      },
                      "poreliminarbq": {
                          "ver": null,
                          "rechazar": null,
                          "eliminar": null
                      },
                      "poraceptarbq": {
                            "ver": null,
                            "rechazar": null,
                            "eliminar": null
                      },
                      "ejecutivosbq": {
                            "ver": null,
                            "crear": null,
                      },
                      "afiliadosexternosbq": {
                            "ver": null
                      },
                      "papeleradereciclajebq": {
                          "ver": null
                      }
                  },


                  "bogota": {
                      "transportes": {
                        "ver": null
                      },
                      "servicios": {
                          "ver": null,
                          "creacion": null,
                          "edicion": null,
                          "eliminacion": null
                      },
                      "reconfirmacion": {
                          "ver": null,
                          "reconfirmar": null
                      },
                      "novedad": {
                          "ver": null,
                          "crear": null
                      },
                      "reportes": {
                          "ver": null,
                          "crear": null
                      },
                      "encuesta": {
                          "ver": null,
                          "crear": null
                      },
                      "constancia": {
                          "crear": null,
                          "edicion": null
                      },
                      "poreliminar": {
                          "ver": null,
                          "rechazar": null,
                          "eliminar": null
                      },
                      "poraceptar": {
                          "ver": null,
                          "rechazar": null,
                          "eliminar": null
                      },
                      "ejecutivos": {
                          "ver": null,
                          "crear": null,
                      },
                      "afiliadosexternos": {
                            "ver": null
                      },
                      "papeleradereciclaje": {
                          "ver": null
                      }
                  },
                  "otrostransporte": {
                      "otrostransporte": {
                        "ver": null
                      }
                  },

                  "facturacion": {
                      "revision": {
                          "ver": null,
                          "crear": null
                      },
                      "liquidacion": {
                          "ver": null,
                          "liquidar": null,
                          "generar_liquidacion": null
                      },
                      "autorizar": {
                          "ver": null,
                          "autorizar": null,
                          "anular": null,
                          "generar_factura": null
                      },
                      "ordenes_de_facturacion": {
                          "ver": null,
                          "anular": null,
                          "ingreso": null,
                          "ingreso_imagenes": null,
                          "revision": null
                      }
                  },
                  "contabilidad": {
                      "pago_proveedores":{
                          "ver": null,
                          "generar_orden_pago": null
                      },
                      "factura_proveedores":{
                          "ver": null,
                          "cerrar_pago":null,
                          "revisar": null,
                          "anular": null
                      },
                      "listado_de_pagos_preparar":{
                          "ver": null,
                          "preparar": null
                      },
                      "listado_de_pagos_auditar":{
                          "ver": null,
                          "auditar": null
                      },
                      "listado_de_pagos_autorizar":{
                          "ver": null,
                          "autorizar": null
                      },
                      "listado_de_pagados":{
                          "ver": null
                      },
                      "comisiones":{
                          "ver": null,
                          "generar_pago": null
                      },
                      "pago_de_comisiones":{
                          "ver": null,
                          "revisar": null
                      },
                      "pagos_por_autorizar_comision":{
                          "ver": null,
                          "autorizar": null
                      },
                      "pagos_por_pagar_comision":{
                          "ver": null
                      },
                  },
                  "turismo": {
                      "otros": {
                      "ver": null,
                      "crear": null
                      }
                  },
                  "comercial":{
                      "cotizaciones": {
                          "ver": null,
                          "crear": null,
                          "editar": null
                      }
                  },
                  "administrativo": {
                      "centros_de_costo":{
                          "ver": null,
                          "crear": null,
                          "editar": null,
                          "bloquear_desbloquear": null,
                      },
                      "proveedores":{
                          "ver": null,
                          "crear": null,
                          "editar": null,
                          "bloquear_desbloquear": null,
                          "listado_vehiculos": null,
                          "listado_conductores": null,
                          "bloqueo_conductores": null,
                          "bloqueo_vehiculos": null,
                      },
                      "administracion_proveedores":{
                          "ver": null,
                          "crear": null
                      },
                      "contratos":{
                          "ver": null,
                          "crear": null,
                          "editar": null,
                          "renovar": null
                      },
                      "seguridad_social":{
                          "ver": null,
                          "crear": null
                      },
                      "fuec":{
                          "ver": null,
                          "crear": null,
                          "editar": null,
                          "descargar": null,
                          "rutas_fuec": null
                      },
                      "rutas_y_tarifas":{
                          "ver": null,
                          "editar": null
                      },
                      "ciudades":{
                          "ver": null,
                          "crear": null,
                          "editar": null
                      }
                  },
                  "talentohumano":{
                    "empleados": {
                      "ver": null,
                      "crear": null,
                      "editar": null,
                      "retirar": null
                    },
                    "prestamos":{
                      "ver": null,
                      "crear": null,
                      "gestionar": null
                    },
                    "vacaciones": {
                      "ver": null,
                      "crear": null,
                    },
                    "control_ingreso": {
                      "ver": null,
                      "crear": null,
                      "guardar_personal": null,
                      "historial": null
                    },
                    "control_ingreso_bog": {
                      "ver": null,
                      "crear": null,
                      "guardar_personal_bog": null,
                      "historial": null
                    }
                  },
                  "administracion": {
                      "usuarios": {
                          "ver": null
                      },
                      "clientes_particulares": {
                        "ver": null
                      },
                      "clientes_empresariales": {
                        "ver": null
                      },
                       "importar_pasajeros": {
                          "ver": null
                      },
                      "listado_pasajeros": {
                          "ver": null
                      },
                  },
                  "mobile": {
                      "servicios_programados_sintarifa": {
                          "ver": null
                      },
                      "servicios_programados_tarifado": {
                          "ver": null
                      },
                      "servicios_programados_pagados": {
                          "ver": null
                      },
                      "servicios_programados_facturacion": {
                          "ver": null
                      },
                      "servicios_programados": {
                          "ver": null
                      }
                  }
          };

          rellenarRoles(data, '#roles');
          rellenarRoles(data, '#edit_roles');

          $table = $('#example').DataTable({

              language: {
                  processing:     "Procesando...",
                  search:         "Buscar:",
                  lengthMenu:    "Mostrar _MENU_ Registros",
                  info:           "Mostrando _START_ de _END_ de _TOTAL_ Registros",
                  infoEmpty:      "Mostrando 0 de 0 de 0 Registros",
                  infoFiltered:   "(Filtrando de _MAX_ registros en total)",
                  infoPostFix:    "",
                  loadingRecords: "Cargando...",
                  zeroRecords:    "NINGUN REGISTRO ENCONTRADO",
                  emptyTable:     "NINGUN REGISTRO DISPONIBLE EN LA TABLA",
                  paginate: {
                      first:      "Primer",
                      previous:   "Antes",
                      next:       "Siguiente",
                      last:       "Ultimo"
                  },
                  aria: {
                      sortAscending:  ": activer pour trier la colonne par ordre croissant",
                      sortDescending: ": activer pour trier la colonne par ordre décroissant"
                  }
              },
              'bAutoWidth': false ,
              'aoColumns' : [
                  { 'sWidth': '2%' },
                  { 'sWidth': '3%' },
                  { 'sWidth': '9%' },
                  { 'sWidth': '5%' },
                  { 'sWidth': '25%' },
              ],
              processing: true,
              "bProcessing": true
          });

          $('.dataTables_length label select').addClass('form-control input-font');
          $('th.sorting_asc, th.sorting').css('border-bottom', '1px solid #D6D6D6');
          $('.dataTables_filter label input').addClass('form-control input-font');

        })

      </script>
  </body>
</html>
