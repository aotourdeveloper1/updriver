<ol style="margin-bottom: 10px" class="breadcrumb">
    @if(isset($permisos->administrativo->proveedores->ver))
        @if($permisos->administrativo->proveedores->ver==='on')
            <li><a href="{{url('proveedores')}}">Listado</a></li>
        @endif
    @endif
    @if(isset($permisos->administrativo->proveedores->ver))
        @if($permisos->administrativo->proveedores->ver==='on')
            <li><a href="{{url('proveedores/nuevosproveedores')}}">Nuevos Proveedores</a></li>
        @endif
    @endif
    @if(isset($permisos->administrativo->proveedores->ver))
        @if($permisos->administrativo->proveedores->ver==='on')
            <li><a href="{{url('proveedores/proveedoreventual')}}">Proveedor Eventual </a></li>
        @endif
    @endif
    @if(isset($permisos->administrativo->administracion_proveedores->ver))
        @if($permisos->administrativo->administracion_proveedores->ver==='on')
            <li><a href="{{url('proveedores/administracion')}}">Administracion</a></li>
        @endif
    @endif
    @if(isset($permisos->administrativo->contratos->ver))
        @if($permisos->administrativo->contratos->ver==='on')
            <li><a href="{{url('proveedores/contratos')}}">Contratos</a></li>
        @endif
    @endif    
    @if(isset($permisos->administrativo->seguridad_social->ver))
        @if($permisos->administrativo->seguridad_social->ver==='on')
            <li><a href="{{url('proveedores/seguridadsocialciudades')}}">Seguridad Social</a></li>
        @endif
    @endif
    @if(Auth::user()->id_rol==2 or Auth::user()->id_rol==1 or Auth::user()->id_rol==25 or Auth::user()->id_rol==46 or Auth::user()->id_rol==5 or Auth::user()->id_rol==52)
        <li><a href="{{url('proveedores/gestiondecuentas')}}">Gestión de cuentas</a></li>
    @endif
</ol>