<!DOCTYPE html>
<html><head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title></title>
    <link href="https://fonts.googleapis.com/css?family=Raleway:300,400,500,600" rel="stylesheet">
    <style>

      body{
        font-family: 'Raleway', sans-serif;
        font-size: 14px;
      }

      #header{
        font-size: 12px;
      }

      .title-table{
        padding: 5px;
        background: #dadada;
        text-align: center;
        font-size: 10px;
        font-weight: 500;
      }

      #servicios tbody td{
        background: #efefef;
        padding: 10px;
        text-align: center;
        font-size: 12px;
      }

      #pasajeros tbody td{
        background: #efefef;
        padding: 10px;
        text-align: center;
        font-size: 12px;
      }

      .number_table{
        width: 10px;
        background: #dadada !important;
        border-right: 1px solid white;
        border-bottom: 1px solid white;
      }

      .pax_name_table{
        border-bottom: 1px solid white;
      }

      #client {
        padding-left: 6px;
        border-left: 6px solid #F47321;
        float: left;
        margin-bottom: 30px;
      }

      h3.name {
        font-weight: 500;
        font-size: 20px;
        margin: 0;
      }

      #informacion{
        table-layout: auto;
        empty-cells: show;
        border-collapse: collapse;
        margin-bottom: 15px;
        margin-top: 5px;
        width: 100%;
      }

      #informacion_conductor{
        table-layout: auto;
        empty-cells: show;
        border-collapse: collapse;
        margin-bottom: 10px;
        margin-top: 15px;
        width: 100%;
      }

      #informacion tr td, #informacion_conductor tr td{
        padding: 5px;
        font-size: 12px;
      }

      .div_info{
        padding: 5px;
        border-left: 1px solid #5a5a5a;
        border-right: 1px solid #5a5a5a;
        border-bottom: 1px solid #5a5a5a;
      }

    </style>
  </head><body>
      <table id="header" style="width:100%;table-layout:auto;empty-cells:show; border: 1px solid #5a5a5a; border-collapse: collapse;">
        <tbody>
          <tr>
            <td width="20" style="padding: 20px; border-right: 1px solid #5a5a5a;"><img width="120" src="biblioteca_imagenes/logos.png"></td>
            <td width="280" align="center">Formato</br> Constancia de Prestación de Servicios # <span style="color: #e26211">{{$servicio->id}}</span></td>
            <td width="120">
              <table style="width:100%; border-collapse: collapse;">
                <tr>
                  <td style="border-right: 1px solid #5a5a5a; border-left: 1px solid #5a5a5a; border-bottom: 1px solid #5a5a5a; padding: 5px;">Codigo</td>
                  <td  align="center" style="border-bottom: 1px solid #5a5a5a;">FM-CM-12</td>
                </tr>
                <tr>
                  <td style="border-right: 1px solid #5a5a5a; border-left: 1px solid #5a5a5a; border-bottom: 1px solid #5a5a5a; padding: 5px;">Version</td>
                  <td align="center" style="border-bottom: 1px solid black;">2</td>
                </tr>
                <tr>
                  <td style="border-right: 1px solid #5a5a5a; border-left: 1px solid #5a5a5a; border-collapse: collapse; padding: 5px;">Fecha</td>
                  <td align="center">07/07/2017</td>
                </tr>
              </table>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="div_info">

        <table id="informacion">
          <tbody>
            <tr>
              <td style="width: 42px">Ciudad:</td>
              <td style="border-bottom: 1px solid #5a5a5a; width: 70px">{{ucwords(strtolower($servicio->ciudad))}}</td>
              <td style="width: 35px">Fecha:</td>
              <td style="border-bottom: 1px solid #5a5a5a; width: 45px">{{$servicio->fecha_viaje}}</td>
              <td style="width: 80px">Empresa Solicitante:</td>
              <td style="border-bottom: 1px solid #5a5a5a; width: 110px">{{ucwords(strtolower($servicio->id))}}</td>
            </tr>
          </tbody>
        </table>

        <table>
          <tbody>
            <tr>
              <td style="padding: 5px; font-size: 12px;">Nombre Pasajero(s):</td>
            </tr>
          </tbody>
        </table>

        <table id="pasajeros" style="width:100%;table-layout:auto;empty-cells:show; border-collapse: collapse; margin-bottom: 15px;">
          <tbody>

          </tbody>
        </table>

        <table>
          <tbody>
            <tr>
              <td style="padding: 5px; font-size: 12px;">Detalles del servicio</td>
            </tr>
          </tbody>
        </table>

        <table id="servicios" style="width:100%;table-layout:auto;empty-cells:show; border-collapse: collapse;">
          <thead>
            <tr>
              <th class="title-table" style="border-right: 1px solid white; width: 80px">FECHA</th>
              <th class="title-table" style="border-right: 1px solid white; width: 50px">HORA INICIO</th>
              <th class="title-table" style="border-right: 1px solid white; width: 50px">HORA FINALIZACION</th>
              <th class="title-table">DESCRIPCION</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td style="border-right: 1px solid white">{{$servicio->fecha_viaje}}</td>
              <td style="border-right: 1px solid white">{{substr($servicio->hora_inicio, -8, 5)}}</td>
              <td style="border-right: 1px solid white">{{substr($servicio->hora_finalizado, -8, 5)}}</td>
              <td>{{ucwords(strtolower($servicio->id)).' - '.ucwords(strtolower($servicio->id))}}</td>
            </tr>
          </tbody>
        </table>

        <table id="informacion_conductor">
          <tbody>
            <tr>
              <td style="width: 105px">Conductor Asignado:</td>
              <td style="border-bottom: 1px solid #5a5a5a; width: 155px">{{ucwords(strtolower($servicio->id))}}</td>
              <td style="width: 85px">Tipo de Vehiculo:</td>
              <td style="border-bottom: 1px solid #5a5a5a; width: 85px">{{ucwords(strtolower($servicio->id))}}</td>
              <td style="width: 35px">Placa:</td>
              <td style="border-bottom: 1px solid #5a5a5a; width: 45px">{{$servicio->id}}</td>
            </tr>
          </tbody>
        </table>

        <table id="informacion_conductor">
          <tbody>
            <tr>
              <td style="width: 150px">Calidad del Servicio Prestado:</td>

                <td style="width: 70px">
                  Excelente: <span style="border-bottom: 1px solid #5a5a5a;"></span>
                </td>
              

            </tr>
            <tr>
              <td style="width: 150px">Actitud de Servicio del Conductor:</td>

                <td style="width: 70px">
                  Excelente: <span style="border-bottom: 1px solid #5a5a5a;"></span>
                </td>

            </tr>
          </tbody>
        </table>

        <div style="width: 100%; min-height: 100px; background: #efefef; font-size: 12px; padding: 5px;">
        </div>

        <table>
          <tbody>
            <tr>
              <td style="padding: 5px; font-size: 12px;">Firma del pasajero</td>
            </tr>
          </tbody>
        </table>

        <div style="width: 100%; height: 150px; background: #efefef; padding: 5px;">
          
        </div>

        <p style="font-size: 10px; margin-top: 0px;">Cualquier comentario o sugerencia adicional del servicio prestado por favor escribir al correo electronico: servicios@aotour.com.co</p>
        <table>
          <tbody>
            <tr>
              <td><img style="width: 80px;" src="img/calidad_iconos.png" alt=""></td>
              <td><img style="width: 100px;" src="img/logo-supertransporte.png" alt=""></td>
            </tr>
          </tbody>
        </table>
      </div>

  </body></html>
