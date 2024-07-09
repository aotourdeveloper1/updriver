$(function(){

    /**
    * FUNCION PARA CREAR USUARIOS
    */
    $('#guardar').click(function(){

        var nombres = $('input[name="nombres"]').val().trim().toUpperCase();
        var apellidos = $('input[name="apellidos"]').val().trim().toUpperCase();
        var contrasena = $('input[name="contrasena"]').val();
        var repetir_contrasena = $('input[name="repetir_contrasena"]').val();
        var rol = $('#rol option:selected').val();
        var localidad = $('#localidad option:selected').val();
        
        if (nombres==='' || apellidos==='' || contrasena==='' || (contrasena!=repetir_contrasena) || rol=='0' || localidad=='0') {
            alert('Rellene todos los campos correctamente!');
        }else{

            formData = new FormData();
            formData.append('nombres',nombres);
            formData.append('apellidos',apellidos);
            formData.append('contrasena',contrasena);
            formData.append('rol',rol);
            formData.append('localidad',localidad);
            formData.append('_token', "{{ csrf_token() }}");

            $.ajax({
                url: 'crearusuario',
                data: formData,
                method: 'post',
                contentType: false,
                processData: false,
                success: function(data){
                    if (data.respuesta===true) {
                        alert('Usuario creado satisfactoriamente!');
                        location.reload();
                    }
                },
                error: function (request, status, error) {
                    alert('Hubo un error, llame al administrador del sistema'+request+status+error);
                }
            });
        }
    });

    /**
    * FUNCION PARA CREAR ROLES DE USUARIOS PREDEFINIDOS
    */

    $('#crear_rol').click(function(){

        var formData = new FormData($('#formulario_roles')[0]);
        var nombre_rol = $('input[name="nombre_rol"]').val().trim().toUpperCase();

        $.ajax({
            url: 'crearrol',
            data: formData,
            method: 'post',
            processData: false,
            contentType: false,
        }).done(function (data) {

            if (data.respuesta===true){
                location.reload();
            }else if(data.respuesta===false){

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
            }
        }).fail(function (data) {

        });
    });

    $('#ver_roles').click(function () {

      $('#tb_roles tbody').html('');

      $.ajax({
          url: 'verroles',
          method: 'post',
          data: {
            _token: "{{ csrf_token() }}"
          },
          dataType: 'json'
      }).done(function (data) {
          if (data.respuesta===true){

              $('#tb_roles').removeClass('hidden');

              for(i in data.roles){
                  $('#tb_roles tbody').append('<tr>' +
                      '<td>'+data.roles[i].nombre_rol.toUpperCase()+'</td>'+
                      '<td><a data-id="'+data.roles[i].id+'" data-toggle="modal" data-target=".mymodal4" class="btn btn-list-table btn-primary tb_roles_item">VER ROLES</a></td>'+
                      '<td>'+data.roles[i].first_name+' '+data.roles[i].last_name+' / '+data.roles[i].created_at+'</td>'+
                  '</tr>');
              }

          }else if(data.respuesta==='relogin'){
              location.reload();
          }
      }).fail(function () {

      });
    });

    $('#guardar_rol_usuario').click(function () {

        var id = $(this).attr('data-id');
        var rol = $('#rol_usuario').val();
        $.ajax({
            url: 'cambiarrolusuario',
            method: 'post',
            data: {
                id: id,
                rol: rol
            }
        }).done(function (data) {
            if (data.respuesta===true){
                location.reload();
            }
        }).fail(function () {

        });
    });

    $('#actualizar_rol').click(function () {

        var id = $(this).attr('data-id');
        var formData = new FormData($('#edicion_roles')[0]);
        formData.append('id',id);

        $.ajax({
            url: 'editarrol',
            method: 'post',
            contentType: false,
            processData: false,
            data: formData
        }).done(function (data) {
            if(data.respuesta===true){
                location.reload();
            }
        }).fail(function () {

        });

    });

    $('#tb_roles').on('click','.tb_roles_item', function () {

        var id = $(this).attr('data-id');

        $.ajax({
            url: 'permisosrol',
            method: 'post',
            data: {
                id: id
            },
            dataType: 'json'
        }).done(function (dataResponse) {

            if (dataResponse.respuesta===true){

                $('#actualizar_rol').attr('data-id',id);
                adata = JSON.parse(dataResponse.permisos);
                //rellenarRoles(data, '#edit_roles');
                $('#edit_roles .nombre_rol').val(dataResponse.nombre_rol.toUpperCase());

                for(var i in adata){
                    for(j in adata[i]){
                        for(k in adata[i][j]){
                            if(adata[i][j][k]==='on'){
                                $('#edit_roles input[name="'+i+'.'+j+'.'+k+'"]').bootstrapToggle('on');
                            }else {
                              $('#edit_roles input[name="'+i+'.'+j+'.'+k+'"]').bootstrapToggle('off');
                            }
                            console.log(i+'.'+j+'.'+k+'='+adata[i][j][k]);
                        }
                    }

                }
            }

        }).fail(function () {

        });
    });

    $('.modal-content').draggable({
        handle: ".modal-header"
    });

    $('#example').on('click', '.cambiar_contrasena', function(event) {
        $('.contenedor_informacion_usuario ').removeClass('hidden');
        var id = $(this).attr('data-id');
        $('#cambiar_contrasena').attr('data-id',id);
    });

    $('#example').on('click', '.banear_usuario', function () {

      var id = $(this).attr('data-id');
      var option = $(this).attr('data-option');

      $.ajax({
          url: 'usuarios/banearusuario',
          method: 'post',
          data: {
            id: id,
            option: option
          },
        dataType: 'json'
      }).done(function (data) {
        if(data.respuesta===true){
            alert(data.mensaje);
        }else if(data.respuesta==='relogin'){
          location.reload();
        }
      }).fail({

      });
    });

    $('#example').on('click', '.asignar_roles', function (e) {

        e.preventDefault();
        id = $(this).attr('data-id');
        $('.contenedor_informacion_rol').removeClass('hidden');

        $.ajax({
            url: 'verrolusuario',
            method: 'post',
            data: {
                id: id
            },
            dataType: 'json'
        }).done(function (data) {
            if (data.respuesta==true){

                $('#guardar_rol_usuario').attr('data-id',id);
                if (data.user.id_rol===null){
                    value = 0;
                }else{
                    value = data.user.id_rol;
                }
                $('#rol_usuario').val(value);

            }else if(data.respuesta==='relogin'){
                location.reload();
            }
        }).fail(function () {

        });
    });

    $('#cambiar_contrasena').click(function(){

      var id = $(this).attr('data-id');
      var contrasena = $('input[name="editar_contrasena"]').val();
      var repetir_contrasena = $('input[name="editar_repetir_contrasena"]').val();

      if(contrasena==='' || repetir_contrasena==='' || (contrasena!=repetir_contrasena)){
        alert('Rellene los campos correctamente!');
      }else{
        $.ajax({
          url: 'cambiarcontrasena',
          method: 'post',
          data: {
            'id': id,
            'contrasena': contrasena
          },
          type: 'json',
          success: function(data){
            if (data.respuesta===true) {
              alert('Realizado!');
              $('.contenedor_informacion_usuario').addClass('hidden');
            }else{
              alert('Ha ocurrido un error!');
            }
          },
        });
      }

    });

    $('#form_cambiar_contrasena').submit(function(e){

      e.preventDefault();

      var $form = $(this);

      var id = $form.find('button[type="submit"]').data('id');
      var password = $form.find('input[name="password"]').val();
      var confirm_password = $form.find('input[name="confirm_password"]').val();
      var url = $('meta[name="url"]').attr('content');

      if(password==='' || confirm_password==='' || (password!=confirm_password)){

        alert('Rellene los campos correctamente!');

      }else{

        var formData = new FormData($form[0]);
        formData.append('id', id);

        $.ajax({
          url: url+'/usuarios/cambiarcontrasenaapp',
          method: 'post',
          data: formData,
          contentType: false,
          processData: false,
          success: function(data){

            if (data.response===true) {

              alert('Realizado!');
              $('#modal_password').modal('hide');

            }else{

              alert('Ha ocurrido un error!');

            }

          },
        });
      }

    });

    $('#listado_clientes_movil_empresariales').on('click', '.cambiarcontrasena', function(e) {

      e.preventDefault();
      var $el = $(this);
      $('#form_cambiar_contrasena button[type="submit"]').attr('data-id', $el.data('id'));

    });

    $('.contenedor_informacion_usuario #cerrar_alerta').click(function(){
      $elemento = $(this).closest('.contenedor_informacion_usuario');
      $elemento.addClass('hidden');
    });

    $('.contenedor_informacion_rol #cerrar_alerta').click(function(){
        $elemento = $(this).closest('.contenedor_informacion_rol');
        $elemento.addClass('hidden');
    });

    $('.cerrar').click(function () {
      $('.errores-modal').addClass('hidden');
    });

    $('input[name="contrasena"], input[name="repetir_contrasena"]').keyup(function(){

        var contrasena = $('input[name="contrasena"]').val();
        var repetir_contrasena = $('input[name="repetir_contrasena"]').val();

        $div = $('.usuario_contrasena');

        if (contrasena!=repetir_contrasena) {
          $div.find('.glyphicon').addClass('glyphicon-remove').removeClass('glyphicon-ok');
          $div.find('.has-feedback').addClass('has-error').removeClass('has-success');
        }else{
          $div.find('.glyphicon').removeClass('glyphicon-remove').addClass('glyphicon-ok');
          $div.find('.has-feedback').addClass('has-success').removeClass('has-error');
        }
    });

    $('input[name="editar_contrasena"], input[name="editar_repetir_contrasena"]').keyup(function(){

        var contrasena = $('input[name="editar_contrasena"]').val();
        var repetir_contrasena = $('input[name="editar_repetir_contrasena"]').val();

        $div = $('.editar_contrasena');

        if (contrasena!=repetir_contrasena) {
          $div.find('.glyphicon').addClass('glyphicon-remove').removeClass('glyphicon-ok');
          $div.find('.has-feedback').addClass('has-error').removeClass('has-success');
        }else{
          $div.find('.glyphicon').removeClass('glyphicon-remove').addClass('glyphicon-ok');
          $div.find('.has-feedback').addClass('has-success').removeClass('has-error');
        }
    });

    $('select[name="centrodecosto"]').change(function(){

      url = $('meta[name="url"]').attr('content');
      var centrodecosto_id = $(this).val();

        $.ajax({
          url: url+'/transportes/mostrarsubcentros',
          type: 'post',
          data: {
            centrosdecosto_id: centrodecosto_id
          }
        })
        .done(function(data) {

          if (data.mensaje===true) {

            $('select[name="centrodecosto"]').next().addClass('hidden');

            var htmlVal = '';

            for (var i in data.respuesta) {

              htmlVal += '<option value="'+data.respuesta[i].id+'">'+data.respuesta[i].nombresubcentro+'</option>';

            }

            $('select[name="subcentrodecosto"]').html('').append('<option value="0">TODOS</option>'+htmlVal).removeAttr('disabled');

          }else if (data.mensaje===false) {

            $('select[name="subcentrodecosto"]').html().attr('disabled').addClass('disabled');

          }

        })
        .fail(function() {
          console.log("error");
        })
        .always(function() {
          console.log("complete");
        });

    });

    $('#listado_clientes_movil_empresariales').on('click', '.activacion', function(){

      var user_id = $(this).attr('data-id');

      $('#enlazar_centrodecosto button[type="submit"]').attr('data-id', user_id);

    });

    $('#enlazar_centrodecosto').submit(function(event) {

      event.preventDefault();
      var url = $('meta[name="url"]').attr('content');

      $('#enlazar_centrodecosto').find('button[type="submit"]').attr('disabled', 'disabled');

      var user_id = $(this).find('button[type="submit"]').attr('data-id');

      var centrodecosto_id = parseInt($('select[name="centrodecosto"]').val());
      var subcentrodecosto_id = parseInt($('select[name="subcentrodecosto"]').val());

      if (centrodecosto_id!=0) {

        $.ajax({
          url: url+'/usuarios/enlazarcentrodecosto',
          type: 'post',
          data: {
            user_id: user_id,
            centrodecosto_id: centrodecosto_id,
            subcentrodecosto_id: subcentrodecosto_id
          }
        })
        .done(function(data) {

          if (data.respuesta===true) {
            location.reload();
          }

        })
        .fail(function() {
          $('#enlazar_centrodecosto').find('button[type="submit"]').removeAttr('disabled', 'disabled');
        })
        .always(function() {
          console.log("complete");
        });

      }else {

        $('#enlazar_centrodecosto').removeAttr('disabled').removeClass('disabled');
        $('select[name="centrodecosto"]').next().text('Debe seleccionar un centro de costo').removeClass('hidden');

      }

    });

    $('#listado_clientes_movil_particulares').on('click', '.activar_cuenta', function(event) {

      event.preventDefault();

      var url = $('meta[name="url"]').attr('content');
      var user_id = $(this).attr('data-id-user');
      var $boton = $(this);

      $boton.attr('disabled','disabled').addClass('disabled');

      $.ajax({
        url: url+'/usuarios/activarcliente',
        type: 'post',
        dataType: 'json',
        data: {
          user_id: user_id
        }
      })
      .done(function(data) {

        if (data.response===true) {

          $boton.text('ACTIVADO').removeClass('btn-info').addClass('btn-success').removeClass('disabled').removeAttr('disabled');

        }else{

          $boton.removeAttr('disabled').removeClass('disabled');
          alert('Este usuario ya ha sido activado!');

        }

      })
      .fail(function() {
        console.log("error");
      })
      .always(function() {
        console.log("complete");
      });

    });

    $('#listado_clientes_movil_empresariales').on('click', '.bloquear', function(event) {

      event.preventDefault();

      var url = $('meta[name="url"]').attr('content');
      var $btn = $(this);
      var user_id = $btn.attr('data-id');

      $btn.html('<i class="fa fa-spin fa-spinner"></i>').removeClass('btn-danger btn-warning').addClass('btn-success').attr('disabled','disabled').addClass('disabled');

      $.ajax({
        url: url+'/usuarios/bloquear',
        type: 'post',
        dataType: 'json',
        data: {
          user_id: user_id
        }
      })
      .done(function(data) {

        if (data.response===true) {

          if (data.bloqueado==null) {

            $btn.text('BLOQUEAR').removeClass('btn-danger').addClass('btn-warning').removeClass('disabled').removeAttr('disabled');

          }else {

            $btn.text('DESBLOQUEAR').removeClass('btn-warning').addClass('btn-danger').removeClass('disabled').removeAttr('disabled');

          }

        }else{

          $boton.removeAttr('disabled').removeClass('disabled');
          alert('Este usuario ya ha sido activado!');

        }

      })
      .fail(function() {
        console.log("error");
      })
      .always(function() {
        console.log("complete");
      });

    });

    $('#listado_clientes_movil_empresariales').on('click', '.activado', function(event) {

      var user_id = $(this).data('id');
      var url = $('meta[name="url"]').attr('content');

      $('#actualizar_centrodecosto button[type="submit"]').attr('data-id', user_id);

      $.ajax({
        url: url+'/usuarios/traercentrosubcentro',
        type: 'get',
        dataType: 'json',
        data: {user_id: user_id}
      })
      .done(function(data, responseCode, responseText) {

        if (responseText.status==200) {

          var htmlVal = '';

          var subcentros = data.user.centrodecosto.subcentro;

          $('#actualizar_centrodecosto select[name="centrodecosto"]').val(data.user.centrodecosto_id);

          for (var i in subcentros) {

            htmlVal += '<option value="'+subcentros[i].id+'">'+subcentros[i].nombresubcentro+'</option>';

          }

          $('#actualizar_centrodecosto select[name="subcentrodecosto"]').html('').append('<option value="0">TODOS</option>'+htmlVal).removeAttr('disabled').removeClass('disabled');

          if (data.user.subcentrodecosto_id!=null) {
            $('#actualizar_centrodecosto select[name="subcentrodecosto"]').val(data.user.subcentrodecosto_id);
          }

          $('#activado_centrodecosto').modal('show');

        }

      })
      .fail(function(data, responseCode, responseText) {

        if (data.status==404) {


        }

      });


    });

    $('#actualizar_centrodecosto').submit(function(event) {

      event.preventDefault();

      var $form = $(this);

      var user_id = $form.find('button[type="submit"]').data('id');

      var url = $('meta[name="url"]').attr('content');

      var formData = new FormData($form[0]);
      formData.append('user_id', user_id);

      $.ajax({
        url: url+'/usuarios/actualizarcentrodeusuario',
        type: 'post',
        data: formData,
        processData: false,
        contentType: false
      })
      .done(function(data, responseCode, responseText) {

        if (responseText.status==200) {

          $('#activado_centrodecosto').modal('hide');
          alert('Realizado');

        }

      })
      .fail(function(data, responseCode, responseText) {

        if (data.status==404) {


        }

      });


    });

    if($('#listado_clientes_movil_particulares').length>0){

      $tableClientesParticulares = $('#listado_clientes_movil_particulares').DataTable({
          paging: false,
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
              { 'sWidth': '8%' },
              { 'sWidth': '8%' },
              { 'sWidth': '10%' },
              { 'sWidth': '6%' },
              { 'sWidth': '14%' }
          ],
          processing: true,
          "bProcessing": true
      });

    }

    if($('#listado_clientes_movil_empresariales').length>0){

      $tableClientesEmpresariales = $('#listado_clientes_movil_empresariales').DataTable({
          paging: false,
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
              { 'sWidth': '3%' },
              { 'sWidth': '10%' },
              { 'sWidth': '8%' },
              { 'sWidth': '8%' },
              { 'sWidth': '10%' },
              { 'sWidth': '10%' },
              { 'sWidth': '8%' },
              { 'sWidth': '6%' },
              { 'sWidth': '15%' }
          ],
          processing: true,
          "bProcessing": true
      });

    }

    $('#cambiar_contrasena').click(function(){

        var id = $(this).attr('data-id');
        var contrasena = $('input[name="editar_contrasena"]').val();
        var repetir_contrasena = $('input[name="editar_repetir_contrasena"]').val();

        if(contrasena==='' || repetir_contrasena==='' || (contrasena!=repetir_contrasena)){
            alert('Rellene los campos correctamente!');
        }else{
            $.ajax({
                url: 'cambiarcontrasena',
                method: 'post',
                data: {
                    'id': id,
                    'contrasena': contrasena
                },
                type: 'json',
                success: function(data){
                    if (data.respuesta===true) {
                        alert('Realizado!');
                        $('.contenedor_informacion_usuario').addClass('hidden');
                        location.reload();
                    }else{
                        alert('Ha ocurrido un error!');
                    }
                },
            });
        }

    });

    $('#contrasena').click(function () {
        $('.contenedor_informacion_usuario').removeClass('hidden');
    });

    $('#form_firma_usuario').submit(function(event) {

      event.preventDefault();

      var $form = $(this);
      var url = $('meta[name="url"]').attr('content');
      var formData = new FormData($form[0]);

      formData.append('nombre_completo', $form.find('input[name="nombre_completo"]').val().trim());
      formData.append('nombre_puesto', $form.find('input[name="nombre_puesto"]').val().trim());

      $.ajax({
        url: url+'/usuarios/crearfirma',
        method: 'post',
        data: formData,
        processData: false,
        contentType: false
      }).done(function(data){

        if(data.response==true){

          location.reload();

        }else if(data.response==false){

          var erroresHtml = '';

          for (var i in data.errores) {
            erroresHtml += data.errores[i]+'<br>';
          }

          $.alert({
              title: 'Autonet',
              content: erroresHtml,
          });

        }

      });

    });

});
