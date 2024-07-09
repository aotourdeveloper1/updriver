<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upnet</title>
    <link href="{{url('images/logo.png')}}" rel="icon" type="image/x-icon" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <style>
        .divider:after,
            .divider:before {
            content: "";
            flex: 1;
            height: 1px;
            background: #eee;
            }
            .h-custom {
            height: calc(100% - 73px);
            }
            @media (max-width: 450px) {
            .h-custom {
            height: 100%;
            }
            }
    </style>
</head>
<body>
    Hola mundo
<section class="vh-100">
  <div class="container-fluid h-custom">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-md-9 col-lg-6 col-xl-5">
        <center><img src="{{url('/images/upnet.png')}}"
          class="img-fluid" alt="Sample image"></center>
      </div>
      <div class="col-md-6 col-lg-4 col-xl-4 offset-xl-1">
        <form>
          <!-- Email input -->
          <div class="form-outline mb-4">
            <input type="email" id="username" class="form-control form-control-lg"
              placeholder="Escribe tu usuario" />
            <label class="form-label" for="form3Example3">Usuario</label>
          </div>

          <!-- Password input -->
          <div class="form-outline mb-3">
            <input type="password" id="password" class="form-control form-control-lg"
              placeholder="Escribe tu contraseña" />
            <label class="form-label" for="form3Example4">Contraseña</label>
          </div>

          <div class="d-flex justify-content-between align-items-center">
            <!-- Checkbox -->
            <div class="form-check mb-0">
              <input class="form-check-input me-2" type="checkbox" value="" id="form2Example3" />
              <label class="form-check-label" for="form2Example3">
                Mantener la sesión
              </label>
            </div>
            <a href="#!" class="text-body">Olvidaste tu contraseña?</a>
          </div>

          <div class="text-center text-lg-start mt-4 pt-2">
            <button id="login" type="button" class="btn btn-primary btn-lg"
              style="padding-left: 2.5rem; padding-right: 2.5rem;">Ingresar</button>
              <!--<div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
                </div>-->
          </div>

        </form>
      </div>
      
    </div>
  </div>
  
</section>
@include('scripts.scripts')
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

    <script type="text/javascript">

        $('#login').click(function() {
            
            var username = $('#username').val();
            var password = $('#password').val();

            $.ajax({
                url: 'autenticate',
                method: 'post',
                data: {username: username, password: password, _token: "{{ csrf_token() }}",}
            }).done(function(data){

                if(data.respuesta==true){
                    location.reload();
                }else if(data.respuesta=='incorrecta'){
                  alert('La contraseña es incorrecta')
                }else if(data.respuesta=='false'){
                  alert('El usuario no existe')
                }else if(data.respuesta=='requeridos'){
                  alert('Usuario y contraseña requeridos')
                }

            });

        });

        $('#cerrar_sesion').click(function() {

            $.ajax({
                url: 'logout',
                method: 'post',
                data: {_token: "{{ csrf_token() }}",}
            }).done(function(data){

                if(data.respuesta==true){
                    location.reload();
                }else if(data.respuesta==false){

                }

            });

        });
        
    </script>
</html>