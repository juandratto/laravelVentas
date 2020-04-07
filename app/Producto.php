<?php

namespace sisVentas;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table='producto';

    protected $primaryKey='idproducto';

    #public $timestamps=false;

    protected $fillable= [
    		'idcategoria',
    		'codigo',
    		'nombre',
    		'stock',
    		'descripcion',
    		'imagen',
            'estado',
            'unidad'
    ];

    protected $guarded=[

    ];
    //
}
