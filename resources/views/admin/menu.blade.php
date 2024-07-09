
    <nav class="navbar navbar-custom">
      <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar" style="background: #ffffff;"></span>
            <span class="icon-bar" style="background: #ffffff;"></span>
            <span class="icon-bar" style="background: #ffffff;"></span>
          </button>
          <a class="navbar-brand" href="{{url('/')}}">

              <img src="{{url('images/logo.png')}}" width="100px" height="100px">

          </a>

        </div>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav navbar-right">

              <!-- ESCOLAR
              @if(isset($permisos->portalusuarios->qrusers->ver))
                @if($permisos->portalusuarios->qrusers->ver==='on')
                @endif
              @endif-->

              @if(isset($permisos->escolar->gestion->ver))
                @if($permisos->escolar->gestion->ver==='on')
                  <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Rutas <i class="fa fa-bars"></i><span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li >
                          <a class="dropdown-toggle" href="{{url('transporteescolar/seguimientoservicios')}}" >Seguimiento a Rutas  <i class="fa fa-bus" aria-hidden="true"></i></a>
                        </li>
                    </ul>
                  </li>
                @endif
              @endif

              @if(isset($permisos->escolar->gestion->ver))
                @if($permisos->escolar->gestion->ver==='on')
                  <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Información y Pagos <i class="fa fa-bars"></i><span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li >
                          <a class="dropdown-toggle" href="{{url('transporteescolar/seguimiento')}}" >Información Personal  <i class="fa fa-bars" aria-hidden="true"></i></a>
                        </li>
                        <li >
                          <a class="dropdown-toggle" href="{{url('transporteescolar/enlacesdepago')}}" >Pagos <i class="fa fa-money" aria-hidden="true"></i></a>
                        </li>
                    </ul>
                  </li>
                @endif
              @endif

              <!-- ESCOLAR -->

              @if(isset($permisos->portalusuarios->qrusers->ver))
                @if($permisos->portalusuarios->qrusers->ver==='on')
                  <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Código QR <i class="fa fa-qrcode"></i><span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li >
                          <a class="dropdown-toggle" href="{{url('portalusu/qrcode')}}" >Descargar Código QR  <i class="fa fa-qrcode" aria-hidden="true"></i></a>
                        </li>
                        <li >
                          <a class="dropdown-toggle" href="{{url('portalusu/politicas')}}" >Descargar Politicas de datos <i class="fa fa-cloud-download" aria-hidden="true"></i></a>
                        </li>
                    </ul>
                  </li>
                @endif
              @endif
              <!-- -->
                @if(isset($permisos->portalusuarios->gestiondocumental->ver))
                  @if($permisos->portalusuarios->gestiondocumental->ver==='on' and (Auth::user()->centrodecosto_id!=329 and Auth::user()->centrodecosto_id!=343))
                    <!--<li class="dropdown">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Gestión documental <i class="fa fa-pencil-square-o" aria-hidden="true"></i></i><span class="caret"></span></a>
                      <ul class="dropdown-menu">
                        <li>
                          <a class="dropdown-toggle" href="{{url('gestiondocumental/verificacionderutas')}}">Verificación de fotos <i class="fa fa-camera" aria-hidden="true"></i></a>
                        </li>
                      </ul>
                    </li>-->
                  @endif
                @endif
              <!-- -->
              @if(isset($permisos->portalusuarios->admin->ver))
                @if($permisos->portalusuarios->admin->ver==='on' and (Auth::user()->centrodecosto_id!=329 and Auth::user()->centrodecosto_id!=343))
                  <!--<li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">PQR <i class="fa fa-pencil-square-o" aria-hidden="true"></i></i><span class="caret"></span></a>
                    <ul class="dropdown-menu">
                      <li>
                        <a class="dropdown-toggle" href="{{url('pqr')}}" >Generar PQR <i class="fa fa-share-square" aria-hidden="true"></i></a>
                      </li>
                    </ul>
                  </li>-->
                @endif
              @endif
              <!-- -->

                @if(isset($permisos->portalusuarios->admin->ver))
                  @if($permisos->portalusuarios->admin->ver==='on' and (Auth::user()->centrodecosto_id!=329 and Auth::user()->centrodecosto_id!=343))
                    <li class="dropdown">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">PQR <i class="fa fa-exclamation-triangle"></i><span class="caret"></span></a>
                      <ul class="dropdown-menu">
                        <li>
                          <a class="dropdown-toggle" href="{{url('reportes/listapqr')}}" >PQR <i class="fa fa-users" aria-hidden="true"></i></a>
                        </li>
                        <!--<li>
                          <a class="dropdown-toggle" href="{{url('reportes/reporteutilizacion')}}" >Reporte de Utilización <i class="fa fa-bar-chart" aria-hidden="true"></i></a>
                        </li>-->
                      </ul>
                    </li>
                  @endif
                @endif

                @if(isset($permisos->portalusuarios->admin->ver))
                  @if($permisos->portalusuarios->admin->ver==='on' and (Auth::user()->centrodecosto_id!=329 and Auth::user()->centrodecosto_id!=343))
                    <li class="dropdown">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Novedades <i class="fa fa-exclamation-triangle"></i><span class="caret"></span></a>
                      <ul class="dropdown-menu">
                        <li>
                          <a class="dropdown-toggle" href="{{url('reportes/lista')}}" >Novedades de Rutas <i class="fa fa-users" aria-hidden="true"></i></a>
                        </li>
                        <li>
                          <a class="dropdown-toggle" href="{{url('reportes/meses')}}" >Dashboard <i class="fa fa-bar-chart" aria-hidden="true"></i></a>
                        </li>
                      </ul>
                    </li>
                  @endif
                @endif


              <!--@if(isset($permisos->portalusuarios->admin->ver))
                @if($permisos->portalusuarios->admin->ver==='on' and (Auth::user()->centrodecosto_id!=329 and Auth::user()->centrodecosto_id!=343))
                  <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Usuarios <i class="fa fa-users"></i><span class="caret"></span></a>
                    <ul class="dropdown-menu">
                      <li>
                        <a class="dropdown-toggle" href="{{url('listadousuariosqr')}}" >Usuarios QR <i class="fa fa-user" aria-hidden="true"></i></a>
                      </li>
                      <li>
                        <a class="dropdown-toggle" href="{{url('portalusu/exportardatos')}}" >Exportar Información de Rutas <i class="fa fa-download" aria-hidden="true"></i></a>
                      </li>
                    </ul>
                  </li>-->

                  <!--<li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Dashboard <i class="fa fa-tachometer" aria-hidden="true"></i><span class="caret"></span></a>
                    <ul class="dropdown-menu">
                      <li>
                        <a class="dropdown-toggle" href="{{url('portalusu/dashboardadministrador')}}" >Ver Dashboard <i class="fa fa-tachometer" aria-hidden="true"></i></a>
                      </li>
                      <li>
                        <a class="dropdown-toggle" href="{{url('portalusu/exportardatos')}}" >Exportar Información de Rutas <i class="fa fa-download" aria-hidden="true"></i></a>
                      </li>
                    </ul>
                  </li>-->
                <!--@endif
              @endif-->

                @if(isset($permisos->portalusuarios->admin->ver) or isset($permisos->portalusuarios->bancos->ver) or isset($permisos->portalusuarios->ejecutivo->ver) )
                  @if($permisos->portalusuarios->admin->ver==='on' or $permisos->portalusuarios->bancos->ver==='on' or $permisos->portalusuarios->ejecutivo->ver==='on' )
                    <li class="dropdown">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Servicios <i class="fa fa-bars"></i><span class="caret"></span></a>
                      <ul class="dropdown-menu">

                        @if(isset($permisos->portalusuarios->ejecutivo->ver))
                          @if($permisos->portalusuarios->ejecutivo->ver==='on' and (Auth::user()->centrodecosto_id===329 || Auth::user()->centrodecosto_id===343 || Auth::user()->centrodecosto_id===287 || Auth::user()->centrodecosto_id===19))
                              <li>
                                <a class="dropdown-toggle" href="{{url('serviciosejecutivos')}}" >Solicitud de Servicios <i class="fa fa-share" aria-hidden="true"></i></a>
                              </li>
                          @endif
                        @endif

                          @if(isset($permisos->portalusuarios->ejecutivo->ver))
                            @if($permisos->portalusuarios->ejecutivo->ver==='on' and (Auth::user()->centrodecosto_id===329 || Auth::user()->centrodecosto_id===343 || Auth::user()->centrodecosto_id===287 || Auth::user()->centrodecosto_id===19))
                              <li >
                                <a class="dropdown-toggle" href="{{url('serviciosejecutivos/programados')}}" >Servicios Programados <i class="fa fa-tasks" aria-hidden="true"></i></a>
                              </li>
                            @endif
                          @endif

                            @if( ($permisos->portalusuarios->admin->ver==='on' or $permisos->portalusuarios->bancos->ver==='on') and (Auth::user()->centrodecosto_id!=329 and Auth::user()->centrodecosto_id!=343 and Auth::user()->centrodecosto_id!=287 and Auth::user()->centrodecosto_id!=19))
                              <li >
                                <a class="dropdown-toggle" href="{{url('serviciosadmin')}}" >Servicios Ejecutivos <i class="fa fa-car" aria-hidden="true"></i></a>
                              </li>
                            @endif

                          @if($permisos->portalusuarios->admin->ver==='on' and (Auth::user()->centrodecosto_id!=329 and Auth::user()->centrodecosto_id!=343 and Auth::user()->centrodecosto_id!=287 and Auth::user()->centrodecosto_id!=19))
                            <li>
                              <a class="dropdown-toggle" href="{{url('serviciosadmin/rutasempresariales')}}" >Rutas Empresariales <i class="fa fa-bus" aria-hidden="true"></i></a>
                            </li>
                          @endif

                      </ul>
                    </li>
                  @endif
                @endif

                @if(isset($permisos->portalproveedores->documentacion->ver))
                  @if($permisos->portalproveedores->documentacion->ver==='on')
                    <li class="dropdown">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Fuec <i class="fa fa-file-pdf-o"></i><span class="caret"></span></a>
                      <ul class="dropdown-menu">

                        <li >
                          <a class="dropdown-toggle" href="{{url('portalproveedores/generarfuec')}}" >Generar mis Fuec <i class="fa fa-plus" aria-hidden="true"></i></a>
                        </li>

                        <li>
                          <a class="dropdown-toggle" href="{{url('portalproveedores/ver')}}" >
                          Descargar mis Fuec <i class="fa fa-download" aria-hidden="true"></i></a>
                        </li>

                        <!--<li>
                          <a class="dropdown-toggle" href="{{url('portalproveedores/documentacionproveedor')}}" >
                          Proveedor <i class="fa fa-male" aria-hidden="true"></i></a>
                        </li>-->

                      </ul>
                    </li>
                  @endif
                @endif

                @if(isset($permisos->portalproveedores->documentacion->ver))
                  @if($permisos->portalproveedores->documentacion->ver==='on')
                    <li class="dropdown">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Documentación <i class="fa fa-book"></i><span class="caret"></span></a>
                      <ul class="dropdown-menu">

                        <li >
                          <a class="dropdown-toggle" href="{{url('portalproveedores/documentacionvehiculos')}}" >Vehículos <i class="fa fa-car" aria-hidden="true"></i></a>
                        </li>

                        <li>
                          <a class="dropdown-toggle" href="{{url('portalproveedores/documentacionconductores')}}" >
                          Conductores <i class="fa fa-users" aria-hidden="true"></i></a>
                        </li>

                        <!--<li>
                          <a class="dropdown-toggle" href="{{url('portalproveedores/documentacionproveedor')}}" >
                          Proveedor <i class="fa fa-male" aria-hidden="true"></i></a>
                        </li>-->

                      </ul>
                    </li>
                  @endif
                @endif

                @if(isset($permisos->portalproveedores->cuentasdecobro->ver))
                  @if($permisos->portalproveedores->cuentasdecobro->ver==='on')
                    <li class="dropdown">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Cuentas de Cobro <i class="fa fa-money"></i><span class="caret"></span></a>
                      <ul class="dropdown-menu">

                        <li >
                          <a class="dropdown-toggle" href="{{url('portalproveedores/clientes')}}" >Generar <i class="fa fa-thumb-tack" aria-hidden="true"></i></a>
                        </li>

                        <li>
                          <a class="dropdown-toggle" href="{{url('portalproveedores/solicitudactual')}}" >
                          Estado de solicitud actual <i class="fa fa-hourglass-start" aria-hidden="true"></i></a>
                        </li>

                        <li>
                          <a class="dropdown-toggle" href="{{url('portalproveedores/historialdecuentas')}}" >
                          Mis Cuentas de Cobro <i class="fa fa-th-list" aria-hidden="true"></i></a>
                        </li>

                      </ul>
                    </li>
                  @endif
                @endif

                @if(isset($permisos->portalproveedores->cuentasdecobro->ver))
                  @if($permisos->portalproveedores->cuentasdecobro->ver==='on')
                    <li class="dropdown">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Programaciones <i class="fa fa-car"></i><span class="caret"></span></a>
                      <ul class="dropdown-menu">

                        <li >
                          <a class="dropdown-toggle" href="{{url('portalproveedores/programaciones')}}" >Histórico <i class="fa fa-history" aria-hidden="true"></i></a>
                        </li>

                        <!--<li>
                          <a class="dropdown-toggle" href="{{url('portalproveedores/solicitudactual')}}" >
                          Estado de solicitud actual <i class="fa fa-hourglass-start" aria-hidden="true"></i></a>
                        </li>

                        <li>
                          <a class="dropdown-toggle" href="{{url('portalproveedores/historialdecuentas')}}" >
                          Mis Cuentas de Cobro <i class="fa fa-th-list" aria-hidden="true"></i></a>
                        </li>-->

                      </ul>
                    </li>
                  @endif
                @endif
              
              @if(isset($permisos->barranquilla->transportesbq->ver) or isset($permisos->bogota->transportes->ver) or isset($permisos->otrostransporte->otrostransportes->ver))
                  <li class="dropdown">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Servicios <i class="fa fa-bars"></i>

                      <?php
                        $sin_programar_baq = 0;
                        $sin_programar_bog = 0;
                      ?>

                    @if(isset($permisos->barranquilla->ejecutivosbq->ver) or isset($permisos->bogota->ejecutivos->ver))
                      <!-- INCLUIR ROL -->
                      <!--@'servicios.servicios_ejecutivos.pusher_solicitud_servicios'-->

                      @if(isset($permisos->barranquilla->ejecutivosbq->ver))
                        @if($permisos->barranquilla->ejecutivosbq->ver==='on')
                          <!-- BARRANQUILLA -->
                          <?php

                            $sin_programar_baq = DB::table('servicios_autonet')->whereNull('estado_programado')->where('localidad','barranquilla')->count();

                          ?>
                          @if($sin_programar_baq>0)
                            <div class="badge_menu_head fontbulger servicios_autonetbaq_badge" style="margin-left: 6px">{{$sin_programar_baq}}
                            </div>
                          @else
                            <div class="badge_menu_head servicios_autonetbaq_badge">{{$sin_programar_baq}}
                            </div>
                          @endif
                        @endif
                      @endif


                      @if(isset($permisos->bogota->ejecutivos->ver))
                        @if($permisos->bogota->ejecutivos->ver==='on')
                          <!-- BOGOTA -->
                          <?php

                            $sin_programar_bog = DB::table('servicios_autonet')->whereNull('estado_programado')->where('localidad','bogota')->count();

                          ?>
                          @if($sin_programar_bog>0)
                            <div class="badge_menu_head fontbulger servicios_autonetbog_badge" style="margin-left: 6px">{{$sin_programar_bog}}
                            </div>
                          @else
                            <div class="badge_menu_head servicios_autonetbog_badge">{{$sin_programar_bog}}
                            </div>
                          @endif
                        @endif
                      @endif

                    @endif
                      <span class="caret"></span></a>
                      <ul class="dropdown-menu">
                        @if(isset($permisos->barranquilla->transportesbq->ver))
                              @if($permisos->barranquilla->transportesbq->ver==='on')


                          <li class="dropdown-submenu">
                            <a class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bold" aria-hidden="true"></i>ARRANQUILL<i class="fa fa-buysellads" aria-hidden="true"></i></a>
                            <ul class="dropdown-menu">
                                <li class="dropdown-submenu">
                                  <a class="dropdown-toggle" data-toggle="dropdown">Transportes <i class="fa fa-road"></i></a>
                                  <ul class="dropdown-menu">
                                    <li><a href="{{url('transportesbaq')}}">Servicios <i class="fa fa-car" aria-hidden="true"></i></a></li>
                                    <li><a href="{{url('transportesbaq/serviciosporprogramar')}}">No programados <i class="fa fa-car" aria-hidden="true"></i>
                                    @if(isset($permisos->barranquilla->ejecutivosbq->ver))
                                      @if($permisos->barranquilla->ejecutivosbq->ver==='on')
                                        <div class="badge_menu_head baq">{{$sin_programar_baq}}</div>
                                      @endif
                                    @endif</a></li>

                                <li><a href="{{url('transportesrutas')}}">Rutas <i class="fa fa-bus" aria-hidden="true"></i></a></li>
                                <li><a href="{{url('reportes/programacionbarranquilla')}}">Programación <i class="fa fa-table"></i></a></li>

                                <li><a href="{{url('serviciosyrutasbarranquilla')}}">Servicios y Rutas <i class="fa fa-car" aria-hidden="true"></i> <i class="fa fa-bus" aria-hidden="true"></i></a></li>

                                <li><a href="{{url('reportes/campanas')}}">Novedades <i class="fa fa-list" aria-hidden="true"></i></a></li>

                                <li><a href="{{url('maps/liveview')}}">Live View <i class="fa fa-map-marker" aria-hidden="true"></i></a></li>

                            </ul>
                                </li>

                                @if(isset($permisos->barranquilla->poreliminarbq->ver))
                                  @if($permisos->barranquilla->poreliminarbq->ver==='on')
                                    <li><a href="{{url('transportesbaq/serviciosporeliminar')}}">Servicios por Eliminar <i class="fa fa-ban"></i></a></li>
                                    <li><a href="{{url('transportesbaq/servicioseditados')}}">Servicios Editados <i class="fa fa-pencil"></i></a></li>
                                  @endif
                                @endif
                                @if(isset($permisos->turismo->otros->ver))
                                  @if($permisos->turismo->otros->ver==='on')
                                    <li><a href="{{url('otrosservicios')}}">Otros <i class="fa fa-ticket"></i></a></li>
                                  @endif
                                @endif
                                @if(isset($permisos->barranquilla->papeleradereciclajebq->ver))
                                  @if($permisos->barranquilla->papeleradereciclajebq->ver==='on')
                                    <li><a href="{{url('papelera')}}">Papelera de reciclaje <i class="fa fa-trash-o"></i></a></li>
                                  @endif
                                @endif
                                @if(isset($permisos->barranquilla->transportesbq->ver))
                                  @if($permisos->barranquilla->transportesbq->ver==='on')
                                    <li><a href="{{url('gestiondocumental/verificaciondefotosbaq')}}">Fotos de Bioseguridad <i class="fa fa-photo"></i></a></li>
                                  @endif
                                @endif
                            </ul>
                          </li>
                          @endif
                          @endif
                          @if(isset($permisos->bogota->transportes->ver))
                              @if($permisos->bogota->transportes->ver==='on')

                                <li class="dropdown-submenu">
                                <a class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bold" aria-hidden="true"></i>OGOTÁ</a>
                                <ul class="dropdown-menu">
                                    <li class="dropdown-submenu">
                                      <a class="dropdown-toggle" data-toggle="dropdown">Transportes <i class="fa fa-road"></i></a>
                                      <ul class="dropdown-menu">
                                        <li><a href="{{url('transportesbog')}}">Servicios <i class="fa fa-car" aria-hidden="true"></i></a></li>

                                          <li><a href="{{url('transportesbog/serviciosporprogramar')}}">No Programados  <i class="fa fa-calendar" aria-hidden="true"></i>
                                          @if(isset($permisos->bogota->ejecutivos->ver))
                                            @if($permisos->bogota->ejecutivos->ver==='on')
                                              <div class="badge_menu_head bog">{{$sin_programar_bog}}</div> </a></li>
                                            @endif
                                          @endif

                                        <li><a href="{{url('transportesrutasbog')}}">Rutas <i class="fa fa-bus" aria-hidden="true"></i></a></li>

                                        <li><a href="{{url('reportes/programacionbogota')}}">Programación <i class="fa fa-table"></i></a></li>

                                        <li><a href="{{url('serviciosyrutasbogota')}}">Servicios y Rutas <i class="fa fa-car" aria-hidden="true"></i> <i class="fa fa-bus" aria-hidden="true"></i></a></li>

                                        <li><a href="{{url('reportes/campanasbog')}}">Novedades <i class="fa fa-list" aria-hidden="true"></i> </a></li>

                                        <li><a href="{{url('maps/liveviewbog')}}">Live View <i class="fa fa-map-marker" aria-hidden="true"></i></a></li>

                                        <li><a href="{{url('transportesbog/ordenes')}}">Reconfirmación <i class="fa fa-mouse-pointer" aria-hidden="true"></i></a></li>

                                        <li><a href="{{url('reportes/disponibilidadbogota')}}">Disponibilidad <i class="fa fa-list-alt" aria-hidden="true"></i></a></li>

                                      </ul>
                                    </li>
                                    @if(isset($permisos->transporteescolar->gestionusuarios->ver))
                                      @if($permisos->transporteescolar->gestionusuarios->ver==='on')
                                        <li class="dropdown-submenu">
                                          <a class="dropdown-toggle" data-toggle="dropdown">Transporte Escolar <i class="fa fa-graduation-cap" aria-hidden="true"></i></a>
                                          <ul class="dropdown-menu">
                                            <li><a href="{{url('transporteescolar')}}">Crear Usuarios <i class="fa fa-plus" aria-hidden="true"></i></a></li>

                                            <li><a href="{{url('transporteescolar/listado')}}">Lista de Usuarios <i class="fa fa-list" aria-hidden="true"></i></a></li>

                                          </ul>
                                        </li>
                                      @endif
                                    @endif

                                    @if(isset($permisos->bogota->poreliminar->ver))
                                      @if($permisos->bogota->poreliminar->ver==='on')
                                        <li><a href="{{url('transportesbog/serviciosporeliminar')}}">Servicios por Eliminar <i class="fa fa-ban"></i></a></li>
                                        <li><a href="{{url('transportesbog/servicioseditados')}}">Servicios Editados <i class="fa fa-pencil"></i></a></li>
                                      @endif
                                    @endif
                                    @if(isset($permisos->turismo->otros->ver))
                                      @if($permisos->turismo->otros->ver==='on')
                                        <li><a href="{{url('otrosservicios')}}">Otros <i class="fa fa-ticket"></i></a></li>
                                      @endif
                                    @endif
                                    @if(isset($permisos->bogota->papeleradereciclaje->ver))
                                      @if($permisos->bogota->papeleradereciclaje->ver==='on')
                                         <li><a href="{{url('papelerabog')}}">Papelera de reciclaje <i class="fa fa-trash-o"></i></a></li>
                                       @endif
                                    @endif
                                    @if(isset($permisos->bogota->transportes->ver))
                                      @if($permisos->bogota->transportes->ver==='on')
                                        <li><a href="{{url('gestiondocumental/verificaciondefotosbog')}}">Fotos de Bioseguridad <i class="fa fa-photo"></i></a></li>
                                      @endif
                                    @endif
                                </ul>
                              </li>
                              @endif
                          @endif
                          @if(isset($permisos->otrostransporte->otrostransporte->ver))
                            @if($permisos->otrostransporte->otrostransporte->ver==='on')

                              <li class="dropdown-submenu">
                                <a class="dropdown-toggle" data-toggle="dropdown">OTROS <i class="fa fa-globe" aria-hidden="true"></i></a>
                                <ul class="dropdown-menu">
                                    <li><a href="{{url('transportes')}}">Transportes AO <i class="fa fa-car" aria-hidden="true"></i></a></li>

                                </ul>
                              </li>
                              @endif
                              @endif
                      </ul>
                  </li>
              @endif

              @if(isset($permisos->facturacion->revision->ver) or isset($permisos->facturacion->liquidacion->ver) or isset($permisos->facturacion->autorizar->ver) or isset($permisos->facturacion->ordenes_de_facturacion->ver))
                <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Facturacion <i class="fa-solid fa-money-bill-trend-up fa-beat-fade"></i><span class="caret"></span></a>
                <ul class="dropdown-menu">
                    @if(isset($permisos->facturacion->revision->ver))
                        @if($permisos->facturacion->revision->ver==='on')
                            <li><a href="{{url('facturacion/revision')}}">Revision <i class="fa-solid fa-clipboard-list fa-flip fa-xl" style="color: #001c40;"></i></a></li>
                        @endif
                    @endif
                    @if(isset($permisos->facturacion->liquidacion->ver))
                        @if($permisos->facturacion->liquidacion->ver==='on')
                            <li><a href="{{url('facturacion/liquidacion')}}">Liquidacion <i class="fa fa-file-o"></i></a></li>
                        @endif
                    @endif
                    @if(isset($permisos->facturacion->autorizar->ver))
                        @if($permisos->facturacion->autorizar->ver==='on')
                            <li><a href="{{url('facturacion/autorizacionservicios')}}">Pendientes Autorizar <i class="fa fa-square-o" aria-hidden="true"></i></a></li>
                            <li><a href="{{url('facturacion/serviciosautorizados')}}">Autorizados <i class="fa fa-check-square-o"></i></a></li>
                        @endif
                    @endif
                    @if(isset($permisos->facturacion->ordenes_de_facturacion->ver))
                        @if($permisos->facturacion->ordenes_de_facturacion->ver==='on')
                            <li><a href="{{url('facturacion/ordenesfacturacion')}}">Ordenes Facturacion <i class="fa-solid fa-file-invoice fa-beat"></i></a></li>
                            <li><a href="{{url('facturacion/facturasconap')}}">Con AP Sin Ingreso <i class="fa fa-files-o" aria-hidden="true"></i></a></li>
                            <!--<li class="dropdown-submenu">
                                <a class="dropdown-toggle" data-toggle="dropdown">Facturas <i class="fa fa-text"></i></a>
                                <ul class="dropdown-menu">
                                    <li><a href="{{url('facturacion/facturasanuladas')}}">Anuladas <i class="fa fa fa-text" aria-hidden="true"></i></a></li>
                                    <li><a href="{{url('facturacion/facturasporvencer')}}">Por Vencer <i class="fa fa fa-text" aria-hidden="true"></i></a></li>
                                    <li><a href="{{url('facturacion/facturasvencidas')}}">Vencidas <i class="fa fa fa-text" aria-hidden="true"></i></a></li>
                                    <li><a href="{{url('facturacion/facturasconingreso')}}">Con Ingreso<i class="fa fa fa-text" aria-hidden="true"></i></a></li>
                                    <li><a href="{{url('facturacion/facturasrevisadas')}}">Revisadas <i class="fa fa fa-text"></i></a></li>

                                    <li><a href="{{url('facturacion/facturasconap')}}">Con AP SIN INGRESO <i class="fa fa fa-text"></i></a></li>

                                </ul>
                            </li>-->
                        @endif
                    @endif
                </ul>
              </li>
              @endif

              @if(isset($permisos->contabilidad->pago_proveedores->ver) or isset($permisos->contabilidad->comisiones->ver))
                <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Contabilidad <i class="fa-solid fa-comments-dollar fa-bounce"></i>
                  @if(Auth::user()->id===2 or Auth::user()->id===37 or Auth::user()->id===170)

                      <!-- ALERTA CUENTAS -->
                      <?php
                      $consul = DB::table('listado_cuentas')->where('estado',0)->get();
                      $consul = count($consul);

                      $consul2 = DB::table('listado_cuentas')->where('estado',2)->get();
                      $consul2 = count($consul2);
                      ?>

                      <!--'otros.pushercontabilidad'-->
                      @if($consul>0)
                                <div style="background: gray; color: yellow" class="badge_menu_head fontbulger contabilidad_cuenta_badge">{{$consul}}
                                </div>

                      @else
                                <div style="background: gray; color: yellow" class="badge_menu_head contabilidad_cuenta_badge">0
                                </div>

                      @endif

                      @if($consul2>0)

                                <div style="background: gray; color: yellow" class="badge_menu_head fontbulger contabilidad_cuenta2_badge">{{$consul2}}
                                </div>
                      @else

                                <div style="background: gray; color: yellow" class="badge_menu_head contabilidad_cuenta2_badge">0
                                </div>
                      @endif

                  @endif<span class="caret"></span></a>
                  <ul class="dropdown-menu">
                      @if(isset($permisos->contabilidad->pago_proveedores->ver))
                          @if($permisos->contabilidad->pago_proveedores->ver==='on')
                          <li><a href="{{url('facturacion/pagoproveedores')}}">Pagos a Proveedores <i class="fa fa-building-o" aria-hidden="true"></i></a></li>
                          @endif
                      @endif
                      @if(isset($permisos->contabilidad->listado_de_pagos_preparar->ver))
                          @if($permisos->contabilidad->listado_de_pagos_preparar->ver==='on')
                            <li><a href="{{url('facturacion/listadoprestamosproveedores')}}">Préstamos a Proveedores <i class="fa fa-credit-card"></i></a></li>
                          @endif
                      @endif
                      @if(isset($permisos->contabilidad->listado_de_pagos_preparar->ver))
                          @if($permisos->contabilidad->listado_de_pagos_preparar->ver==='on')
                            <li><a href="{{url('transporteescolar/pagos')}}">Pagos Wompi <i class="fa fa-money"></i></a></li>
                          @endif
                      @endif
                      @if(isset($permisos->contabilidad->comisiones->ver))
                          @if($permisos->contabilidad->comisiones->ver==='on')
                          <li><a href="{{url('comisiones')}}">Comisiones <i class="fa fa-star" aria-hidden="true"></i></a></li>
                          @endif
                      @endif
                  </ul>
              </li>
              @endif


                <!--  <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Proyecto <i class="fa fa-usd"></i>

                  <span class="caret"></span></a>
                  <ul class="dropdown-menu">
                      @if(isset($permisos->contabilidad->pago_proveedores->ver))
                          @if($permisos->contabilidad->pago_proveedores->ver==='on')
                          <li><a href="{{url('tareas')}}">Mis Tareas <i class="fa fa-tasks" aria-hidden="true"></i></a></li>
                          @endif
                      @endif
                      @if(isset($permisos->contabilidad->listado_de_pagos_preparar->ver))
                          @if($permisos->contabilidad->listado_de_pagos_preparar->ver==='on')
                            <li><a href="{{url('tareas/asignadas')}}">Tareas Asignadas <i class="fa fa-share-square-o"></i></a></li>
                          @endif
                      @endif
                  </ul>
              </li>-->

              @if(isset($permisos->administrativo->centros_de_costo->ver) or isset($permisos->administrativo->proveedores->ver)
              or isset($permisos->administrativo->administracion_proveedores->ver) or isset($permisos->administrativo->contratos->ver)
              or isset($permisos->administrativo->seguridad_social->ver) or isset($permisos->administrativo->fuec->ver)
              or isset($permisos->administrativo->rutas_y_tarifas->ver) or isset($permisos->administrativo->ciudades->ver))
                <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">ADMIN <i class="fa fa-sliders"></i><span class="caret"></span></a>
                  <ul class="dropdown-menu multi-level">
                      @if(isset($permisos->administrativo->centros_de_costo->ver))
                          @if($permisos->administrativo->centros_de_costo->ver==='on')
                              <li><a href="{{url('centrodecosto')}}">Centros de costos <i class="fa fa-users"></i></a></li>
                          @endif
                      @endif
                    @if(isset($permisos->administrativo->proveedores->ver) or isset($permisos->administrativo->administracion_proveedores->ver)
                    or isset($permisos->administrativo->contratos->ver) or isset($permisos->administrativo->seguridad_social->ver) or isset($permisos->administrativo->fuec->ver))
                    <li class="dropdown-submenu">
                        <a class="dropdown-toggle" data-toggle="dropdown">Proveedores <i class="fa fa-car"></i></a>
                        <ul class="dropdown-menu">
                            @if(isset($permisos->administrativo->proveedores->ver))
                                @if($permisos->administrativo->proveedores->ver==='on')
                                    <li class="dropdown-submenu">
                                      <a href="{{url('proveedores')}}">Listado</a>
                                      <ul class="dropdown-menu">
                                          <li><a href="{{url('proveedores')}}">Proveedores <i class="fa fa-server" aria-hidden="true"></i></i></a></li>
                                          <li><a href="{{url('proveedores/listadoconductores')}}">Conductores <i class="fa fa-users"></i></a></li>
                                          <li><a href="{{url('proveedores/listadovehiculos')}}">Vehiculos <i class="fa fa-car"></i></a></li>
                                          @if(Auth::user()->id===2 or Auth::user()->id===4086 or Auth::user()->id===508 or Auth::user()->id===3801)
                                            <li><a href="{{url('proveedores/listadofotosconductores')}}">Fotos de Conductores <i class="fa fa-car"></i></a></li>
                                          @endif
                                      </ul>
                                    </li>
                                @endif
                            @endif
                            @if(isset($permisos->administrativo->administracion_proveedores->ver))
                                @if($permisos->administrativo->administracion_proveedores->ver==='on')
                                    <li><a href="{{url('proveedores/administracion')}}">Administracion <i class="fa fa-balance-scale" aria-hidden="true"></i></a></li>
                                @endif
                            @endif
                            @if(isset($permisos->administrativo->contratos->ver))
                                @if($permisos->administrativo->contratos->ver==='on')
                                    <li><a href="{{url('proveedores/contratos')}}">Contratos <i class="fa fa-book" aria-hidden="true"></i></a></li>
                                @endif
                            @endif
                            @if(isset($permisos->administrativo->seguridad_social->ver))
                                @if($permisos->administrativo->seguridad_social->ver==='on')
                                    <li><a href="{{url('proveedores/seguridadsocialciudades')}}">Seguridad Social <i class="fa fa-heart" aria-hidden="true"></i></a></li>
                                @endif
                            @endif
                            @if(isset($permisos->administrativo->fuec->ver))
                                @if($permisos->administrativo->fuec->ver==='on')
                                    <li><a href="{{url('fuec')}}">Fuec <i class="fa fa-map"></i></a></li>
                                @endif
                            @endif
                            @if(isset($permisos->administrativo->fuec->ver))
                                @if($permisos->administrativo->fuec->ver==='on')
                                  @if(Auth::user()->id==2)
                                    <li><a href="{{url('proveedores/usuarios')}}">Usuarios <i class="fa fa-user-plus"></i></a></li>
                                  @endif
                                @endif
                            @endif
                            <!-- COLOCAR PERMISOS DE DOCUMENTACIÓN VEHICULAR -->
                            @if(isset($permisos->administrativo->ciudades->ver))
                                @if($permisos->administrativo->ciudades->ver==='on' or Auth::user()->id_rol==28)
                                    <li><a href="{{url('proveedores/documentacionvehiculos')}}">Doc Vehículos <i class="fa fa-file"></i></a></li>
                                @endif
                            @endif
                            <!-- COLOCAR PERMISOS DE DOCUMENTACIÓN CONDUCTORES -->
                            @if(isset($permisos->administrativo->ciudades->ver))
                                @if($permisos->administrativo->ciudades->ver==='on' or Auth::user()->id_rol==17)
                                    <li><a href="{{url('proveedores/documentacionconductores')}}">Doc Conductores <i class="fa fa-file-o"></i></a></li>
                                @endif
                            @endif
                        </ul>
                    </li>
                    @endif
                    @if(isset($permisos->administrativo->rutas_y_tarifas->ver))
                        @if($permisos->administrativo->rutas_y_tarifas->ver==='on')

                            <li class="dropdown-submenu">
                        <a class="dropdown-toggle" data-toggle="dropdown">Tarifas Traslados <i class="fa fa-road" aria-hidden="true"></i></a>
                        <ul class="dropdown-menu">

                            <li><a href="{{url('tarifastraslados/trayectos')}}">Tarifas BAQ - Clientes <i class="fa fa-tasks" aria-hidden="true"></i></a></li>

                            <li><a href="{{url('tarifastraslados/trayectosproveedor')}}">Tarifas BAQ - Prov <i class="fa fa-share-square-o"></i></a></li>

                            <li><a href="{{url('tarifastraslados/trayectosbog')}}">Tarifas BOG - Clientes <i class="fa fa-share-square-o"></i></a></li>

                            <li><a href="{{url('tarifastraslados/trayectosproveedorbog')}}">Tarifas BOG - Prov <i class="fa fa-share-square-o"></i></a></li>

                        </ul>
                    </li>

                        @endif
                    @endif
                    @if(isset($permisos->administrativo->ciudades->ver))
                        @if($permisos->administrativo->ciudades->ver==='on')
                            <li><a href="{{url('ciudades')}}">Ciudades <i class="fa fa-globe"></i></a></li>
                        @endif
                    @endif
                  </ul>
                </li>
              @endif

              <!-- GI -->
              @if(isset($permisos->gestion_integral->indicadores->ver))
                <li class="dropdown">
                  <a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">GI <i class="fa fa-get-pocket"></i><span class="caret"></span></a>
                  <ul class="dropdown-menu multi-level">
                    @if($permisos->gestion_integral->indicadores->ver==='on')
                      <li><a href="{{url('gestionintegral')}}">Indicadores de Gestión <i class="fa fa-tachometer"></i></a></li>
                    @endif
                  </ul>
                </li>
              @endif
              <!-- END GI -->

              <!-- TALENTO HUMANO -->
              @if(isset($permisos->talentohumano->empleados->ver) or isset($permisos->talentohumano->prestamos->ver)
              or isset($permisos->talentohumano->vacaciones->ver) or isset($permisos->talentohumano->control_ingreso->ver) or isset($permisos->talentohumano->control_ingreso_bog->ver))
                <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">TH <i class="fa fa-user"></i><span class="caret"></span></a>
                  <ul class="dropdown-menu multi-level">
                      @if(isset($permisos->talentohumano->empleados->ver))
                          @if($permisos->talentohumano->empleados->ver==='on')
                              <li><a href="{{url('talentohumano/personaladministrativo')}}">Personal Administrativo <i class="fa fa-users"></i></a></li>
                          @endif
                      @endif
                      @if(isset($permisos->talentohumano->empleados->ver))
                          @if($permisos->talentohumano->empleados->ver==='on')
                              <li><a href="{{url('talentohumano/personaloperativo')}}">Personal Operativo <i class="fa fa-users"></i></a></li>
                          @endif
                      @endif
                      @if(isset($permisos->talentohumano->empleados->ver))
                          @if($permisos->talentohumano->empleados->ver==='on')
                              <li><a href="{{url('talentohumano/personalretirado')}}">Personal Retirado <i class="fa fa-users"></i></a></li>
                          @endif
                      @endif
                      @if(isset($permisos->talentohumano->prestamos->ver))
                          @if($permisos->talentohumano->prestamos->ver==='on')
                              <li><a href="{{url('talentohumano/solicitudesdeprestamos')}}">Gestión de Préstamos <i class="fa fa-money"></i></a></li>
                          @endif
                      @endif
                      @if(isset($permisos->talentohumano->vacaciones->ver))
                          @if($permisos->talentohumano->vacaciones->ver==='on')
                              <li><a href="{{url('talentohumano/vacaciones')}}">Vacaciones <i class="fa fa-plane"></i></a></li>
                          @endif
                      @endif

                      @if(isset($permisos->talentohumano->control_ingreso->ver))
                          @if($permisos->talentohumano->control_ingreso->ver==='on')
                              <li><a href="{{url('control')}}">Control de Ingreso BAQ <i class="fa fa-check"></i></a></li>
                          @endif
                      @endif

                      @if(isset($permisos->talentohumano->control_ingreso_bog->ver))
                          @if($permisos->talentohumano->control_ingreso_bog->ver==='on')
                              <li><a href="{{url('control/controlbog')}}">Control de Ingreso BOG <i class="fa fa-check"></i></a></li>
                          @endif
                      @endif
                  </ul>
                </li>
              @endif
              <!-- TALENTO HUMANO -->

              @if(isset($permisos->comercial->cotizaciones->ver))
                  @if($permisos->comercial->cotizaciones->ver==='on')
                  <li class="dropdown">
                      <a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Comercial <i class="fa fa-briefcase" aria-hidden="true"></i><span class="caret"></span></a>
                      <ul class="dropdown-menu">
                        @if(isset($permisos->comercial->cotizaciones->ver))
                            @if($permisos->comercial->cotizaciones->ver==='on')
                                <li><a href="{{url('cotizaciones/listado')}}">Cotizaciones <i class="fa fa-pencil-square-o" aria-hidden="true"></i></a></li>
                            @endif
                        @endif
                        @if(isset($permisos->comercial->cotizaciones->ver))
                            @if($permisos->comercial->cotizaciones->ver==='on')
                                <li><a href="{{url('reportes/listadopqr')}}">PQR <i class="fa fa-exclamation-circle" aria-hidden="true"></i></a></li>
                            @endif
                        @endif
                        @if(isset($permisos->comercial->cotizaciones->ver))
                            @if($permisos->comercial->cotizaciones->ver==='on')
                                <li><a href="{{url('reportes/portafoliosenviados')}}">Portafolio <i class="fa fa-file-text" aria-hidden="true"></i></a></li>
                            @endif
                        @endif
                        <!--@if(isset($permisos->comercial->cotizaciones->ver))
                            @if($permisos->comercial->cotizaciones->ver==='on')
                                <li><a href="{{url('reportes/pqr')}}">PQR - Historial <i class="fa fa-pencil-square-o" aria-hidden="true"></i></a></li>
                            @endif
                        @endif-->
                      </ul>
                  </li>
                  @endif
              @endif

              @if(isset($permisos->administracion->usuarios->ver) or isset($permisos->administracion->clientes_particulares->ver) or isset($permisos->administracion->clientes_empresariales->ver) or isset($permisos->administracion->importar_pasajeros->ver) or isset($permisos->administracion->listado_pasajeros->ver) or Auth::user()->id_empleado!=null)
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Usuarios <i class="fa fa-users"></i><span class="caret"></span></a>
                    <!-- new -->
                    <!-- new -->
                    <ul class="dropdown-menu">

                      <!-- new tareas -->
                      @if( Auth::user()->id_empleado!=null )
                      <li class="dropdown-submenu">
                        <a class="dropdown-toggle" data-toggle="dropdown">Tareas Usuarios <span style='font-size:15px;'>&#128240;</span></a>
                        <ul class="dropdown-menu">

                            <li><a href="{{url('tareas')}}">Mis Tareas <i class="fa fa-tasks" aria-hidden="true"></i></a></li>

                            <li><a href="{{url('tareas/asignadas')}}">Tareas Asignadas <i class="fa fa-share-square-o"></i></a></li>

                            @if(Auth::user()->id == 12 or Auth::user()->id == 3801 or Auth::user()->id == 2)
                              <li><a href="{{url('tareas/tasks')}}">Todas las Tareas <i class="fa fa-globe"></i></a></li>
                            @endif

                            <li><a href="{{url('tareas/generarproyecto')}}">Generar Informe &#128220</a></li>

                            <li><a href="{{url('tareas/adjuntarproyecto')}}">Adjuntar Proyecto &#128189</a></li>

                        </ul>
                    </li>
                    @endif
                      <!-- new -->

                      <!-- new usuarios -->
                      @if(isset($permisos->administracion->usuarios->ver))
                        @if($permisos->administracion->usuarios->ver==='on')
                        <li class="dropdown-submenu">
                          <a class="dropdown-toggle" data-toggle="dropdown">Listado Usuarios <span style='font-size:15px;'>&#129489;</span></a>
                          <ul class="dropdown-menu">
                              @if(isset($permisos->administracion->usuarios->ver))
                            @if($permisos->administracion->usuarios->ver==='on')
                                <li><a href="{{url('usuarios')}}">Listado <i class="fa fa-user" aria-hidden="true"></i></a></li>
                            @endif
                          @endif
                          @if(isset($permisos->administracion->clientes_particulares->ver))
                            @if($permisos->administracion->clientes_particulares->ver==='on')
                              <li><a href="{{url('usuarios/clientesparticulares')}}">Clientes particulares <i class="fa fa-user" aria-hidden="true"></i></a></li>
                            @endif
                          @endif
                          @if(isset($permisos->administracion->clientes_empresariales->ver))
                            @if($permisos->administracion->clientes_empresariales->ver==='on')
                              <li><a href="{{url('usuarios/clientesempresariales')}}">Clientes empresariales<i class="fa fa-user" aria-hidden="true"></i></a></li>
                            @endif
                          @endif
                          @if(isset($permisos->administracion->importar_pasajeros->ver))
                            @if($permisos->administracion->importar_pasajeros->ver==='on')
                              <li><a href="{{url('importarpasajeros')}}">Importar Usuarios <i class="fa fa-user"></i></a></li>
                            @endif
                          @endif
                          @if(isset($permisos->administracion->listado_pasajeros->ver))
                            @if($permisos->administracion->listado_pasajeros->ver==='on')
                              <li><a href="{{url('listadopasajeros')}}">Listado Clientes <i class="fa fa-user"></i></a></li>
                            @endif
                          @endif
                          </ul>
                      </li>
                      @endif
                    @endif
                      <!-- new usuarios -->


                    </ul>
                </li>
              @endif

              @if(isset($permisos->mobile->servicios_programados_sintarifa->ver) or isset($permisos->mobile->servicios_programados_tarifado->ver) or
                  isset($permisos->mobile->servicios_programados_pagados->ver) or isset($permisos->mobile->servicios_programados_facturacion->ver) or
                  isset($permisos->mobile->servicios_programados->ver))

                <?php

                  //$sin_tarifa = Servicioaplicacion ::sintarifa()->count();
                  //$pagados = Servicioaplicacion ::pagados()->count();

                  //$empresarial = Servicioaplicacion::pagofacturacion()->count();

                  /*$empresarial = Servicioaplicacion::whereRaw('(pago_facturacion = 1 or liquidacion_pendiente = 1)')
    							->whereNull('programado')
    							->whereNull('cancelado')
    							->count();*/

                  $empresarial = 0;
                  $pagados = 0;
                  $cancelados = 0;
                  $sin_tarifa = 0;
                  //$cancelados = Servicio::canceladoapp()->count();

                ?>

                <li class="dropdown">
                  <a href="#" class="dropdown-toggle usuario_nombre" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                      
                      <i class="fa-brands fa-upwork fa-shake fa-2xl"></i>
                      @if(($sin_tarifa+$pagados+$empresarial+$cancelados)>0)
                        <div class="badge_menu_head fontbulger servicios_mobile_badge">
                          {{$sin_tarifa+$pagados+$empresarial+$cancelados}}
                        </div>
                      @else
                        <div class="badge_menu_head servicios_mobile_badge">{{$sin_tarifa+$pagados+$empresarial+$cancelados}}</div>
                      @endif
                      <span class="caret"></span>
                  </a>
                  <ul class="dropdown-menu">
                    @if(isset($permisos->mobile->servicios_programados_sintarifa->ver))
                        @if($permisos->mobile->servicios_programados_sintarifa->ver==='on')
                          <li class="list_sin_tarifa" style="position: relative">
                            <a href="{{url('mobile/serviciosprogramadossintarifa')}}">
                              Sin tarifa
                              <i class="fa fa-car"></i>
                              @if ($sin_tarifa>0)
                                <div class="badge_menu">
                                  {{$sin_tarifa}}
                                </div>
                              @else
                                <div class="badge_menu hidden">
                                </div>
                              @endif
                            </a>
                          </li>
                        @endif
                    @endif

                    @if(isset($permisos->mobile->servicios_programados_tarifado->ver))
                        @if($permisos->mobile->servicios_programados_tarifado->ver==='on')
                          <li class="list_tarifados" style="position: relative">
                            <a href="{{url('mobile/serviciosprogramadostarifado')}}">Tarifados <i class="fa fa-car"></i></a>
                          </li>
                        @endif
                    @endif

                    @if(isset($permisos->mobile->servicios_programados_pagados->ver))
                        @if($permisos->mobile->servicios_programados_pagados->ver==='on')
                          <li class="list_pagados" style="position: relative">
                            <a href="{{url('mobile/serviciosprogramadospagados')}}">Pagados
                              <i class="fa fa-car"></i>
                              @if ($pagados>0)
                                <div class="badge_menu">
                                  {{$pagados}}
                                </div>
                              @else
                                <div class="badge_menu hidden">
                                </div>
                              @endif
                            </a>
                          </li>
                        @endif
                    @endif

                    @if(isset($permisos->mobile->servicios_programados_facturacion->ver))
                        @if($permisos->mobile->servicios_programados_facturacion->ver==='on')
                          <li class="list_empresariales" style="position: relative">
                            <a href="{{url('mobile/serviciosempresariales')}}">Empresariales
                              @if ($empresarial>0)
                                <div class="badge_menu">
                                  {{$empresarial}}
                                </div>
                              @else
                                <div class="badge_menu hidden">
                                </div>
                              @endif
                            </a>
                          </li>
                        @endif
                    @endif

                    @if(isset($permisos->mobile->servicios_programados->ver))
                        @if($permisos->mobile->servicios_programados->ver==='on')
          								<li class="dropdown-submenu">
          									<a class="dropdown-toggle" data-toggle="dropdown">Programados <i class="fa fa-road"></i></a>
          									<ul class="dropdown-menu">
                              <li><a href="{{url('mobile/serviciosprogramadosparticulares')}}">Particulares</a></li>
                              <li><a href="{{url('mobile/serviciosprogramados')}}">Cobro por facturacion</a></li>
                              <li><a href="{{url('mobile/serviciospendientesporliquidar')}}">Cobro por tarjeta de credito</a></li>
                            </ul>
                          </li>
                        @endif
                    @endif

                    @if(isset($permisos->mobile->servicios_programados_facturacion->ver))
                        @if($permisos->mobile->servicios_programados_facturacion->ver==='on')
                          <li class="dropdown-submenu">
          									<a class="dropdown-toggle" data-toggle="dropdown">Cancelados</a>
          									<ul class="dropdown-menu">
                              <li class="list_cancelados" style="position: relative">
                                <a href="{{url('mobile/servicioscancelados')}}">
                                  Cancelados sin programar
                                  @if ($cancelados>0)
                                    <div class="badge_menu">
                                      {{$cancelados}}
                                    </div>
                                  @else
                                    <div class="badge_menu hidden">
                                    </div>
                                  @endif
                                </a>
                              </li>
                              <li><a href="{{url('mobile/servicioscanceladosinprogramar')}}">Despues de programados</a></li>
                            </ul>
                          </li>
                        @endif
                    @endif

                  </ul>
                </li>
              @endif

              <li class="dropdown">
                <a href="#" class="dropdown-toggle usuario_nombre" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                    {{Auth::user()->first_name.' '.Auth::user()->last_name}}
                    <i class="fa-solid fa-user-gear fa-fade fa-xl"></i>
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                  <li><a href="{{url('usuarios/configuracion')}}">Configuracion <i class="fa fa-cog"></i></a></li>
                  <li><a href="{{url('logout')}}">Salir <i class="fa fa-power-off"></i></a></li>
                </ul>
              </li>
            </ul>
        </div><!-- /.navbar-collapse -->
      </div><!-- /.container-fluid -->
  </nav>
