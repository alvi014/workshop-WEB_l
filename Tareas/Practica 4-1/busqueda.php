<?php
// Archivo: funciones_busqueda.php

function buscarVehiculos(mysqli $conn, string $column, string $term) {
    

    $allowed_columns = ['placa', 'marca', 'modelo', 'año', 'tipo', 'id_vehiculo'];
    

    if (!in_array($column, $allowed_columns)) {
  
        $column = 'placa';
    }


    $clean_term = $conn->real_escape_string($term);
    
   
    if (empty($term)) {
   
        $where_clause = '';
    } else {
        
        $where_clause = " WHERE {$column} LIKE '%{$clean_term}%' ";
    }
    
   
    $sql = "SELECT * FROM vehiculos {$where_clause} ORDER BY id_vehiculo DESC";
    

    return $conn->query($sql);
}

