<html>
<head>
    <meta charset="UTF-8">
    <title>Autonet | Proveedores</title>
    <link href="{{url('img/favicon.png')}}" rel="icon" type="image/x-icon" />
    <meta name="url" content="{{url('/')}}">
    @include('scripts.styles')
    <link rel="stylesheet" href="{{url('bootstrap/css/datatables.css')}}">
    <link rel="stylesheet" href="{{url('datatables/media/css/dataTables.bootstrap.css')}}">
</head>
<body>

@include('admin.menu')
<div class="col-xs-12">
    <div class="col-lg-8">
      <div class="row">
          @include('proveedores.menu_proveedores')
      </div>
    </div>
    <div class="col-lg-12">
      <div class="row">
        <h3 class="h_titulo">LISTADO DE PROVEEDORES</h3>
        <div class="col-lg-2 col-md-3 col-sm-2" style="margin-bottom: 5px;">
          <div class="row">
            <label>Tipo de afiliado</label>
            <select class="form-control input-font" id="tipo_afiliado">
              <option value="0">-</option>
              <option value="1">TODOS</option>
              <option value="5">BARRANQUILLA</option>
              <option value="4">BOGOTA</option>
              <option value="2">AFILIADOS INTERNO</option>
              <option value="3">AFILIADOS EXTERNO</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    @if(isset($proveedores))
      <table id="example" class="table table-bordered hover" cellspacing="0" width="100%">
          <thead>
            <tr>
                <th>Nit</th>
                <th>Razon Social</th>
                <th>Tipo de Servicio</th>
                <th>Direccion</th>
                <th>Telefono</th>
                <th></th>
            </tr>
          </thead>
          <tfoot>
          <tr>
            <th>Nit</th>
            <th>Razon Social</th>
            <th>Tipo de Servicio</th>
            <th>Direccion</th>
            <th>Telefono</th>
            <th></th>
          </tr>
          </tfoot>
          <tbody>
          @foreach($proveedores as $proveedor)
            <tr class="@if(intval($proveedor->inactivo)===1 && intval($proveedor->inactivo_total)===1){{'danger'}}@elseif(intval($proveedor->inactivo)===1){{'warning'}}@endif">
              <td>{{$proveedor->nit.'-'.$proveedor->codigoverificacion}}</td>
              <td>{{$proveedor->razonsocial.' '.$proveedor->tipoempresa}}</td>
              <td>{{$proveedor->tipo_servicio}}</td>
              <td>{{$proveedor->direccion}}</td>
              <td>{{$proveedor->telefono}}</td>
              <td id="{{$proveedor->id}}">
                <a class="btn btn-primary btn-list-table" href="proveedores/ver/{{strtolower($proveedor->razonsocial)}}">DETALLES</a>
                @if($proveedor->tipo_servicio==='TRANSPORTE TERRESTRE')
                    <a class="btn btn-warning btn-list-table" href="proveedores/conductores/{{strtolower($proveedor->id)}}">CONDUCTORES</a>
                    <a class="btn btn-success btn-list-table" href="proveedores/vehiculos/{{strtolower($proveedor->id)}}">VEHICULOS</a>
                @endif
              </td>
            </tr>
          @endforeach
          </tbody>
      </table>
    @endif
    @if(isset($permisos->administrativo->proveedores->crear))
        @if($permisos->administrativo->proveedores->crear==='on')
            <button type="button" class="btn btn-default btn-icon" data-toggle="modal" data-target=".mymodal">Agregar<i class="fa fa-plus icon-btn"></i></button>
        @else
            <button type="button" class="btn btn-default btn-icon" disabled>Agregar<i class="fa fa-plus icon-btn"></i></button>
        @endif
    @else
        <button type="button" class="btn btn-default btn-icon" disabled>Agregar<i class="fa fa-plus icon-btn"></i></button>
    @endif
    <a class="btn btn-primary btn-icon" onclick="goBack()">Volver<i class="fa fa-reply icon-btn"></i></a>
</div>

<div class="modal fade mymodal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-lg">
      <form id="formulario">
          <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                  <h4 class="modal-title">NUEVO PROVEEDOR</h4>
              </div>
              <div class="modal-body">
                  <div class="row">
                      <div class="col-xs-12">
                          <fieldset style="margin-bottom: 5px;"><legend class="margin_label">Datos Generales</legend>
                              <div class="row">
                                  <div class="col-xs-2">
                                    <label class="obligatorio" for="tipo_afiliado">Tipo Afiliado</label>
                                    <select name="tipo_afiliado" class="form-control input-font" id="tipo_afiliado">
                                      <option>-</option>
                                      <option value="1">AFILIADO INTERNO</option>
                                      <option value="2">AFILIADO EXTERNO</option>
                                    </select>
                                  </div>
                                  <div class="col-xs-2">
                                    <label class="obligatorio" for="nit">Nit o C.C</label>
                                    <input class="form-control input-font" type="text" id="nit">
                                  </div>
                                  <div class="col-xs-2">
                                      <label class="obligatorio" for="digitoverificacion">Digito verificacion</label>
                                      <select name="digitoverificacion" class="form-control input-font" id="digitoverificacion">
                                        <option>-</option>
                                        <option>0</option>
                                        <option>1</option>
                                        <option>2</option>
                                        <option>3</option>
                                        <option>4</option>
                                        <option>5</option>
                                        <option>6</option>
                                        <option>7</option>
                                        <option>8</option>
                                        <option>9</option>
                                      </select>
                                  </div>
                                  <div class="col-xs-4">
                                      <label class="obligatorio" for="razonsocial">Nombre completo o Razon social</label>
                                      <input class="form-control input-font" type="text" id="razonsocial">
                                  </div>
                                  <div class="col-xs-2">
                                      <label class="obligatorio" for="tipoempresa">Tipo de empresa</label>
                                      <select class="form-control input-font" name="tipoempresa" id="tipoempresa">
                                          <option>-</option>
                                          <option>S.A.S</option>
                                          <option>S.R.L.</option>
                                          <option>S.A</option>
                                          <option>L.T.D.A</option>
                                          <option>P.N</option>
                                      </select>
                                  </div>
                                  <div class="col-xs-3 representante">
                                      <label class="obligatorio" for="representante">Representante Legal</label>
                                      <input type="text" class="form-control input-font" id="representante">
                                  </div>
                                  <div class="col-xs-3" style="min-height: 0px;">
                                      <label class="obligatorio cedula" for="cedula">C.C</label>
                                      <input type="text" id="cedula" class="form-control input-font">
                                  </div>
                                  <div class="col-xs-3">
                                      <label for="direccion" class="obligatorio">Direccion</label>
                                      <input class="form-control input-font" id="direccion" type="text">
                                  </div>
                                  <div class="col-xs-3">
                                      <label class="obligatorio" for="departamento">Departamento</label>
                                      <select class="form-control input-font" name="departamento" id="departamento">
                                          <option>-</option>
                                          @if(isset($departamentos))
                                              @foreach($departamentos as $departamento)
                                                  <option value="{{$departamento->id}}">{{$departamento->departamento}}</option>
                                              @endforeach
                                          @endif
                                      </select>
                                  </div>
                                  <div class="col-xs-3">
                                      <label class="obligatorio" for="ciudad">Ciudad</label>
                                      <select class="form-control input-font" name="ciudad" id="ciudad" disabled>
                                          <option>-</option>
                                      </select>
                                  </div>
                                  <div class="col-xs-3">
                                      <label for="email">Email</label>
                                      <input type="email" class="form-control input-font" id="email">
                                  </div>
                                  <div class="col-xs-3">
                                      <label class="obligatorio" for="celular">Celular</label>
                                      <input type="text" class="form-control input-font" id="celular">
                                  </div>
                                  <div class="col-xs-3">
                                      <label for="telefono">Telefono</label>
                                      <input type="text" class="form-control input-font" id="telefono">
                                  </div>
                                  <div class="col-xs-2">
                                      <label class="obligatorio tipo_servicio_pn hidden" for="tipo_servicio_pn">Tipo de Servicio</label>
                                      <select class="form-control input-font hidden" id="tipo_servicio_pn" name="tipo_servicio_pn">
                                          <option>-</option>
                                          <option>TRANSPORTE TERRESTRE</option>
                                          <option>HOTEL</option>
                                          <option>OTROS</option>
                                      </select>
                                  </div>
                                  <div class="col-xs-3">
                                      <label class="obligatorio" for="localidad">Localidad</label>
                                      <select class="form-control input-font" name="localidad" id="localidad">
                                          <option>-</option>
                                          <option>Barranquilla</option>
                                          <option>Bogota</option>
                                      </select>
                                  </div>

                              </div>
                          </fieldset>
                      </div>

                      <div class="col-xs-12" id="container_contacto">
                          <fieldset style="margin-bottom: 5px;"><legend class="margin_label">Contacto</legend>
                              <div class="row">
                                  <div class="col-xs-4">
                                      <label class="obligatorio" for="contacto_nombrecompleto">Nombre Completo</label>
                                      <input class="form-control input-font" id="contacto_nombrecompleto" type="text">
                                  </div>
                                  <div class="col-xs-4">
                                      <label class="obligatorio" for="cargo">Cargo</label>
                                      <input type="text" class="form-control input-font" id="cargo">
                                  </div>
                                  <div class="col-xs-4">
                                      <label class="obligatorio" for="email_contacto">Email</label>
                                      <input type="text" class="form-control input-font" id="email_contacto">
                                  </div>
                                  <div class="col-xs-3">
                                      <label class="obligatorio" for="telefono_contacto">Telefono</label>
                                      <input type="text" class="form-control input-font" id="telefono_contacto">
                                  </div>
                                  <div class="col-xs-3">
                                      <label class="obligatorio" for="celular_contacto">Celular</label>
                                      <input type="text" class="form-control input-font" id="celular_contacto">
                                  </div>
                              </div>
                          </fieldset>
                      </div>

                      <div class="col-xs-12" id="container_informacion_tributaria">
                          <fieldset><legend class="margin_label">Informacion Tributaria</legend>
                              <div class="row">
                                  <div class="col-xs-3">
                                      <label class="obligatorio" for="actividad_economica">Actividad Economica</label>
                                      <input type="text" class="form-control input-font" id="actividad_economica">
                                  </div>
                                  <div class="col-xs-3">
                                      <label class="obligatorio" for="codigo_actividad">Codigo de Actividad</label>
                                      <input type="text" class="form-control input-font" id="codigo_actividad">
                                  </div>
                                  <div class="col-xs-3">
                                      <label class="obligatorio" for="codigo_ica">Codigo ICA</label>
                                      <input class="form-control input-font" type="text" id="codigo_ica">
                                  </div>
                                  <div class="col-xs-3">
                                      <label class="obligatorio" for="tarifa_ica">Tarifa ICA</label>
                                      <input type="text" class="form-control input-font" id="tarifa_ica">
                                  </div>
                                  <div class="col-xs-2">
                                      <label class="obligatorio" for="tipo_servicio">Tipo de Servicio</label>
                                      <select class="form-control input-font" id="tipo_servicio" name="tipo_servicio">
                                          <option>-</option>
                                          <option>TRANSPORTE TERRESTRE</option>
                                          <option>HOTEL</option>
                                          <option>OTROS</option>
                                      </select>
                                  </div>
                              </div>
                          </fieldset>
                      </div>
                      <div class="col-xs-12" id="container_informacion_tributaria">
                          <fieldset><legend class="margin_label">Informacion Bancaria</legend>
                              <div class="row" style="margin-bottom: 15px">
                                    <div style="float: left; background: orange; padding: 5px" class="unable">
                                      <span class="textoa" style="font-family: monospace; font-size: 18px; color: solid">PAGO A TERCERO</span> <input type="checkbox" id="select_all_pagos" class="select_all_pagos"> 
                                    </div> 
                              </div>
                              <div class="row principal">                                
                                  <div class="col-xs-3">
                                      <label class="obligatorio" for="tipo_cuenta" >Tipo de Cuenta</label>
                                      <select class="form-control input-font" name="tipo_cuenta" id="tipo_cuenta">
                                          <option >-</option>
                                          <option >AHORROS</option> 
                                          <option >CORRIENTE</option>
                                      </select>
                                  </div>
                                  <div class="col-xs-3">
                                      <label class="obligatorio" for="entidad_bancaria" >Entidad Bancaria</label>
                                      <select class="form-control input-font" name="entidad_bancaria" id="entidad_bancaria">
                                          <option >-</option>
                                          <option >BANCO DE BOGOTA</option> 
                                          <option >BANCO BBVA</option>
                                          <option >BANCOLOMBIA</option>
                                          <option >BANCO DAVIVIENDA</option>
                                          <option >BANCO POPULAR</option>
                                          <option >SCOTIABANK COLPATRIA S.A</option>
                                          <option >BANCOOMEVA</option>
                                          <option >BANCO FALABELLA S.A.</option>
                                          <option >ITAÚ</option>
                                          <option >BANCO CAJA SOCIAL</option>
                                          <option >BANCO DE OCCIDENTE</option>
                                          <option >BANCO AV VILLAS</option>
                                          <option >BANCO PICHINCHA</option>
                                          <option >HELM BANK</option>
                                          <option >SUDAMERIS</option>
                                          <option >HSBC</option>
                                      </select>
                                  </div>
                                  <div class="col-xs-3">
                                      <label class="obligatorio" for="numero_cuenta" >Número de Cuenta</label>
                                      <input class="form-control input-font" type="number" id="numero_cuenta" placeholder="NÚMERO DE CUENTA">
                                  </div>
                                  <div class="col-xs-3">
                                    <label class="obligatorio" for="certificacion_proveedor">Certificación Bancaria</label><br>  
                                    <input id="certificacion_proveedor" accept="application/pdf" class="certificacion_proveedor" type="file" value="Subir" name="certificacion_proveedor" >
                                  </div>
                              </div>
                              <div class="row hidden tercero">
                                <div class="col-lg-4 col-sm-12 col-xs-12">
                                  <label class="obligatorio" for="razonsocialt" >Razón Social T</label>
                                  <input class="form-control input-font" type="text" id="razonsocialt" placeholder="RAZÓN SOCIAL TERCERO">
                                </div>
                                <div class="col-lg-4 col-sm-12 col-xs-12">
                                  <label class="obligatorio" for="numero_documentot" >Número de Documento T</label>
                                  <input class="form-control input-font" type="text" id="numero_documentot" placeholder="NÚMERO DE DOCUMENTO">
                                </div>
                                <div class="col-lg-4 col-sm-12 col-xs-12">
                                    <label class="obligatorio" for="tipo_cuentat" >Tipo de Cuenta T</label>
                                    <select class="form-control input-font" name="tipo_cuentat" id="tipo_cuentat">
                                        <option >-</option>
                                        <option >AHORROS</option> 
                                        <option >CORRIENTE</option>
                                    </select>
                                </div>
                                <div class="col-lg-3 col-sm-12 col-xs-12">
                                    <label class="obligatorio" for="entidad_bancariat" >Entidad Bancaria T</label>
                                    <select class="form-control input-font" name="entidad_bancariat" id="entidad_bancariat">
                                        <option >-</option>
                                        <option >BANCO DE BOGOTA</option> 
                                        <option >BANCO BBVA</option>
                                        <option >BANCOLOMBIA</option>
                                        <option >BANCO DAVIVIENDA</option>
                                        <option >BANCO POPULAR</option>
                                        <option >SCOTIABANK COLPATRIA S.A</option>
                                        <option >BANCOOMEVA</option>
                                        <option >BANCO FALABELLA S.A.</option>
                                        <option >ITAÚ</option>
                                        <option >BANCO CAJA SOCIAL</option>
                                        <option >BANCO DE OCCIDENTE</option>
                                        <option >BANCO AV VILLAS</option>
                                        <option >BANCO PICHINCHA</option>
                                        <option >HELM BANK</option>
                                        <option >SUDAMERIS</option>
                                        <option >HSBC</option>
                                    </select>
                                </div>
                                <div class="col-lg-3 col-sm-12 col-xs-12">
                                    <label class="obligatorio" for="numero_cuentat" >Número de Cuenta T</label>
                                    <input class="form-control input-font" type="number" id="numero_cuentat" placeholder="NÚMERO DE CUENTA">
                                </div>
                                <div class="col-lg-3 col-sm-12 col-xs-12">
                                  <label class="obligatorio" for="certificacion_tercero">Certificación Bancaria T</label>
                                  <input id="certificacion_tercero" accept="application/pdf" class="certificacion_tercero" type="file" value="Subir" name="certificacion_tercero" class="perfil">
                                </div>
                                <div class="col-lg-2 col-sm-12 col-xs-12">
                                  <label class="obligatorio" for="poder_tercero">Adjuntar Poder T</label>
                                  <input id="poder_tercero" accept="application/pdf" class="poder_tercero" type="file" value="Subir" name="poder_tercero" class="perfil">
                                </div>
                              </div>
                          </fieldset>
                      </div>
                  </div>
              </div>
              <div class="modal-footer">
                  <button id="guardar" class="btn btn-primary btn-icon">Guardar<i class="fa fa-floppy-o icon-btn"></i></button>
                  <a data-dismiss="modal" id="limpiar" class="btn btn-danger btn-icon">Cerrar<i class="fa fa-times icon-btn"></i></a>
              </div>
          </div>
      </form>

    </div>
</div>

<div class="errores-modal bg-danger text-danger hidden model" style="top: 10%;">
    <i style="cursor: pointer; position: absolute;right: 5px;top: 4px;" class="fa fa-close cerrar"></i>
    <ul>
    </ul>
</div>

<div class="guardado bg-success text-success hidden model">
    <i style="cursor: pointer; position: absolute;right: 5px;top: 4px;" class="fa fa-close cerrar"></i>
    <ul style="margin: 0;padding: 0;">
    </ul>
</div>

@include('scripts.scripts')
<script src="{{url('datatables/media/js/jquery.datatables.js')}}"></script>
<script src="{{url('jquery/proveedores.js')}}"></script>
<script src="{{url('jquery/bootstrap.file-input.js')}}"></script>
<script type="text/javascript">
    function goBack(){
        window.history.back();
    }

    $('input[type=file]').bootstrapFileInput(); 
    $('.file-inputs').bootstrapFileInput();

</script>
</body>
</html>
