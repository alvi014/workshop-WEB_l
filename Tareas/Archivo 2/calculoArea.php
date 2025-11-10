
<?php


/// Calculo de areas de figuras geometricas Alvaro Victor Zamora

echo "Area de un cuadrado de lado 10 es de: ". "<br>";

$lado= 10;
$area= ($lado * $lado);
echo "el Area del cuadrado es de: ". $area. "<br>" ;


echo "Area de un rectangulo: base 25 y altura 30 ". "<br>";
$base = 25;
$altura = 30;

$areaTriangulo= ($base * $altura);

echo "El area del rectangulo es de: ".$areaTriangulo. "<br>" ;



echo "Area de un triangulo: base 10 y altura 6 divido entre 2 ". "<br>";

$baseRec = 10;
$alturaRec = 6;


$areaRect = $baseRec*$alturaRec /2;
echo "El area del rectangulo es de: ".$areaRect. "<br>" ;



echo "Area de un Cirulo: radio 7 ". "<br>";
$radio = 7;

$areaCirc= pi()*pow($radio,2);


echo "El area del circulo es de: ".$areaCirc. "<br>" ;


echo "Area de un rombo con Diagonal mayor 10 y digaonal menor 6 ". "<br>";

$D = 10; 
$d = 6;  

$area = ($D * $d) / 2;

echo "El área del rombo con diagonales $D y $d es: $area";
?>



