<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern HTML Email Template</title>

    <style type="text/css">
    	body {
    		margin: 0;
    		background-color: #cccccc;
    	}
    	table {
    		border-spacing: 0;
    	}
    	td {
    		padding: 0;
    	}
    	img {
    		border: 0;
    	}

    	.tops {
			height: 33px;
			top: 53px;
			gap: 0px;
			border-radius: 6px 6px 6px 6px;
			opacity: 0px;
			background: #c03900;
		}

		.respu {
			font-family: Sansation;
			font-size: 24px;
			font-weight: 700;
			line-height: 31.48px;
			text-align: center;
			color: rgba(255, 255, 255, 1);

		}

		.footer-logo {
			width: 196.41px;
			height: 56.33px;
			top: 657px;
			left: 208px;
			gap: 0px;
			opacity: 0px;
			align: center;
		}

    	/*old*/
    	.preguntas {
    		margin-left: 10px; 
    		font-family: Sansation; 
    		font-size: 18px; 
    		font-weight: 700; 
    		line-height: 12.24px; 
    		text-align: right; 
    		color: #E04403;
    	}
    	.solicita {
    		font-family: Sansation; 
    		font-size: 18px; 
    		font-weight: 700; 
    		line-height: 12.24px; 
    		text-align: right; 
    		color: #E04403;
    	}

    	.lineas {
			
			left: calc(50% - 627px/2 + 10.5px);
			color: #232323;
			font-family: 'Sansation';
			font-style: italic;
			font-weight: 700;
			font-size: 11px;
			line-height: 102%;
			/* or 11px */
			text-align: center;	
		}
    	.names {
			font-family: Sansation;
			font-size: 45px;
			font-weight: 700;
			line-height: 10.56px;
			text-align: center;
			color: #E04403;

		}

		.asunto {
			font-family: Sansation;
			font-size: 18px;
			font-weight: 400;
			line-height: 28.96px;
			text-align: center;
			color: #232323;
		}

		.elegir {
			font-family: Sansation;
			font-size: 15px;
			font-weight: 400;
			line-height: 11.22px;
			text-align: center;
			color: black;
		}

    	.wrapper {
    		width: 100%;
    		table-layout: fixed;
    		background-color: #cccccc;
    		padding-bottom: 60px;
    	}
    	.main {
    		background-color: #FFFFFF;
    		margin: 0 auto;
    		width: 100%;
    		max-width: 600px;
    		border-spacing: 0;
    		font-family: Sansation;
    		color: #171a1b;
    	}

    	.two-columns {
    		text-align: center;
    		font-size: 0;

    	}
    	.two-columns .column {
    		width: 100%;
    		max-width: 300px;
    		display: inline-block;
    		vertical-align: top;
    		text-align: center;
    	}
    	.three-columns {
    		text-align: center;
    		font-size: 0;
    		padding: 5px 0 25px;
    	}
    	.three-columns .column {
    		width: 100%;
    		max-width: 300px;
    		display: inline-block;
    		vertical-align: top;
    		text-align: center;
    	}
    	.three-columns .padding {
    		padding: 1px;
    	}
    	.three-columns .content {
    		font-size: 15px;
    		line-height: 20px;
    		padding: 0 5px;
    	}
    	.button {
			background-color: rgba(224, 68, 3, 1);
			color: white;
			text-decoration: none;
			padding: 8px 20px;
			border-radius: 5px;
			border: 1px solid rgba(224, 68, 3, 1);
			font-weight: bold;
			font-size: 12px;
			margin-left: 40px;
		}

		.buttonr {
			background-color: rgba(255, 255, 255, 1);
			color: black;
			text-decoration: none;
			padding: 8px 20px;
			border-radius: 5px;
			border: 1px solid black;
			font-weight: bold;
			font-size: 12px;
		}

    </style>


</head>

<body>
	<center class="wrapper">
	
		<table class="main" width="100%">
		
			<!-- TOP BORDER -->
			<tr>
				<td>
					<table>
						<tr>
							<td>
								<img src="{{url('images/logo-header-white.png')}}" alt="" width="650" style="max-width: 100%;">
							</td>
						</tr>
					</table>
				</td>
			</tr>

			<!-- LOGO SECTION -->
				
			<!-- BANNER IMAGE -->

			<!-- THREE COLUMN SECTION -->
			<tr>
				<td>
					<table width="100%">
						<tr>
							<td>
								<p class="names">¡Bienvenido!</p>
								<p class="asunto">Reciban un cordial saludo de parte del equipo de <b>AOTOUR SAS.</b> <br><br>Gracias por confiar en nuestro trabajo, nos encargaremos de que vivas una experiencia de transporte a otro nivel.</p>
							</td>
						</tr>
						
						<br>
						
						<tr>
							<td>
								<img src="{{url('images/logo-bienvenida.png')}}" alt="" width="600" style="max-width: 100%;">
							</td>
						</tr>

					</table>
				</td>
			</tr>
			<!-- TITTLE, TEXT & BUTTON -->

			<!-- FOOTER SECTION -->
			<tr>
				<td>
					<table width="100%">
						<tr>
							<td style="text-align: center; padding: 15px 20px; color: #ffffff">
								<p class="lineas"><b style="color: #E04403">LÍNEAS DE ATENCIÓN:</b> Bogotá: (601) 358 5555 - Barranquilla: (605) 358 2555 - Nacional: 314 780 6060</p>
							</td>
						</tr>
					</table>
				</td>
			</tr>
	 </table>

	</center>

</body>
</html>