<html>
<head>
    <meta charset="UTF-8">
    <meta name="url" content="{{url('/')}}">
    <title>Autonet | Listado de conductores</title>
    <link href="{{url('img/favicon.png')}}" rel="icon" type="image/x-icon" />
    @include('scripts.styles')
    <link rel="stylesheet" href="{{url('bootstrap/css/datatables.css')}}">
    <link rel="stylesheet" href="{{url('datatables/media/css/dataTables.bootstrap.css')}}">
    <link rel="stylesheet" href="{{url('bootstrap-datetimepicker\css\bootstrap-datetimepicker.min.css')}}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.css">
</head>
<body>
@include('admin.menu')

<div class="col-lg-12">

    <h3 class="h_titulo">LISTADO DE CONDUCTORES</h3>
    <div class="col-sm-3">
      <div class="row">
        <div class="form-group">
          <select class="form-control input-font" name="listado_conductores">
            <option value="0">-</option>
            <option value="1">TODOS</option>
            <option value="5">ACTIVOS</option>
            <option value="2">ARCHIVADOS</option>
            @if(Auth::user()->id===2)
              <option value="3">CON FOTO</option>
              <option value="4">CON FOTO SIN AUTORIZAR</option>
            @endif
          </select>
        </div>
      </div>
    </div>
    <table class="table table-bordered table-hover" id="listado_conductores">
    	<thead>
    		<tr>
    			<th>Proveedor</th>
    			<th>Nombre Completo</th>
    			<th>Edad</th>
    			<th>Cedula</th>
    			<th>Fecha Vinculacion</th>
    			<th>Tipo Licencia</th>
    			<th>Fecha Expedicion</th>
    			<th>Fecha Vigencia</th>
    			<th>Informacion</th>
    		</tr>
    	</thead>
    	<tbody>
        <?php
          ##CANTIDAD DE DOCUMENTOS POR VENCER POR TODOS LOS CONDUCTORES
          $documentos_por_vencer = 0;

          $documentos_vencidos = 0;

          #CANTIDAD DE CONDUCTORES QUE NO TIENEN SEGURIDAD SOCIAL
          $contar_seguridad = 0;
        ?>

        @foreach($conductores as $conductor)
            @if($conductor->nombre_completo)
                <?php

                  $date = date('Y-m-d');

                  $i = 0;
                  $seguridad_social = null;
                  $estado_ssocial = null;

                  $ss = "select seguridad_social.fecha_inicial, seguridad_social.fecha_final from seguridad_social where conductor_id = ".$conductor->id." and '".$date."' between fecha_inicial and fecha_final ";
                  $ss = DB::select($ss);

                  if($ss!=null){
                    
                  }else{
                    $contar_seguridad++;
                  }

                  ##CANTIDAD DE DOCUMENTOS VENCIDOS POR CONDUCTOR
                  $documentos_vencidos_por_conductor = 0;

                  ##CANTIDAD DE DOCUMENTOS POR VENCER POR CONDUCTOR
                  $documentos_por_vencer_por_conductor = 0;

                  $licencia_conduccion = floor((strtotime($conductor->fecha_licencia_vigencia)-strtotime(date('Y-m-d')))/86400);



                  //DIA ACTUAL
                  $fecha_actual = intval(date('d'));

                  ##CANTIDAD DE CONDUCTORES QUE TIENEN DOCUMENTOS VENCIDOS
                  if ($licencia_conduccion<0){
                      $documentos_vencidos_por_conductor++;
                      $documentos_vencidos++;
                  }

                  ##CANTIDAD DE CONDUCTORES QUE TIENEN DOCUMENTOS POR VENCER
                  if (($licencia_conduccion>=0 && $licencia_conduccion<=30))
                  {
                      $documentos_por_vencer++;
                      $documentos_por_vencer_por_conductor++;
                  }
                ?>
                <tr
                    data-conductor-id="{{$conductor->id}}"
                    data-seguridad="<?php if($estado_ssocial===null): echo '0'; else: echo '1'; endif; ?>"
                    data-vencido="<?php if($documentos_vencidos_por_conductor>0): echo '1'; else: echo '0'; endif; ?>"
                    data-por-vencer="<?php if($documentos_por_vencer_por_conductor>0): echo '1'; else: echo '0'; endif; ?>"
                    class="@if($conductor->bloqueado_total==1 and $conductor->bloqueado==1){{'danger warning'}}@elseif($conductor->bloqueado_total==1){{'danger'}}@elseif($conductor->bloqueado==1){{'warning'}}@endif">
                    <td>{{$conductor->proveedor->razonsocial}}</td>
                    <td>{{$conductor->nombre_completo}}</td>
                    <td>{{$conductor->edad}}</td>
                    <td>{{$conductor->cc}}</td>
                    <td>{{$conductor->fecha_vinculacion}}</td>
                    <td>{{$conductor->tipodelicencia}}</td>
                    <td>{{$conductor->fecha_licencia_expedicion}}</td>
                    <td>{{$conductor->fecha_licencia_vigencia}}</td>
                    <td>
                        <div class="btn-group dropdown" style="display: inline-block">
                            <button style="padding: 6px 8px 6px 8px; display: inline-block" type="button" class="btn btn-success dropdown-toggle btn-list-table" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                ver <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                              <li>
                                <a href="{{url('proveedores/conductores/'.$conductor->proveedores_id)}}">DOCUMENTACION</a>
                              </li>
                              <li>
                                <a href="#" class="bloqueo_conductores" data-conductor-id="{{$conductor->id}}" data-toggle="modal" data-target="#open_modal_bloqueo_conductores">BLOQUEOS</a>
                              </li>
                              <li>
                                <a href="#" data-id-conductor="{{$conductor->id}}" data-toggle="modal" data-target=".mdl_app_aotour" class="app_aotour">APP MOBILE</a>
                              </li>
                              <li><a data-conductor-id="{{$conductor->id}}" href="#" class="archivar">ARCHIVAR</a></li>
                            </ul>
                        </div>

                        <a <?php
                             if($licencia_conduccion<=30){
                                 echo 'data-licencia-conduccion="'.$licencia_conduccion.'"';
                             }
                           ?>
                           data-toggle="modal" data-target=".mdl_alertas" data-var="" class="btn btn-list-table btn-<?php if($documentos_vencidos_por_conductor>0): echo 'danger'; else: echo 'default'; endif ?> mostrar_alertas">
                             <i class="fa fa-envelope-o">
                                <span style="padding: 0 4px;" class="badge"><?php echo $documentos_vencidos_por_conductor; ?></span>
                            </i>
                        </a>
                    </td>
                </tr>
            @endif
        @endforeach
    	</tbody>
    </table>
    <a id="mostrar_por_vencer" style="margin-top: 5px" class="btn btn-default btn-icon">POR VENCER <i class="fa fa-filter icon-btn"></i></a>
    <a id="mostrar_vencidos" style="margin-top: 5px" class="btn btn-default btn-icon">VENCIDOS <i class="fa fa-ban icon-btn"></i></a>
    <a id="mostrar_todos" style="margin-top: 5px" class="btn btn-default btn-icon">TODOS <i class="fa fa-car icon-btn" aria-hidden="true"></i></a>
    <a id="mostrar_seguridad" style="margin-top: 5px" class="btn btn-default btn-icon">SEGURIDAD SOCIAL <i class="fa fa-heart icon-btn" aria-hidden="true"></i></a>

</div>

<div class="ventana_modal">
    <div class="col-lg-12">
        <div class="panel panel-primary">
            <div class="panel-heading"><strong>ALERTAS DE DOCUMENTACION</strong>
                <i style="cursor: pointer; float: right; font-weight:100" class="fa fa-close cerrar_ventana"></i>
            </div>
            <div class="panel-body">
				<a id="mostrar_por_vencer_modal" style="cursor: pointer;">Hay un total de <strong><?php echo $documentos_por_vencer; ?></strong> conductores con documentos prontos a vencer!</a><br>
                <a id="mostrar_vencidos_modal" style="cursor: pointer;">Hay un total de <strong>{{ $documentos_vencidos }}</strong> conductores con documentos vencidos!</a><br>
                <a id="mostrar_seguridad_modal" style="cursor: pointer;">Hay un total de <strong><?php echo $contar_seguridad; ?></strong> conductores que no cuentan con seguridad social este mes!</a><br>

            </div>
        </div>
    </div>
</div>

<div class="modal fade mdl_alertas" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-medium">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                <strong>ALERTAS</strong>
            </div>
            <div class="modal-body">
                <div id="alertas_vigencia">
                </div>
            </div>
            <div class="modal-footer">
                <a data-dismiss="modal" id="limpiar" class="btn btn-danger btn-icon">Cerrar<i class="fa fa-times icon-btn"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade mdl_app_aotour" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-medium">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                <strong>USUARIO AOTOUR MOBILE</strong>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <a class="btn btn-primary btn-icon hidden" id="actualizar_datos_app">ACTUALIZAR<i class="fa fa-refresh icon-btn"></i></a>
                <a data-dismiss="modal" id="limpiar" class="btn btn-danger btn-icon">Cerrar<i class="fa fa-times icon-btn"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="open_modal_bloqueo_uso" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <form id="form_bloqueo_uso_conductor" style="margin-bottom: 0px">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <strong>BLOQUEO DE USO DE CONDUCTOR</strong>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="detalles_bloqueo_uso">Detalles</label>
            <textarea type="text" class="form-control input-font" name="detalles" rows="6" id="detalles_bloqueo_uso"
                      placeholder="Digite la razon por la que este conductor sera bloqueado"></textarea>
            <small class="text-danger hidden"></small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal">CERRAR</button>
          <button type="submit" class="btn btn-primary">BLOQUEAR</button>
          <a class="btn btn-primary desbloquear_uso_conductor hidden">DESBLOQUEAR</a>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal" id="open_modal_bloqueo_conductores" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <strong>BLOQUEO CONDUCTOR</strong>
      </div>
      <div class="modal-footer">
        <a type="button" class="btn btn-warning" id="bloqueo_uso">BLOQUEO DE USO</a>
        <a type="button" class="btn btn-danger" id="bloqueo_total">BLOQUEO TOTAL</a>
        <a type="button" class="btn btn-default" data-dismiss="modal">CERRAR</a>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="open_modal_bloqueo_total" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <form id="form_bloqueo_total_conductor" style="margin-bottom: 0px">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <strong>BLOQUEO TOTAL DE CONDUCTOR</strong>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="detalles_bloqueo_total">Detalles</label>
            <textarea type="text" class="form-control input-font" name="detalles" rows="6" id="detalles_bloqueo_total"
                      placeholder="Digite la razon por la que este conductor sera bloqueado"></textarea>
            <small class="text-danger hidden"></small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal">CERRAR</button>
          <button type="submit" class="btn btn-primary">BLOQUEAR</button>
          <a class="btn btn-primary desbloquear_total_conductor hidden">DESBLOQUEAR</a>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id='modal_img'>
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
        <div class="modal-header" style="background: #0FAEF3">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" style="text-align: center;">Foto</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-lg-12" align="center">
              <img style="width: 410px; height: 350px" id="imagen">
            </div>
          </div>
        </div>
        <div class="modal-footer">          
          <button type="button" id="aprobar_foto" class="btn btn-primary" style="float: left;">Aprobar esta foto <i class="fa fa-check" aria-hidden="true"></i></button>
          <button type="button" id="desaprobar_foto" class="btn btn-primary" style="float: left; background: red">Desaprobar esta foto <i class="fa fa-remove" aria-hidden="true"></i></button>
          <button type="button" class="btn btn-default" data-dismiss="modal" style="background: #B1B2B4">Cerrar</button>
        </div>
    </div>
  </div>
</div>

@include('scripts.scripts')
<script src="{{url('jquery/jquery-ui.min.js')}}"></script>
<script src="{{url('datatables/media/js/jquery.datatables.js')}}"></script>
<script src="{{url('bootstrap-datetimepicker\js\moment.js')}}"></script>
<script src="{{url('bootstrap-datetimepicker\js\moment-with-locales.js')}}"></script>
<script src="{{url('bootstrap-datetimepicker\js\bootstrap-datetimepicker.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.js"></script>
<script src="{{url('jquery/conductores.js')}}"></script>

</body>
</html>
