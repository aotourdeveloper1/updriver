<html xmlns="http://www.w3.org/1999/html">
<head>
	<title>Autonet | Centro de costo</title>
    <link href="{{url('img/favicon.png')}}" rel="icon" type="image/x-icon" />
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/css/bootstrap-select.min.css" />
		<meta name="url" content="{{url('/')}}">
</head>

@include('scripts.styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
<link rel="stylesheet" href="{{url('js/bootstrap/css/datatables.css')}}">
<link rel="stylesheet" href="{{url('js/datatables/media/css/dataTables.bootstrap.css')}}">
<link rel="stylesheet" href="{{url('js/bootstrap-datetimepicker\css\bootstrap-datetimepicker.min.css')}}">

<body>

@include('admin.menu')

<div class="col-lg-12">
    <h3 class="h_titulo">CENTROS DE COSTO</h3>
        @include('clientes.menu_cc')
		<div class="col-lg-2 col-md-3 col-sm-2" style="margin-bottom: 5px;">
      <div class="row">
        <label>Tipo de Cliente</label>
        <select class="form-control input-font" id="tipo_afiliado">
          <option value="0">-</option>
          <option value="1">TODOS</option>
          <option value="2">INTERNO</option>
          <option value="3">AFILIADOS EXTERNO</option>
        </select>
      </div>
    </div>
    @if(isset($centrosdecosto))
    <table id="example" class="table table-striped table-bordered hover" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th>Codigo</th>
            <th>Nit</th>
            <th>Razon Social</th>
            <th>Email</th>
            <th>Telefono</th>
            <th>Asesor Comercial</th>
            <th>Informacion</th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <th>Codigo</th>
            <th>Nit</th>
            <th>Razon Social</th>
            <th>Email</th>
            <th>Telefono</th>
            <th>Asesor Comercial</th>
            <th>Informacion</th>
        </tr>
        </tfoot>
        <tbody>
        @foreach($centrosdecosto as $centrodecosto)
        <tr class="@if($centrodecosto->inactivo==1){{'warning'}}@endif @if($centrodecosto->inactivo_total){{'danger'}}@endif">
            <td>
                @if(intval(strlen($centrodecosto->id))===1)
                {{'CL00'.$centrodecosto->id}}
                @elseif(intval(strlen($centrodecosto->id))===2)
                {{'CL0'.$centrodecosto->id}}
                @elseif(intval(strlen($centrodecosto->id))===3)
                {{'CL'.$centrodecosto->id}}
                @endif
            </td>
            <td>{{$centrodecosto->nit.'-'.$centrodecosto->codigoverificacion}}</td>
            <td>{{$centrodecosto->razonsocial.' '.$centrodecosto->tipoempresa}}</td>
            <td>{{$centrodecosto->email}}</td>
            <td>{{$centrodecosto->telefono}}</td>
            <td>{{$centrodecosto->nombre_completo}}</td>
            <td>

                <a data-id="{{$centrodecosto->id}}" data-toggle="modal" data-target=".mymodal1" class="detalles_centro btn btn-list-table btn-primary">Detalles</a>
                <!--<a data-id="{{$centrodecosto->id}}" data-toggle="modal" class="detalles_contacto btn btn-list-table btn-warning">Contactos</a>-->
                @if($centrodecosto->razonsocial==='PERSONA NATURAL')
                    <a href="{{url('centrodecosto/subcentrosdecosto/'.$centrodecosto->id)}}" class="btn btn-list-table btn-success">Clientes</a>
                @else
                    <a href="{{url('centrodecosto/subcentrosdecosto/'.$centrodecosto->id)}}" class="btn btn-list-table btn-success">Subcentros</a>
                @endif
                <a href="{{url('centrodecosto/tarifas/'.$centrodecosto->id)}}" class="btn btn-list-table btn-info">TARIFAS</a>
                <div class="btn-group dropdown">
                    <button style="padding: 7px 8px 6px 8px;" type="button" class="btn btn-warning dropdown-toggle btn-list-table" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        RESTRICCIONES <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            @if(isset($permisos->administrativo->centros_de_costo->bloquear_desbloquear))
                                @if($permisos->administrativo->centros_de_costo->bloquear_desbloquear==='on')
                                    <a href="#" data-option="0" data-id="{{$centrodecosto->id}}" data-inactivo="@if(isset($centrodecosto->inactivo)){{$centrodecosto->inactivo}}@else{{'0'}}@endif" class="bloqueo"><span class="input-font">@if($centrodecosto->inactivo===null or intval($centrodecosto->inactivo)===0){{'BLOQUEAR USO'}}@else{{'DESBLOQUEO USO'}}@endif </span> <i class="fa fa-warning"></i></a></li>
                                @else
                                    <a class="bloqueo disabled" style="cursor: not-allowed"><span class="input-font">@if($centrodecosto->inactivo===null or intval($centrodecosto->inactivo)===0){{'BLOQUEAR USO'}}@else{{'DESBLOQUEO USO'}}@endif </span> <i class="fa fa-warning"></i></a></li>
                                @endif
                            @else
                                <a class="bloqueo disabled" style="cursor: not-allowed"><span class="input-font">@if($centrodecosto->inactivo===null or intval($centrodecosto->inactivo)===0){{'BLOQUEAR USO'}}@else{{'DESBLOQUEO USO'}}@endif </span> <i class="fa fa-warning"></i></a></li>
                            @endif
                        <li>
                            @if(isset($permisos->administrativo->centros_de_costo->bloquear_desbloquear))
                                @if($permisos->administrativo->centros_de_costo->bloquear_desbloquear==='on')
                                    <a href="#" data-option="1" data-id="{{$centrodecosto->id}}" data-inactivo-total="@if(isset($centrodecosto->inactivo_total)){{$centrodecosto->inactivo_total}}@else{{'0'}}@endif" class="bloqueo"><span class="input-font">@if($centrodecosto->inactivo_total===null or intval($centrodecosto->inactivo_total)===0){{'BLOQUEO TOTAL'}}@else{{'DESBLOQUEO TOTAL'}}@endif</span> <i class="fa fa-close"></i></a>
                                @else
                                    <a class="bloqueo disabled" style="cursor: not-allowed"><span class="input-font">@if($centrodecosto->inactivo_total===null or intval($centrodecosto->inactivo_total)===0){{'BLOQUEO TOTAL'}}@else{{'DESBLOQUEO TOTAL'}}@endif</span> <i class="fa fa-close"></i></a>
                                @endif
                            @else
                                <a class="bloqueo disabled" style="cursor: not-allowed"><span class="input-font">@if($centrodecosto->inactivo_total===null or intval($centrodecosto->inactivo_total)===0){{'BLOQUEO TOTAL'}}@else{{'DESBLOQUEO TOTAL'}}@endif</span> <i class="fa fa-close"></i></a>
                            @endif
                        </li>
                    </ul>
                </div>
								<a data-id="{{$centrodecosto->id}}" class="btn btn-list-table btn-danger emails">Correos Cartera</a>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @endif
    @if(isset($permisos->administrativo->centros_de_costo->crear))
        @if($permisos->administrativo->centros_de_costo->crear==='on')
            <button type="button" class="btn btn-default btn-icon" data-toggle="modal" data-target=".mymodal">Agregar<i class="fa fa-plus icon-btn"></i></button>
        @else
            <button type="button" class="btn btn-default btn-icon disabled" disabled>Agregar<i class="fa fa-plus icon-btn"></i></button>
        @endif
    @else
        <button type="button" class="btn btn-default btn-icon disabled" disabled>Agregar<i class="fa fa-plus icon-btn"></i></button>
    @endif
    <a class="btn btn-primary btn-icon" href="{{url('/')}}">Volver<i class="fa fa-reply icon-btn"></i></a>
</div>

<div class="modal fade mymodal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-lg">
        <form id="formulario">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                  	<strong>NUEVO CENTRO DE COSTO</strong>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="row">
																<!--<div class="col-xs-2">
                                    <label class="obligatorio" for="tipo_cliente">Tipo de cliente</label>
                                    <select class="form-control input-font" id="tipo_cliente" name="tipo_cliente">
                                        <option value="0">TIPO CLIENTE</option>
                                        <option value="1" selected>INTERNO</option>
                                        <option value="2">AFILIADO EXTERNO</option>
                                    </select>
                                </div>-->
                                <div class="col-xs-3">
                                    <label class="obligatorio" for="nit">Nit.</label>
                                    <input class="form-control input-font" type="text" id="nit">
                                </div>
                                <div class="col-xs-3">
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
                                    <label class="obligatorio" for="razonsocial">Razon social</label>
                                    <input class="form-control input-font" type="text" id="razonsocial">
                                </div>
                                <div class="col-xs-2">
                                    <label class="obligatorio" for="tipoempresa">Tipo de empresa</label>
                                    <select class="form-control input-font" name="tipoempresa" id="tipoempresa">
                                        <option>-</option>
                                        <option>P.N</option>
                                        <option>S.A.S</option>
                                        <option>S.A</option>
                                        <option>S.C.A</option>
                                        <option>S.C</option>
                                        <option>L.T.D.A</option>
                                        <option>OTROS</option>
                                    </select>
                                </div>
                                <div class="col-xs-3">
                                    <label class="obligatorio" for="direccion">Direccion</label>
                                    <input class="form-control input-font" type="text" id="direccion">
                                </div>
                                <div class="col-xs-3">
                                    <label class="obligatorio" for="departamento">Departamento</label>
                                    <select class="form-control input-font" id="departamento">
                                        <option>-</option>
                                        @foreach($departamentos as $departamento)
                                            <option value="{{$departamento->id}}">{{$departamento->departamento}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-xs-2">
                                    <label class="obligatorio" for="ciudad">Ciudad o Municipio</label>
                                    <select disabled class="form-control input-font" id="ciudad">
                                        <option>-</option>
                                    </select>
                                </div>
                                <div class="col-xs-3">
                                    <label class="obligatorio" for="email">Email</label>
                                    <input class="form-control input-font" type="text" id="email" autocomplete="off">
                                </div>
                                <div class="col-xs-2">
                                    <label class="obligatorio" for="telefono">Telefono</label>
                                    <input class="form-control input-font" type="text" id="telefono">
                                </div>
                                <!--<div class="col-xs-3">
                                    <label class="obligatorio" for="telefono">Asesor Comercial</label>
                                    <select class="form-control input-font" id="asesorcomercial">
                                        <option value="0">-</option>
                                        @foreach ($asesor_comercial as $key => $value)
                                            <option value="{{$value->id}}">{{$value->nombre_completo}}</option>
                                        @endforeach
                                    </select>
                                </div>-->
                                <div class="col-xs-2">
                                    <label class="obligatorio">Credito</label>
                                    <select id="credito" class="form-control input-font">
                                        <option value="0">-</option>
                                        <option value="1">SI</option>
                                        <option value="2">NO</option>
                                    </select>
                                </div>
                                <div class="col-xs-2 hidden plazo_pago">
                                    <label class="obligatorio">Plazo de Pago</label>
                                    <input type="text" class="form-control input-font" id="plazo_pago">
                                </div>
                                <div class="col-xs-2">
                                      <label class="obligatorio" for="localidad">Localidad</label>
                                      <select class="form-control input-font" name="localidad" id="localidad">
                                          <option>-</option>
                                          <option>Barranquilla</option>
                                          <option>Bogota</option>
                                          <option>Provisional</option>
                                      </select>
                                  </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-3">
                                  <label class="obligatorio" for="localidad">Tipo Tarifa Cliente</label>
                                  <select class="form-control input-font" name="tipo_tarifa" id="tipo_tarifa">
                                      <option value="0">-</option>
                                      <option value="1">Tarifa Aotour</option>
                                      <option value="2">Tarifa Negociada</option>
                                  </select>
                                </div>
                                <div class="col-xs-3">
                                  <label class="obligatorio" for="localidad">Tipo Tarifa Proveedor</label>
                                  <select class="form-control input-font" name="tipo_tarifa_proveedor" id="tipo_tarifa_proveedor">
                                      <option value="0">-</option>
                                      <option value="1">Tarifa Aotour</option>
                                      <option value="2">Tarifa Negociada</option>
                                  </select>
                                </div>
                                <div class="col-xs-2">
                                  <label class="obligatorio" for="recargo_nocturno">Recargo Nocturno</label>
                                  <select class="form-control input-font" name="recargo_nocturno" id="recargo_nocturno">
                                      <option value="0">-</option>
                                      <option value="1">Si</option>
                                      <option value="2">No</option>
                                  </select>
                                </div>
                                <div class="col-xs-2">
                                    <label class="obligatorio" for="localidad">Desde</label>
                                    <div class="input-group">
                                        <div class="input-group date" id="datetimepicker3">
                                            <input type="text" class="form-control input-font" id="desde" autocomplete="off" placeholder="Hora inicio">
                                            <span class="input-group-addon">
                                                <span class="fa fa-calendar">
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-2">
                                    <label class="obligatorio" for="localidad">Hasta</label>
                                    <div class="input-group">
                                        <div class="input-group date" id="datetimepicker4">
                                            <input type="text" class="form-control input-font" id="hasta" autocomplete="off" placeholder="Hora fin">
                                            <span class="input-group-addon">
                                                <span class="fa fa-calendar">
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>


                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-lg-12">
                            <h4>Datos de Contacto</h4>
                            <div class="row">
                                <div class="col-xs-4">
                                    <label class="obligatorio" for="razonsocial">Nombres del Contacto</label>
                                    <input class="form-control input-font" type="text" id="nombres">
                                </div>
                                <div class="col-xs-4">
                                    <label class="obligatorio" for="razonsocial">Apellidos del Contacto</label>
                                    <input class="form-control input-font" type="text" id="apellidos">
                                </div>
                                <div class="col-xs-3">
                                    <label class="obligatorio" for="correo">Correo del Contacto</label>
                                    <input class="form-control input-font" type="text" id="correo">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-4">
                                  <label class="obligatorio">Ciudad</label><br>
                                  <select class="selectpicker" id="ciudadSiigo" data-show-subtext="true" data-live-search="true">
                                    <option data-subtext="Seleccionar una" id="0">Ciudades</option>
                                    <option data-subtext="Atlántico" state-name="Atlántico" id="08001" data-state="08" country-code="Co" country-name="Colombia" state-code="08">Barranquilla</option>
                                    <option data-subtext="Antioquia" id="05001" data-state="05">Medellín</option>
                                    <option data-subtext="Bogotá D.C" id="11001" data-state="11">Bogotá</option>
                                    <option data-subtext="Valle del Cauca" id="76001" data-state="76">Cali</option>
                                  </select>
                                </div>
                                <div class="col-xs-3">
                                    <label class="obligatorio" for="contribuyente">Responsabilidades fiscales</label>
                                    <select class="form-control input-font" name="contribuyente" id="contribuyente">
                                        <option value="0">-</option>
                                        <option value="R-99-PN" >No Aplica - Otros*</option>
                                        <option value="O-13" >Gran contribuyente</option>
                                        <option value="O-15" >Autorretenedor</option>
                                        <option value="O-23" >Agente de retención IVA</option>
                                        <option value="O-47" >Régimen simple de tributación</option>
                                    </select>
                                </div>
                                <div class="col-xs-1">
                                    <label class="obligatorio" for="contribuyente">RUT</label>
                                    <input id="rut" accept="application/pdf" class="rut" type="file" value="Subir" name="rut" >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <!--@if(isset($permisos->administrativo->centros_de_costo->crear))
                        @if($permisos->administrativo->centros_de_costo->crear==='on')
                            <button type="submit" id="guardar" class="btn btn-primary btn-icon input-font">Guardar<i class="fa fa-floppy-o icon-btn"></i></button>
                        @else
                            <button type="submit" class="btn btn-primary btn-icon input-font disabled" disabled>Guardar<i class="fa fa-floppy-o icon-btn"></i></button>
                        @endif
                    @else
                        <button type="submit" class="btn btn-primary btn-icon input-font disabled" disabled>Guardar<i class="fa fa-floppy-o icon-btn"></i></button>
                    @endif-->

                    <button id="guardar2" class="btn btn-success btn-icon input-font">Guardar<i class="fa fa-floppy-o icon-btn"></i></button>

                    <a data-dismiss="modal" id="limpiar" class="btn btn-danger btn-icon input-font">Cerrar<i class="fa fa-times icon-btn"></i></a>
                </div>
            </div><!-- /.modal-content -->
        </form>
    </div><!-- /.modal-dialog -->
</div>
<div class="modal fade mymodal1" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-lg">
        <form id="formulario_actualizar">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                    	<strong>EDITAR CENTRO DE COSTO</strong>
                </div>
                <div class="modal-body">
                    <div class="row edicion">
                        <div class="col-xs-12">
                            <div class="row">
																<div class="col-xs-2">
                                    <label class="obligatorio" for="tipo_cliente_centrodecosto">Tipo de cliente</label>
                                    <select class="form-control input-font" id="tipo_cliente_centrodecosto" name="tipo_cliente_centrodecosto">
                                        <option value="0">TIPO CLIENTE</option>
                                        <option value="1">INTERNO</option>
                                        <option value="2">AFILIADO EXTERNO</option>
                                    </select>
                                </div>
                                <div class="col-xs-2">
                                    <label>Nit:</label>
                                    <input class="form-control input-font" disabled id="nit_centrodecosto" value="">
                                </div>
                                <div class="col-xs-2">
                                    <label>Digito Verificacion:</label>
                                    <select name="digitoverificacion" class="form-control input-font" id="digitoverificacion_centrodecosto">
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

                                <div class="col-xs-6">
                                    <label>Razon Social:</label>
                                    <input class="form-control input-font" disabled id="razonsocial_centrodecosto">
                                </div>
                                <div class="col-xs-2">
                                    <label>Tipo Empresa:</label>
                                    <select class="form-control input-font" name="tipoempresa" id="tipoempresa_centrodecosto">
                                        <option>-</option>
                                        <option>P.N</option>
                                        <option>S.A.S</option>
                                        <option>S.A</option>
                                        <option>S.C.A</option>
                                        <option>S.C</option>
                                        <option>L.T.D.A</option>
                                        <option>OTROS</option>
                                    </select>
                                </div>
                                <div class="col-xs-3">
                                    <label>Direccion:</label>
                                    <input class="form-control input-font" disabled id="direccion_centrodecosto">
                                </div>
                                <div class="col-xs-3">
                                    <label>Departamento:</label>
                                    <select class="form-control input-font" disabled id="departamento_centrodecosto">
                                        <option>-</option>
                                    </select>
                                </div>
                                <div class="col-xs-3">
                                    <label>Ciudad:</label>
                                    <select class="form-control input-font" disabled id="ciudad_centrodecosto">
                                        <option>-</option>
                                    </select>
                                </div>
                                <div class="col-xs-3">
                                    <label>Email:</label>
                                    <input class="form-control input-font" disabled id="email_centrodecosto">
                                </div>
                                <div class="col-xs-3">
                                    <label>Telefono:</label>
                                    <input class="form-control input-font" disabled id="telefono_centrodecosto">
                                </div>
                                <div class="col-xs-3">
                                    <label>Asesor Comercial:</label>
                                    <select class="form-control input-font" disabled id="asesor_centrodecosto">
                                        <option value="0">-</option>
                                        <?php foreach ($asesor_comercial as $key => $value): ?>
                                            <?php echo '<option value="'.$value->id.'">'.$value->nombre_completo.'</option>'; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-xs-3">
                                    <label>Credito:</label>
                                    <select class="form-control input-font" disabled id="credito_centrodecosto">
                                        <option value="0">-</option>
                                        <option value="1">SI</option>
                                        <option value="2">NO</option>
                                    </select>
                                </div>
                                <div class="col-xs-2 plazo_pago_centrodecosto hidden">
                                    <label>Plazo de Pago</label>
                                    <input type="text" class="form-control input-font" id="plazo_pago_centrodecosto">
                                </div>
                                <div class="col-xs-2">
                                      <label class="obligatorio" for="localidad">Localidad</label>
                                      <select class="form-control input-font" name="localidad" id="localidad_centrodecosto">
                                          <option>-</option>
                                          <option>BARRANQUILLA</option>
                                          <option>BOGOTA</option>
                                          <option>PROVISIONAL</option>
                                      </select>
                                  </div>
                                  <div class="col-xs-2">
                                      <label class="obligatorio" for="localidad">Tipo Tarifa Cliente</label>
                                      <select class="form-control input-font" name="tipo_tarifa" id="tipo_tarifa_cliente_editar">
                                          <option value="0">-</option>
                                          <option value="1">Tarifa Aotour</option>
                                          <option value="2">Tarifa Negociada</option>
                                      </select>
                                  </div>

                                  <div class="col-xs-2">
                                      <label class="obligatorio" for="localidad">Tipo Tarifa Proveedor</label>
                                      <select class="form-control input-font" name="tipo_tarifa" id="tipo_tarifa_proveedor_editar">
                                          <option value="0">-</option>
                                          <option value="1">Tarifa Aotour</option>
                                          <option value="2">Tarifa Negociada</option>
                                      </select>
                                  </div>

                                  <div class="col-xs-2">
                                  <label class="obligatorio" for="recargo_nocturno">Recargo Nocturno</label>
                                  <select class="form-control input-font" name="recargo_nocturno" id="recargo_nocturno_editar">
                                      <option value="0">-</option>
                                      <option value="1">Si</option>
                                      <option value="2">No</option>
                                  </select>
                                </div>
                                <div class="col-xs-2">
                                    <label class="obligatorio" for="localidad">Desde</label>
                                    <div class="input-group">
                                        <div class="input-group date" id="datetimepicker5">
                                            <input type="text" class="form-control input-font" id="desde_editar" autocomplete="off" placeholder="Hora inicio">
                                            <span class="input-group-addon">
                                                <span class="fa fa-calendar">
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-2">
                                    <label class="obligatorio" for="localidad">Hasta</label>
                                    <div class="input-group">
                                        <div class="input-group date" id="datetimepicker6">
                                            <input type="text" class="form-control input-font" id="hasta_editar" autocomplete="off" placeholder="Hora fin">
                                            <span class="input-group-addon">
                                                <span class="fa fa-calendar">
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </div>


                            </div>

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    @if(isset($permisos->administrativo->centros_de_costo->editar))
                        @if($permisos->administrativo->centros_de_costo->editar==='on')
                            <a id="editar" class="btn btn-success btn-icon input-font">Editar<i class="fa fa-pencil icon-btn"></i></a>
                            <button id="actualizar" class="btn btn-primary btn-icon input-font">Guardar<i class="fa fa-floppy-o icon-btn"></i></button>
                        @else
                            <a class="btn btn-success btn-icon input-font disabled">Editar<i class="fa fa-pencil icon-btn"></i></a>
                            <button class="btn btn-primary btn-icon input-font disabled" disabled>Guardar<i class="fa fa-floppy-o icon-btn"></i></button>
                        @endif
                    @else
                        <a class="btn btn-success btn-icon input-font disabled">Editar<i class="fa fa-pencil icon-btn"></i></a>
                        <button id="actualizar" class="btn btn-primary btn-icon input-font disabled" disabled>Guardar<i class="fa fa-floppy-o icon-btn"></i></button>
                    @endif

                    <a data-dismiss="modal" class="btn btn-danger btn-icon input-font">Cerrar<i class="fa fa-times icon-btn"></i></a>
                </div>
            </div><!-- /.modal-content -->
        </form>
    </div><!-- /.modal-dialog -->
</div>
<div class="modal fade mymodal2" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-lg">
        <form id="formulario_actualizar">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                    <h4 class="modal-title">CONTACTOS</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12 datos_nuevos">
                            <div class="row">
                                <div class="col-xs-3">
                                    <label class="obligatorio">Nombre</label>
                                    <input type="text" class="form-control nombre input-font">
                                </div>
                                <div class="col-xs-2">
                                    <label class="obligatorio">Cargo</label>
                                    <input type="text" class="form-control cargo input-font">
                                </div>
                                <div class="col-xs-2">
                                    <label class="obligatorio">Email</label>
                                    <input type="text" class="form-control email input-font">
                                </div>
                                <div class="col-xs-2">
                                    <label class="obligatorio">Celular</label>
                                    <input type="text" class="form-control celular input-font">
                                </div>
                                <div class="col-xs-2">
                                    <label class="obligatorio">Telefono</label>
                                    <input type="text" class="form-control telefono input-font">
                                </div>
                                <div class="col-xs-1">
                                    <button style="margin-top:30px" id="nuevo_contacto" class="btn btn-primary"><i class="fa fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="lista">

                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <a data-dismiss="modal" class="btn btn-danger btn-icon">Cerrar<i class="fa fa-times icon-btn"></i></a>
                </div>
            </div><!-- /.modal-content -->
        </form>
    </div><!-- /.modal-dialog -->
</div>

<div class="modal fade" tabindex="-1" role="dialog" id='modal_emails' style="overflow: scroll;">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" style="text-align: center;" id="name"><b id="title" class="parpadea">Actualización de Correos - Cartera</b></h4>
        </div>
        <div class="modal-body">
          <form id="formulario">
            <div class="row">
                <div class="col-lg-12" style="margin-top: 10px">
                    <div class="panel panel-default">
                        <div class="panel-heading" style="background-color: gray; color: white; text-align: center">Estás agregando los correos de cartera de este cliente, al que llegarán las alertas de notificación sobre facturas vencidas.</div>
                        <div class="panel-body">

                          <div class="row">
                            <div class="col-lg-12">
															<center>
																<a id="agregar_descuento" style="margin-right: 3px;" class="btn btn-primary btn-icon margin">AGREGAR CORREO<i class="fa fa-plus icon-btn"></i></a>
																<a id="eliminar_descuento" class="btn btn-warning btn-icon">ELIMINAR CORREO<i class="fa fa-close icon-btn"></i></a>
															</center>

															<table class="table table-bordered hover descuentos hidden" style="margin-top: 15px; margin-bottom:15px; box-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);">
															</table>

                            </div>
                          </div>

                        </div>
                    </div>

                </div>
            </div>

            <p style="color: red; float: right">Al presionar eliminar, se borrará el último correo</p>

            <a id="enviar_tarifas_seleccionadas" style="float: right; margin-bottom: 25px; margin-top: 15px" type="text" class="btn btn-primary btn-icon hidden">Enviar Tarifas Seleccionadas<i class="fa fa-send icon-btn"></i></a>

            <a id="esconder_tarifas" style="float: left; margin-bottom: 25px; margin-top: 15px" type="text" class="btn btn-warning btn-icon hidden">Quiero Adjuntar Soportes<i class="fa fa-paperclip icon-btn"></i></a>

            <hr>
            <button id="guardarcambios" style="width: 100%" type="button" class="btn btn-success btn-icon">Guardar Cambios<i class="fa fa-save icon-btn"></i></button>
          </form>
        </div>
        <div class="modal-footer">
          <a style="float: right; margin-right: 6px; margin-left: 5px" data-dismiss="modal" class="btn btn-danger btn-icon">Cancelar<i class="fa fa-times icon-btn"></i></a>
          <!--<a style="float: right; margin-right: 6px; margin-left: 5px" class="btn btn-info btn-icon volver">Regresar<i class="fa fa-arrow-left icon-btn"></i></a>-->
        </div>
    </div>
  </div>
</div>

<div class="errores-modal bg-danger text-danger hidden model">
    <i style="cursor: pointer; position: absolute;right: 5px;top: 4px;" class="fa fa-close cerrar"></i>
    <ul>
    </ul>
</div>

<div class="guardado bg-success text-success hidden model">
    <i style="cursor: pointer; position: absolute;right: 5px;top: 4px;" class="fa fa-close cerrar"></i>
    <ul style="margin: 0;padding: 0;">
    </ul>
</div>

</body>

@include('scripts.scripts')
<script src="{{url('js/datatables/media/js/jquery.datatables.js')}}"></script>
<script src="{{url('js/jquery/clientes.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/js/bootstrap-select.min.js"></script>
<script src="{{url('js/bootstrap-datetimepicker\js\moment-with-locales.js')}}"></script>
    <script src="{{url('js/bootstrap-datetimepicker\js\bootstrap-datetimepicker.js')}}"></script>
<script src="{{url('jquery/bootstrap.file-input.js')}}"></script>

<script type="text/javascript">

    //GUARDAR NUEVO CENTRO DE COSTO
    $('#guardar2').click(function(e){

        var ciudadSiigo = $('#ciudadSiigo option:selected').html();
        var state_name = $('#ciudadSiigo option:selected').attr('state-name');
        var state_code = $('#ciudadSiigo option:selected').attr('state-code');
        var country_code = $('#ciudadSiigo option:selected').attr('country-code');
        var country_name = $('#ciudadSiigo option:selected').attr('country-name');
        var id_ciudad = $('#ciudadSiigo option:selected').attr('id');

        var nombres = $('#nombres').val().trim();
        var apellidos = $('#apellidos').val().trim();
        var correo = $('#correo').val().trim();

        //Antiguos
        var tipo_cliente = $('#tipo_cliente').val();
        var nit = $('#nit').val().trim();
        var digito = $('#digitoverificacion option:selected').html().trim().toUpperCase();
        var razonsocial = $('#razonsocial').val().trim().toUpperCase();
        var direccion = $('#direccion').val().trim().toUpperCase();
        var ciudad = $('#ciudad option:selected').html().trim().toUpperCase();
        var email = $('#email').val().trim().toUpperCase();
        var telefono = $('#telefono').val().trim();

        var localidad = $('#localidad option:selected').html().trim().toUpperCase();
        var tipo_tarifa = $('#tipo_tarifa').val();
        var recargo_nocturno = $('#recargo_nocturno').val();
        var desde = $('#desde').val();
        var hasta = $('#hasta').val();
        var tipo_tarifa_proveedor = $('#tipo_tarifa_proveedor').val();
        //alert(recargo_nocturno)
        var credito = $('#credito').val().trim();
        var tipoempresa = $('#tipoempresa').val().trim();


        var contribuyente = $('#contribuyente option:selected').val();
        var rut = $('#rut').val();

        //console.log(digito)

        e.preventDefault();
        if(!($('.errores-modal').hasClass('hidden'))){
            $('.errores-modal').addClass('hidden');
        }



        if(recargo_nocturno==='0' || (recargo_nocturno!='0' && (desde==='' || hasta==='') ) ||tipo_cliente==='0' || nit==='' || digito==='-' || razonsocial==='' || tipoempresa==='-' || direccion==='' || email==='' || localidad==='-' || telefono==='' || credito==='0' || contribuyente==='0' || ciudadSiigo==='Ciudades' || nombres==='' || apellidos==='' || correo==='' || rut===''){

            var text = '';

            if(tipo_cliente==='0' || nit==='' || digito==='-' || razonsocial==='' || tipoempresa==='-' || direccion==='' || email==='' || localidad==='-' || telefono==='' || credito==='0'){
                var text = '<b>Datos del Cliente:<b> <br>';
            }

            if(tipo_cliente==='0'){
                text += "<li>Tipo de Cliente<br>";
            }

            if(nit===''){
                text += "<li>Nit<br>";
            }

            if(digito==='-'){
                text += "<li>Dígito de Verificación<br>";
            }

            if(razonsocial===''){
                text += "<li>Razón Social<br>";
            }

            if(tipoempresa==='-'){
                text += "<li>Tipo de Empresa<br>";
            }

            if(direccion===''){
                text += "<li>Dirección<br>";
            }

            if(email===''){
                text += "<li>Email<br>";
            }

            if(telefono===''){
                text += "<li>Teléfono<br>";
            }

            if(credito==='0'){
                text += "<li>Crédito<br>";
            }

            if(localidad==='-'){
                text += "<li>Localidad<br>";
            }

            if(recargo_nocturno==='0'){
                text += "<li>Recargo Nocturno<br>";
            }

						alert(recargo_nocturno)
            if( ( (recargo_nocturno!='0' && recargo_nocturno!='2' ) && (desde==='' || hasta==='') ) ){
                if( desde==='' ) {
                    text += "<li>Hora Inicio recargo<br>";
                }
                if( hasta==='' ) {
                    text += "<li>Hora Fin recargo<br><br>";
                }
            }

            //Contacto

            if(contribuyente==='0' || ciudadSiigo==='Ciudades' || nombres==='' || apellidos==='' || correo==='' || rut===''){
                text += '<b>Datos de contacto:<b> <br>';
            }

            if(nombres===''){
                text += "<li>Nombres del Contacto<br>";
            }

            if(apellidos===''){
                text += "<li>Apellidos del Contacto<br>";
            }

            if(correo===''){
                text += "<li>Correo del Contacto<br>";
            }

            if(ciudadSiigo==='Ciudades'){
                text += "<li>Seleccionar Ciudad<br>";
            }

            if(contribuyente==='0'){
                text += "<li>Seleccionar Responsibilidad Fiscal<br>";
            }

            if(rut===''){
                text += "<li>RUT PDF<br>";
            }

            $.confirm({
                title: 'Campos Vacíos!',
                content: 'Los siguientes campos están vacíos...<br><br>'+text,
                buttons: {
                    confirm: {
                        text: 'Ok',
                        btnClass: 'btn-success',
                        keys: ['enter', 'shift'],
                        action: function(){

                        }

                    }
                }
            });

        }else{

            $('.fa-floppy-o').removeClass('fa-floppy-o').addClass('fa-spinner fa-spin');

            formData = new FormData($('#formulario')[0]);
            formData.append('tipo_cliente',1);
            formData.append('nit',$('#nit').val().trim());
            formData.append('digitoverificacion', digito);
            formData.append('razonsocial',$('#razonsocial').val().trim().toUpperCase());
            formData.append('direccion',$('#direccion').val().trim().toUpperCase());
            formData.append('tipoempresa', tipoempresa);
            formData.append('ciudad',$('#ciudad option:selected').html().trim().toUpperCase());
            formData.append('departamento',$('#departamento option:selected').html().trim().toUpperCase());
            formData.append('email',$('#email').val().trim().toUpperCase());
            formData.append('telefono',$('#telefono').val().trim());
            formData.append('asesorcomercial',0);
            formData.append('credito',$('#credito').val().trim());
            formData.append('plazo_pago',$('#plazo_pago').val());
            formData.append('localidad',$('#localidad option:selected').html().trim().toUpperCase());
            formData.append('tipo_tarifa',$('#tipo_tarifa').val());
            formData.append('recargo_nocturno',$('#recargo_nocturno').val());
            formData.append('desde',desde);
            formData.append('hasta',hasta);
            formData.append('tipo_tarifa_proveedor',$('#tipo_tarifa_proveedor').val());
            formData.append('tipo_cliente2',$('#tipo_cliente2').val());

            formData.append('contribuyente', contribuyente);

            formData.append('ciudadSiigo', ciudadSiigo);
            formData.append('state_name', state_name);
            formData.append('state_code', state_code);
            formData.append('country_code', country_code);
            formData.append('country_name', country_name);
            formData.append('id_ciudad', id_ciudad);

            formData.append('first_name_siigo', nombres);
            formData.append('last_name_siigo', apellidos);
            formData.append('email_siigo', correo);

            formData.append('rut', $('#rut').val());

            $.ajax({
                type: "post",
                url: "centrodecosto/nuevocentro",
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {

                    if(data.mensaje===false){

                        $('.fa-spinner fa-spin').removeClass('fa-floppy-o').addClass('fa-floppy-o');

                        if(!($('.guardado').hasClass('hidden'))){
                            $('.guardado').addClass('hidden');
                        }

                        $('.errores-modal ul li').remove();
                        for(i in data.errores){
                            var string = JSON.stringify(data.errores[i]);
                            var clean = string.split('"').join('')
                                .split('.').join('<br>')
                                .split(',').join('<li>')
                                .split('[').join('')
                                .split(']').join('');

                            $('.errores-modal').removeClass('hidden');
                            $('.errores-modal ul').append('<li>'+clean+'</li>');
                        }
                    }else if(data.mensaje===true){

                        if(!($('.errores-modal').hasClass('hidden'))){
                            $('.errores-modal').addClass('hidden');
                        }
                        $('.guardado ul li').remove();
                        $('.guardado').removeClass('hidden');
                        $('.guardado ul').append('<li style="list-style: none;">'+data.respuesta+'</li>');
                        $(':input').val('');
                        $('select').val('-');
                        $('#ciudad').attr('disabled','disabled');
                        location.href = "centrodecosto";

                    }else if(data.respuesta==='relogin'){
                        location.reload();
                    }else{
                        $('.errores-modal ul li').remove();
                        $('.errores-modal').addClass('hidden');
                    }
                }
            });
        }

    });

    $('#datetimepicker3, #datetimepicker4').datetimepicker({
        format: 'HH:mm',
        locale: 'es',
        icons: {
            time: 'glyphicon glyphicon-time',
            date: 'glyphicon glyphicon-calendar',
            up: 'glyphicon glyphicon-chevron-up',
            down: 'glyphicon glyphicon-chevron-down',
            previous: 'glyphicon glyphicon-chevron-left',
            next: 'glyphicon glyphicon-chevron-right',
            today: 'glyphicon glyphicon-screenshot',
            clear: 'glyphicon glyphicon-trash',
            close: 'glyphicon glyphicon-remove'
        }
    });

		$('.emails').click(function() {

			var id = $(this).attr('data-id');
			//alert('emails : '+id)

			$.ajax({
        url: 'centrodecosto/consultarmails',
        method: 'post',
        data: {id: id}
      }).done(function(data){

        if(data.respuesta==true){

					$('.descuentos').html('');
					$elemento = '';
					for(var i in data.cliente){
						//console.log(data.cliente[i].mail)
						$elemento += '<tr>'+
			                    '<td>'+
			                        '<input value="'+data.cliente[i].mail+'" class="form-control input-font email" style="text-transform: uppercase;" placeholder="DIGITE EL CORREO"></input>'+
			                    '</td>'+
			                  '</tr>';
					}
					$('.descuentos').removeClass('hidden').append($elemento);
					$('#guardarcambios').attr('data-id',id);
					$('#modal_emails').modal('show');

        }else if(data.respuesta==false){

        }

      });

		});

		$('#agregar_descuento').click(function(event){
      event.preventDefault();
      $elemento = '<tr>'+
                    '<td>'+
                        '<input rows="2" class="form-control input-font email" style="text-transform: uppercase;" placeholder="DIGITE EL CORREO"></input>'+
                    '</td>'+
                  '</tr>';
      $('.descuentos').removeClass('hidden').append($elemento);
    });

    $('#eliminar_descuento').click(function(event){
      $('.descuentos tbody tr').last().remove();
    });

		$('#guardarcambios').click(function() {

			var sw = 0;
			var sw2 = 0;
			var mails = [];
			var id = $(this).attr('data-id');

			$('.descuentos tbody tr').each(function(index){

        $(this).children("td").each(function (index2){

            switch (index2){
                case 0:
										sw2 = 1;
                    var email = $(this).find('.email').val();
										if(email!=''){
											mails.push(email);
										}else{
											sw = 1;
										}
                break;
            }
        });

      });

			if(sw2===0){

				$.confirm({
					title: '¡Atención!',
					content: 'No has agregado nigún correo.',
					buttons: {
							confirm: {
									text: 'Ok',
									btnClass: 'btn-danger',
									keys: ['enter', 'shift'],
									action: function(){

									}

							}
					}

				});

			}else if(sw===1){

				$.confirm({
					title: '¡Atención!',
					content: 'Hay campos vacíos.',
					buttons: {
							confirm: {
									text: 'Ok',
									btnClass: 'btn-danger',
									keys: ['enter', 'shift'],
									action: function(){

									}

							}
					}

				});

			}else{
				//alert('datos completos')
				$.ajax({
	        url: 'centrodecosto/guardarcambios',
	        method: 'post',
	        data: {id: id, mails: mails}
	      }).done(function(data){

	        if(data.respuesta==true){

						$.confirm({
		          title: 'Realizado!',
		          content: '¡Actualización exitosa!',
		          buttons: {
		              confirm: {
		                  text: 'Ok',
		                  btnClass: 'btn-success',
		                  keys: ['enter', 'shift'],
		                  action: function(){
												location.reload();
		                  }

		              }
		          }

		        });

	        }else if(data.respuesta==false){

	        }

	      });
			}
			/*$('.descuentos tbody tr').each(function(index) {
        if ($(this).find('.email').val()==='') {
          sw = 1;
        }else {
					alert($(this).find('.email').val())
					mails.push( $(this).find('.email').val() );
        }

      });*/
			//alert(sw)
			//alert(mails)
		});

</script>
<script>

  $('input[type=file]').bootstrapFileInput();
  $('.file-inputs').bootstrapFileInput();

</script>

</html>
