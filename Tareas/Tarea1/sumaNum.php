


<?php

/// Alvaro Josué Victor Zamora

/*Se requiere crear un programa en una página php que realice el cálculo de la suma de tres números que sean ingresados
 por medio de la url de la página. Por lo tanto, se requiere realizar el desarrollo y subir el código en un archivo PDF,
  con los datos mínimos de nombre del estudiante, instrucciones y código realizado.

Esta asignación debe ser entregada a más tardar el día 30/09/2025 antes de las 18:00 horas.
*/



$num1 = $_GET['num1'];
$num2 = $_GET['num2'];
$num3 = $_GET['num3'];
$resultado = ($num1 + $num2 + $num3);



Echo "La suma de $num1, $num2 y $num3 es: $resultado";


?>

