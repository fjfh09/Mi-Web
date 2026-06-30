<!DOCTYPE html>

<!-- Abrimos la etiqueta de la página -->
<html lang="es">


<!-- Definimos el head -->
<head>
	<meta charset="utf-8" />

<style>
body {background-image: url('https://educacionadistancia.juntadeandalucia.es/centros/granada/pluginfile.php/575269/mod_assign/introattachment/0/images/mifondo.jpg')}
</style>
</head>

<body>
	<header>
		<h1>MI PRIMERA WEB CON HTML5</h1>
	</header>
	


	<nav>
		<ul>
			<li><a href="contactar.html">Inicio</a></li>
		</ul>
	</nav>
	
<section>
		<article>
			<h3> RECEPCIÓN DE DATOS </h3>
		</article>
		<?php


			echo '------------DATOS GET-----------------';
			echo '<pre>';
					print_r($_GET);
			echo '</pre>'; 
			echo '------------DATOS POST-----------------';
			echo '<pre>';
					print_r($_POST);
			echo '</pre>'; 
			echo '--------------------------------------';
			/* 
			Nos vamos a centrar en los valores GET y POST, pero si queremos obtener más datos, navegador, variables de sesión podemos usar los valores $_SESSION, $_FILES y $_SERVER
			*/
			/*
			echo '<pre>';
					print_r($_SESSION);
			echo '</pre>'; 

			echo '--------------------------------------';
			echo '<pre>';
					print_r($_FILES);
			echo '</pre>'; 

			echo '--------------------------------------';
			echo '<pre>';
					print_r($_SERVER);
			echo '</pre>'; 
			echo '--------------------------------------';
			*/

		?>

	</section>
	
	<footer>
		<p> Píe de página contruido con HTML5 </p>
	</footer>
	
</body>

</html>