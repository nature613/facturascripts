<?php
/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2013  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'base/fs_model.php';
require_once 'model/agente.php';
require_once 'model/albaran_cliente.php';
require_once 'model/articulo.php';
require_once 'model/asiento.php';
require_once 'model/cliente.php';
require_once 'model/ejercicio.php';
require_once 'model/secuencia.php';

class linea_factura_cliente extends fs_model
{
   public $pvptotal;
   public $dtopor;
   public $recargo;
   public $irpf;
   public $pvpsindto;
   public $cantidad;
   public $codimpuesto;
   public $pvpunitario;
   public $idlinea;
   public $idfactura;
   public $idalbaran;
   public $descripcion;
   public $dtolineal;
   public $referencia;
   public $iva;
   
   private $codigo;
   private $fecha;
   private $factura_url;
   private $albaran_codigo;
   private $albaran_numero;
   private $albaran_url;
   private $articulo_url;
   
   private static $facturas;
   private static $albaranes;
   private static $articulos;
   
   public function __construct($l=FALSE)
   {
      parent::__construct('lineasfacturascli');
      
      if( !isset(self::$facturas) )
         self::$facturas = array();
      
      if( !isset(self::$albaranes) )
         self::$albaranes = array();
      
      if( !isset(self::$articulos) )
         self::$articulos = array();
      
      if($l)
      {
         $this->idlinea = $this->intval($l['idlinea']);
         $this->idfactura = $this->intval($l['idfactura']);
         $this->idalbaran = $this->intval($l['idalbaran']);
         $this->referencia = $l['referencia'];
         $this->descripcion = $l['descripcion'];
         $this->cantidad = floatval($l['cantidad']);
         $this->pvpunitario = floatval($l['pvpunitario']);
         $this->pvpsindto = floatval($l['pvpsindto']);
         $this->dtopor = floatval($l['dtopor']);
         $this->dtolineal = floatval($l['dtolineal']);
         $this->pvptotal = floatval($l['pvptotal']);
         $this->codimpuesto = $l['codimpuesto'];
         $this->iva = floatval($l['iva']);
         $this->recargo = floatval($l['recargo']);
         $this->irpf = floatval($l['irpf']);
      }
      else
      {
         $this->idlinea = NULL;
         $this->idfactura = NULL;
         $this->idalbaran = NULL;
         $this->referencia = '';
         $this->descripcion = '';
         $this->cantidad = 0;
         $this->pvpunitario = 0;
         $this->pvpsindto = 0;
         $this->dtopor = 0;
         $this->dtolineal = 0;
         $this->pvptotal = 0;
         $this->codimpuesto = NULL;
         $this->iva = 0;
         $this->recargo = 0;
         $this->irpf = 0;
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   private function fill()
   {
      $encontrado = FALSE;
      foreach(self::$facturas as $f)
      {
         if($f->idfactura == $this->idfactura)
         {
            $this->codigo = $f->codigo;
            $this->fecha = $f->fecha;
            $this->factura_url = $f->url();
            $encontrado = TRUE;
            break;
         }
      }
      if( !$encontrado )
      {
         $fac = new factura_cliente();
         $fac = $fac->get($this->idfactura);
         if($fac)
         {
            $this->codigo = $fac->codigo;
            $this->fecha = $fac->fecha;
            $this->factura_url = $fac->url();
            self::$facturas[] = $fac;
         }
      }
      
      $encontrado = FALSE;
      foreach(self::$albaranes as $a)
      {
         if($a->idalbaran == $this->idalbaran)
         {
            $this->albaran_codigo = $a->codigo;
            if( is_null($a->numero2) OR $a->numero2 == '')
               $this->albaran_numero = $a->numero;
            else
               $this->albaran_numero = $a->numero2;
            $this->albaran_url = $a->url();
            $encontrado = TRUE;
            break;
         }
      }
      if( !$encontrado )
      {
         $alb = new albaran_cliente();
         $alb = $alb->get($this->idalbaran);
         if($alb)
         {
            $this->albaran_codigo = $alb->codigo;
            if( is_null($alb->numero2) OR $alb->numero2 == '')
               $this->albaran_numero = $alb->numero;
            else
               $this->albaran_numero = $alb->numero2;
            $this->albaran_url = $alb->url();
            self::$albaranes[] = $alb;
         }
      }
      
      $encontrado = FALSE;
      foreach(self::$articulos as $a)
      {
         if($a->referencia == $this->referencia)
         {
            $this->articulo_url = $a->url();
            $encontrado = TRUE;
            break;
         }
      }
      if( !$encontrado )
      {
         $art = new articulo();
         $art = $art->get($this->referencia);
         if($art)
         {
            $this->articulo_url = $art->url();
            self::$articulos[] = $art;
         }
      }
   }
   
   public function show_pvp()
   {
      return number_format($this->pvpunitario, 2, '.', ' ');
   }
   
   public function show_dto()
   {
      return number_format($this->dtopor, 2, '.', ' ');
   }
   
   public function show_total()
   {
      return number_format($this->pvptotal, 2, '.', ' ');
   }
   
   public function show_total_iva()
   {
      return number_format($this->pvptotal*(100+$this->iva)/100, 2, '.', ' ');
   }
   
   public function show_codigo()
   {
      if( !isset($this->codigo) )
         $this->fill();
      return $this->codigo;
   }
   
   public function show_fecha()
   {
      if( !isset($this->fecha) )
         $this->fill();
      return $this->fecha;
   }
   
   public function url()
   {
      if( !isset($this->factura_url) )
         $this->fill();
      return $this->factura_url;
   }
   
   public function albaran_codigo()
   {
      if( !isset($this->albaran_codigo) )
         $this->fill();
      return $this->albaran_codigo;
   }
   
   public function albaran_url()
   {
      if( !isset($this->albaran_url) )
         $this->fill();
      return $this->albaran_url;
   }
   
   public function albaran_numero()
   {
      if( !isset($this->albaran_numero) )
         $this->fill();
      return $this->albaran_numero;
   }
   
   public function articulo_url()
   {
      if( !isset($this->articulo_url) )
         $this->fill();
      return $this->articulo_url;
   }
   
   public function exists()
   {
      if( is_null($this->idlinea) )
         return FALSE;
      else
         return $this->db->select("SELECT * FROM ".$this->table_name.
                 " WHERE idlinea = ".$this->var2str($this->idlinea).";");
   }
   
   public function new_idlinea()
   {
      $newid = $this->db->nextval($this->table_name.'_idlinea_seq');
      if($newid)
         $this->idlinea = intval($newid);
   }
   
   public function test()
   {
      $status = TRUE;
      
      $this->descripcion = $this->no_html($this->descripcion);
      $total = $this->pvpunitario * $this->cantidad * (100 - $this->dtopor) / 100;
      $totalsindto = $this->pvpunitario * $this->cantidad;
      
      if( !$this->floatcmp($this->pvptotal, $total) )
      {
         $this->new_error_msg("Error en el valor de pvptotal de la línea ".$this->referencia.
            " de la factura. Valor correcto: ".$total);
         $status = FALSE;
      }
      else if( !$this->floatcmp($this->pvpsindto, $totalsindto) )
      {
         $this->new_error_msg("Error en el valor de pvpsindto de la línea ".$this->referencia.
            " de la factura. Valor correcto: ".$totalsindto);
         $status = FALSE;
      }
      
      return $status;
   }
   
   public function save()
   {
      if( $this->test() )
      {
         if( $this->exists() )
         {
            $sql = "UPDATE ".$this->table_name." SET idfactura = ".$this->var2str($this->idfactura).",
               idalbaran = ".$this->var2str($this->idalbaran).", referencia = ".$this->var2str($this->referencia).",
               descripcion = ".$this->var2str($this->descripcion).", cantidad = ".$this->var2str($this->cantidad).",
               pvpunitario = ".$this->var2str($this->pvpunitario).", pvpsindto = ".$this->var2str($this->pvpsindto).",
               dtopor = ".$this->var2str($this->dtopor).", dtolineal = ".$this->var2str($this->dtolineal).",
               pvptotal = ".$this->var2str($this->pvptotal).", codimpuesto = ".$this->var2str($this->codimpuesto).",
               iva = ".$this->var2str($this->iva).", recargo = ".$this->var2str($this->recargo).",
               irpf = ".$this->var2str($this->irpf)." WHERE idlinea = ".$this->var2str($this->idlinea).";";
         }
         else
         {
            $this->new_idlinea();
            $sql = "INSERT INTO ".$this->table_name." (idlinea,idfactura,idalbaran,referencia,descripcion,cantidad,
               pvpunitario,pvpsindto,dtopor,dtolineal,pvptotal,codimpuesto,iva,recargo,irpf) VALUES
               (".$this->var2str($this->idlinea).",".$this->var2str($this->idfactura).",".$this->var2str($this->idalbaran).",
               ".$this->var2str($this->referencia).",".$this->var2str($this->descripcion).",".$this->var2str($this->cantidad).",
               ".$this->var2str($this->pvpunitario).",".$this->var2str($this->pvpsindto).",".$this->var2str($this->dtopor).",
               ".$this->var2str($this->dtolineal).",".$this->var2str($this->pvptotal).",".$this->var2str($this->codimpuesto).",
               ".$this->var2str($this->iva).",".$this->var2str($this->recargo).",".$this->var2str($this->irpf).");";
         }
         return $this->db->exec($sql);
      }
      else
         return FALSE;
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM ".$this->table_name." WHERE idlinea = ".$this->var2str($this->idlinea).";");
   }
   
   public function all_from_factura($id)
   {
      $linlist = array();
      $lineas = $this->db->select("SELECT * FROM ".$this->table_name."
         WHERE idfactura = ".$this->var2str($id)." ORDER BY idlinea ASC;");
      if($lineas)
      {
         foreach($lineas as $l)
            $linlist[] = new linea_factura_cliente($l);
      }
      return $linlist;
   }
   
   public function all_from_articulo($ref, $offset=0)
   {
      $linealist = array();
      $lineas = $this->db->select_limit("SELECT * FROM ".$this->table_name."
         WHERE referencia = ".$this->var2str($ref)." ORDER BY idalbaran DESC", FS_ITEM_LIMIT, $offset);
      if( $lineas )
      {
         foreach($lineas as $l)
            $linealist[] = new linea_factura_cliente($l);
      }
      return $linealist;
   }
   
   public function facturas_from_albaran($id)
   {
      $facturalist = array();
      $lineas = $this->db->select("SELECT DISTINCT idfactura FROM ".$this->table_name."
         WHERE idalbaran = ".$this->var2str($id).";");
      if($lineas)
      {
         $factura = new factura_cliente();
         foreach($lineas as $l)
            $facturalist[] = $factura->get( $l['idfactura'] );
      }
      return $facturalist;
   }
}


class linea_iva_factura_cliente extends fs_model
{
   public $totallinea;
   public $totalrecargo;
   public $recargo;
   public $totaliva;
   public $iva;
   public $codimpuesto;
   public $neto;
   public $idfactura;
   public $idlinea;
   
   public function __construct($l=FALSE)
   {
      parent::__construct('lineasivafactcli');
      if($l)
      {
         $this->idlinea = $this->intval($l['idlinea']);
         $this->idfactura = $this->intval($l['idfactura']);
         $this->neto = floatval($l['neto']);
         $this->codimpuesto = $l['codimpuesto'];
         $this->iva = floatval($l['iva']);
         $this->totaliva = floatval($l['totaliva']);
         $this->recargo = floatval($l['recargo']);
         $this->totalrecargo = floatval($l['totalrecargo']);
         $this->totallinea = floatval($l['totallinea']);
      }
      else
      {
         $this->idlinea = NULL;
         $this->idfactura = NULL;
         $this->neto = 0;
         $this->codimpuesto = NULL;
         $this->iva = 0;
         $this->totaliva = 0;
         $this->recargo = 0;
         $this->totalrecargo = 0;
         $this->totallinea = 0;
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function show_neto()
   {
      return number_format($this->neto, 2, '.', ' ');
   }
   
   public function show_iva()
   {
      return number_format($this->iva, 2, '.', ' ');
   }
   
   public function show_totaliva()
   {
      return number_format($this->totaliva, 2, '.', ' ');
   }
   
   public function show_total()
   {
      return number_format($this->totallinea, 2, '.', ' ');
   }
   
   public function exists()
   {
      if( is_null($this->idfactura) )
         return FALSE;
      else
         return $this->db->select("SELECT * FROM ".$this->table_name.
                 " WHERE idlinea = ".$this->var2str($this->idlinea).";");
   }
   
   public function test()
   {
      $status = TRUE;
      $totaliva = round($this->neto * $this->iva / 100, 2);
      $total = round($this->neto * (100 + $this->iva) / 100, 2);
      
      if( !$this->floatcmp($totaliva, round($this->totaliva, 2)) )
      {
         $this->new_error_msg("Error en el valor de totaliva de la línea de iva del impuesto ".
                 $this->codimpuesto." de la factura. Valor correcto: ".$totaliva);
         $status = FALSE;
      }
      else if( !$this->floatcmp($total, round($this->totallinea, 2)) )
      {
         $this->new_error_msg("Error en el valor de totallinea de la línea de iva del impuesto ".
                 $this->codimpuesto." de la factura. Valor correcto: ".$total);
         $status = FALSE;
      }
      
      return $status;
   }
   
   public function save()
   {
      if( $this->exists() )
      {
         $sql = "UPDATE ".$this->table_name." SET idfactura = ".$this->var2str($this->idfactura).",
            neto = ".$this->var2str($this->neto).", codimpuesto = ".$this->var2str($this->codimpuesto).",
            iva = ".$this->var2str($this->iva).", totaliva = ".$this->var2str($this->totaliva).",
            recargo = ".$this->var2str($this->recargo).", totalrecargo = ".$this->var2str($this->totalrecargo).",
            totallinea = ".$this->var2str($this->totallinea)." WHERE idlinea = ".$this->var2str($this->idlinea).";";
      }
      else
      {
         $sql = "INSERT INTO ".$this->table_name." (idfactura,neto,codimpuesto,iva,totaliva,recargo,totalrecargo,totallinea)
            VALUES (".$this->var2str($this->idfactura).",".$this->var2str($this->neto).",".$this->var2str($this->codimpuesto).",
            ".$this->var2str($this->iva).",".$this->var2str($this->totaliva).",".$this->var2str($this->recargo).",
            ".$this->var2str($this->totalrecargo).",".$this->var2str($this->totallinea).");";
      }
      return $this->db->exec($sql);
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM ".$this->table_name." WHERE idlinea = ".$this->var2str($this->idlinea).";");;
   }
   
   public function all_from_factura($id)
   {
      $linealist = array();
      $lineas = $this->db->select("SELECT * FROM ".$this->table_name." WHERE idfactura = ".$this->var2str($id).";");
      if($lineas)
      {
         foreach($lineas as $l)
            $linealist[] = new linea_iva_factura_cliente($l);
      }
      return $linealist;
   }
}


class factura_cliente extends fs_model
{
   public $idfactura;
   public $idasiento;
   public $idpagodevol;
   public $idfacturarect;
   public $codigo;
   public $numero;
   public $codigorect;
   public $codejercicio;
   public $codserie;
   public $codalmacen;
   public $codpago;
   public $coddivisa;
   public $fecha;
   public $hora;
   public $codcliente;
   public $nombrecliente;
   public $cifnif;
   public $direccion;
   public $ciudad;
   public $provincia;
   public $apartado;
   public $coddir;
   public $codpostal;
   public $codpais;
   public $codagente;
   public $neto;
   public $totaliva;
   public $total;
   public $totaleuros;
   public $irpf;
   public $totalirpf;
   public $porcomision;
   public $tasaconv;
   public $recfinanciero;
   public $totalrecargo;
   public $observaciones;
   public $deabono;
   public $automatica;
   public $editable;
   public $nogenerarasiento;

   public function __construct($f=FALSE)
   {
      parent::__construct('facturascli');
      if($f)
      {
         $this->idfactura = $this->intval($f['idfactura']);
         $this->idasiento = $this->intval($f['idasiento']);
         $this->idpagodevol = $this->intval($f['idpagodevol']);
         $this->idfacturarect = $this->intval($f['idfacturarect']);
         $this->codigo = $f['codigo'];
         $this->numero = $f['numero'];
         $this->codigorect = $f['codigorect'];
         $this->codejercicio = $f['codejercicio'];
         $this->codserie = $f['codserie'];
         $this->codalmacen = $f['codalmacen'];
         $this->codpago = $f['codpago'];
         $this->coddivisa = $f['coddivisa'];
         $this->fecha = Date('d-m-Y', strtotime($f['fecha']));
         if( is_null($f['hora']) )
            $this->hora = '00:00:00';
         else
            $this->hora = $f['hora'];
         $this->codcliente = $f['codcliente'];
         $this->nombrecliente = $f['nombrecliente'];
         $this->cifnif = $f['cifnif'];
         $this->direccion = $f['direccion'];
         $this->ciudad = $f['ciudad'];
         $this->provincia = $f['provincia'];
         $this->apartado = $f['apartado'];
         $this->coddir = $f['coddir'];
         $this->codpostal = $f['codpostal'];
         $this->codpais = $f['codpais'];
         $this->codagente = $f['codagente'];
         $this->neto = floatval($f['neto']);
         $this->totaliva = floatval($f['totaliva']);
         $this->total = floatval($f['total']);
         $this->totaleuros = floatval($f['totaleuros']);
         $this->irpf = floatval($f['irpf']);
         $this->totalirpf = floatval($f['totalirpf']);
         $this->porcomision = floatval($f['porcomision']);
         $this->tasaconv = floatval($f['tasaconv']);
         $this->recfinanciero = floatval($f['recfinanciero']);
         $this->totalrecargo = floatval($f['totalrecargo']);
         $this->observaciones = $this->no_html($f['observaciones']);
         $this->deabono = ($f['deabono'] == 't');
         $this->automatica = ($f['automatica'] == 't');
         $this->editable = ($f['editable'] == 't');
         $this->nogenerarasiento = ($f['nogenerarasiento'] == 't');
      }
      else
      {
         $this->idfactura = NULL;
         $this->idasiento = NULL;
         $this->idpagodevol = NULL;
         $this->idfacturarect = NULL;
         $this->codigo = NULL;
         $this->numero = NULL;
         $this->codigorect = NULL;
         $this->codejercicio = NULL;
         $this->codserie = NULL;
         $this->codalmacen = NULL;
         $this->codpago = NULL;
         $this->coddivisa = NULL;
         $this->fecha = Date('d-m-Y');
         $this->hora = Date('H:i:s');
         $this->codcliente = NULL;
         $this->nombrecliente = NULL;
         $this->cifnif = NULL;
         $this->direccion = NULL;
         $this->provincia = NULL;
         $this->ciudad = NULL;
         $this->apartado = NULL;
         $this->coddir = NULL;
         $this->codpostal = NULL;
         $this->codpais = NULL;
         $this->codagente = NULL;
         $this->neto = 0;
         $this->totaliva = 0;
         $this->total = 0;
         $this->totaleuros = 0;
         $this->irpf = 0;
         $this->totalirpf = 0;
         $this->porcomision = 0;
         $this->tasaconv = 1;
         $this->recfinanciero = 0;
         $this->totalrecargo = 0;
         $this->observaciones = NULL;
         $this->deabono = FALSE;
         $this->automatica = FALSE;
         $this->editable = TRUE;
         $this->nogenerarasiento = FALSE;
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function show_neto()
   {
      return number_format($this->neto, 2, '.', ' ');
   }
   
   public function show_iva()
   {
      return number_format($this->totaliva, 2, '.', ' ');
   }
   
   public function show_total()
   {
      return number_format($this->total, 2, '.', ' ');
   }
   
   public function observaciones_resume()
   {
      if($this->observaciones == '')
         return '-';
      else if( strlen($this->observaciones) < 60 )
         return $this->observaciones;
      else
         return substr($this->observaciones, 0, 50).'...';
   }
   
   public function url()
   {
      if( is_null($this->idfactura) )
         return 'index.php?page=contabilidad_facturas_cli';
      else
         return 'index.php?page=contabilidad_factura_cli&id='.$this->idfactura;
   }
   
   public function asiento_url()
   {
      $asiento = $this->get_asiento();
      if($asiento)
         return $asiento->url();
      else
         return '#';
   }
   
   public function agente_url()
   {
      $agente = $this->get_agente();
      if($agente)
         return $agente->url();
      else
         return '#';
   }
   
   public function cliente_url()
   {
      $cliente = new cliente();
      $cliente = $cliente->get($this->codcliente);
      if($cliente)
         return $cliente->url();
      else
         return '#';
   }
   
   public function get_agente()
   {
      $agente = new agente();
      return $agente->get($this->codagente);
   }
   
   public function get_asiento()
   {
      $asiento = new asiento();
      return $asiento->get($this->idasiento);
   }
   
   public function get_lineas()
   {
      $linea = new linea_factura_cliente();
      return $linea->all_from_factura($this->idfactura);
   }
   
   public function get_lineas_iva()
   {
      $linea_iva = new linea_iva_factura_cliente();
      $lineasi = $linea_iva->all_from_factura($this->idfactura);
      /// si no hay lineas de IVA las generamos
      if( !$lineasi )
      {
         $lineas = $this->get_lineas();
         if($lineas)
         {
            foreach($lineas as $l)
            {
               $i = 0;
               $encontrada = FALSE;
               while($i < count($lineasi))
               {
                  if($l->codimpuesto == $lineasi[$i]->codimpuesto)
                  {
                     $encontrada = TRUE;
                     $lineasi[$i]->neto += $l->pvptotal;
                     $lineasi[$i]->totaliva += ($l->pvptotal*$l->iva)/100;
                  }
                  $i++;
               }
               if( !$encontrada )
               {
                  $lineasi[$i] = new linea_iva_factura_cliente();
                  $lineasi[$i]->idfactura = $this->idfactura;
                  $lineasi[$i]->codimpuesto = $l->codimpuesto;
                  $lineasi[$i]->iva = $l->iva;
                  $lineasi[$i]->neto = $l->pvptotal;
                  $lineasi[$i]->totaliva = ($l->pvptotal*$l->iva)/100;
               }
            }
            /// redondeamos y guardamos
            foreach($lineasi as $li)
            {
               $li->neto = round($li->neto, 2);
               $li->totaliva = round($li->totaliva, 2);
               $li->totallinea = $li->neto + $li->totaliva;
               $li->save();
            }
         }
      }
      return $lineasi;
   }
   
   public function get($id)
   {
      $fact = $this->db->select("SELECT * FROM ".$this->table_name." WHERE idfactura = ".$this->var2str($id).";");
      if($fact)
         return new factura_cliente($fact[0]);
      else
         return FALSE;
   }
   
   public function get_by_codigo($cod)
   {
      $fact = $this->db->select("SELECT * FROM ".$this->table_name." WHERE codigo = ".$this->var2str($cod).";");
      if($fact)
         return new factura_cliente($fact[0]);
      else
         return FALSE;
   }
   
   public function exists()
   {
      if( is_null($this->idfactura) )
         return FALSE;
      else
         return $this->db->select("SELECT * FROM ".$this->table_name." WHERE idfactura = ".$this->var2str($this->idfactura).";");
   }
   
   public function new_idfactura()
   {
      $newid = $this->db->nextval($this->table_name.'_idfactura_seq');
      if($newid)
         $this->idfactura = intval($newid);
   }
   
   public function new_codigo()
   {
      /// buscamos un hueco
      $encontrado = FALSE;
      $num = 1;
      $fecha = $this->fecha;
      $numeros = $this->db->select("SELECT numero::integer,fecha FROM ".$this->table_name."
         WHERE codejercicio = ".$this->var2str($this->codejercicio).
         " AND codserie = ".$this->var2str($this->codserie)." ORDER BY numero ASC;");
      if( $numeros )
      {
         foreach($numeros as $n)
         {
            if( intval($n['numero']) != $num )
            {
               $encontrado = TRUE;
               $fecha = Date('d-m-Y', strtotime($n['fecha']));
               break;
            }
            else
               $num++;
         }
      }
      
      if( $encontrado )
      {
         $this->numero = $num;
         $this->fecha = $fecha;
      }
      else
      {
         $this->numero = $num;
         
         /// nos guardamos la secuencia para abanq/eneboo
         $sec = new secuencia();
         $sec = $sec->get_by_params2($this->codejercicio, $this->codserie, 'nfacturacli');
         if($sec)
         {
            if($sec->valorout <= $this->numero)
            {
               $sec->valorout = 1 + $this->numero;
               $sec->save();
            }
         }
      }
      
      $this->codigo = $this->codejercicio . sprintf('%02s', $this->codserie) . sprintf('%06s', $this->numero);
   }
   
   public function test()
   {
      $this->observaciones = $this->no_html($this->observaciones);
      $this->totaleuros = $this->total;
      
      return TRUE;
   }
   
   public function full_test()
   {
      $status = TRUE;
      
      /// comprobamos la fecha de la factura
      $numero0 = intval($this->numero)-1;
      if( $numero0 > 0 )
      {
         $codigo0 = $this->codejercicio . sprintf('%02s', $this->codserie) . sprintf('%06s', $numero0);
         $fac0 = $this->get_by_codigo($codigo0);
         if($fac0)
         {
            if( strtotime($fac0->fecha) > strtotime($this->fecha) )
            {
               $status = FALSE;
               $this->new_error_msg("La fecha de esta factura es anterior a la fecha de <a href='".
                       $fac0->url()."'>la factura anterior</a>.");
            }
         }
      }
      $numero2 = intval($this->numero)+1;
      $codigo2 = $this->codejercicio . sprintf('%02s', $this->codserie) . sprintf('%06s', $numero2);
      $fac2 = $this->get_by_codigo($codigo2);
      if($fac2)
      {
         if( strtotime($fac2->fecha) < strtotime($this->fecha) )
         {
            $status = FALSE;
            $this->new_error_msg("La fecha de esta factura es posterior a la fecha de <a href='".
                    $fac2->url()."'>la factura siguiente</a>.");
         }
      }
      
      /// comprobamos las líneas
      $neto = 0;
      $iva = 0;
      foreach($this->get_lineas() as $l)
      {
         if( !$l->test() )
            $status = FALSE;
         
         $neto += $l->pvptotal;
         $iva += $l->pvptotal * $l->iva / 100;
      }
      $neto = round($neto, 2);
      $iva = round($iva, 2);
      $total = $neto + $iva;
      
      if( !$this->floatcmp(round($this->neto, 2), $neto) )
      {
         $this->new_error_msg("Valor neto de la factura incorrecto. Valor correcto: ".$neto);
         $status = FALSE;
      }
      else if( !$this->floatcmp(round($this->totaliva, 2), $iva) )
      {
         $this->new_error_msg("Valor totaliva de la factura incorrecto. Valor correcto: ".$iva);
         $status = FALSE;
      }
      else if( !$this->floatcmp(round($this->total, 2), $total) )
      {
         $this->new_error_msg("Valor total de la factura incorrecto. Valor correcto: ".$total);
         $status = FALSE;
      }
      else if( !$this->floatcmp(round($this->totaleuros, 2), $total) )
      {
         $this->new_error_msg("Valor totaleuros de la factura incorrecto. Valor correcto: ".$total);
         $status = FALSE;
      }
      
      /// comprobamos las líneas de IVA
      $neto = 0;
      $iva = 0;
      foreach($this->get_lineas_iva() as $li)
      {
         if( !$li->test() )
            $status = FALSE;
         
         $neto += $li->neto;
         $iva += $li->totaliva;
      }
      $neto = round($neto, 2);
      $iva = round($iva, 2);
      $total = $neto + $iva;
      
      if( !$this->floatcmp(round($this->neto, 2), $neto) )
      {
         $this->new_error_msg("Valor neto incorrecto en las líneas de IVA. Valor correcto: ".$neto);
         $status = FALSE;
      }
      else if( !$this->floatcmp(round($this->totaliva, 2), $iva) )
      {
         $this->new_error_msg("Valor totaliva incorrecto en las líneas de IVA. Valor correcto: ".$iva);
         $status = FALSE;
      }
      else if( !$this->floatcmp(round($this->total, 2), $total) )
      {
         $this->new_error_msg("Valor total incorrecto en las líneas de IVA. Valor correcto: ".$total);
         $status = FALSE;
      }
      
      /// comprobamos el asiento
      if( !is_null($this->idasiento) )
      {
         $asiento = new asiento();
         $asiento = $asiento->get($this->idasiento);
         if( $asiento )
         {
            if($asiento->tipodocumento != 'Factura de cliente' OR $asiento->documento != $this->codigo)
            {
               $this->new_error_msg("Esta factura apunta a un <a href='".$this->asiento_url()."'>asiento incorrecto</a>.");
               $status = FALSE;
            }
         }
      }
      
      return $status;
   }
   
   public function save()
   {
      if( $this->test() )
      {
         $this->clean_cache();
         if( $this->exists() )
         {
            $sql = "UPDATE ".$this->table_name." SET idasiento = ".$this->var2str($this->idasiento).",
               idpagodevol = ".$this->var2str($this->idpagodevol).", idfacturarect = ".$this->var2str($this->idfacturarect).",
               codigo = ".$this->var2str($this->codigo).", numero = ".$this->var2str($this->numero).",
               codigorect = ".$this->var2str($this->codigorect).", codejercicio = ".$this->var2str($this->codejercicio).",
               codserie = ".$this->var2str($this->codserie).", codalmacen = ".$this->var2str($this->codalmacen).",
               codpago = ".$this->var2str($this->codpago).", coddivisa = ".$this->var2str($this->coddivisa).",
               fecha = ".$this->var2str($this->fecha).", codcliente = ".$this->var2str($this->codcliente).",
               nombrecliente = ".$this->var2str($this->nombrecliente).", cifnif = ".$this->var2str($this->cifnif).",
               direccion = ".$this->var2str($this->direccion).", ciudad = ".$this->var2str($this->ciudad).",
               provincia = ".$this->var2str($this->provincia).",
               apartado = ".$this->var2str($this->apartado).", coddir = ".$this->var2str($this->coddir).",
               codpostal = ".$this->var2str($this->codpostal).", codpais = ".$this->var2str($this->codpais).",
               codagente = ".$this->var2str($this->codagente).", neto = ".$this->var2str($this->neto).",
               totaliva = ".$this->var2str($this->totaliva).", total = ".$this->var2str($this->total).",
               totaleuros = ".$this->var2str($this->totaleuros).", irpf = ".$this->var2str($this->irpf).",
               totalirpf = ".$this->var2str($this->totalirpf).", porcomision = ".$this->var2str($this->porcomision).",
               tasaconv = ".$this->var2str($this->tasaconv).", recfinanciero = ".$this->var2str($this->recfinanciero).",
               totalrecargo = ".$this->var2str($this->totalrecargo).", observaciones = ".$this->var2str($this->observaciones).",
               deabono = ".$this->var2str($this->deabono).", automatica = ".$this->var2str($this->automatica).",
               editable = ".$this->var2str($this->editable).", nogenerarasiento = ".$this->var2str($this->nogenerarasiento).",
               hora = ".$this->var2str($this->hora)." WHERE idfactura = ".$this->var2str($this->idfactura).";";
         }
         else
         {
            $this->new_idfactura();
            $this->new_codigo();
            $sql = "INSERT INTO ".$this->table_name." (idfactura,idasiento,idpagodevol,idfacturarect,codigo,numero,
               codigorect,codejercicio,codserie,codalmacen,codpago,coddivisa,fecha,codcliente,nombrecliente,
               cifnif,direccion,ciudad,provincia,apartado,coddir,codpostal,codpais,codagente,neto,totaliva,total,totaleuros,
               irpf,totalirpf,porcomision,tasaconv,recfinanciero,totalrecargo,observaciones,deabono,automatica,editable,
               nogenerarasiento,hora) VALUES (".$this->var2str($this->idfactura).",".$this->var2str($this->idasiento).",
               ".$this->var2str($this->idpagodevol).",".$this->var2str($this->idfacturarect).",".$this->var2str($this->codigo).",
               ".$this->var2str($this->numero).",".$this->var2str($this->codigorect).",".$this->var2str($this->codejercicio).",
               ".$this->var2str($this->codserie).",".$this->var2str($this->codalmacen).",".$this->var2str($this->codpago).",
               ".$this->var2str($this->coddivisa).",".$this->var2str($this->fecha).",".$this->var2str($this->codcliente).",
               ".$this->var2str($this->nombrecliente).",".$this->var2str($this->cifnif).",".$this->var2str($this->direccion).",
               ".$this->var2str($this->ciudad).",".$this->var2str($this->provincia).",".$this->var2str($this->apartado).",
               ".$this->var2str($this->coddir).",
               ".$this->var2str($this->codpostal).",".$this->var2str($this->codpais).",
               ".$this->var2str($this->codagente).",".$this->var2str($this->neto).",".$this->var2str($this->totaliva).",
               ".$this->var2str($this->total).",".$this->var2str($this->totaleuros).",".$this->var2str($this->irpf).",
               ".$this->var2str($this->totalirpf).",".$this->var2str($this->porcomision).",".$this->var2str($this->tasaconv).",
               ".$this->var2str($this->recfinanciero).",".$this->var2str($this->totalrecargo).",".$this->var2str($this->observaciones).",
               ".$this->var2str($this->deabono).",".$this->var2str($this->automatica).",".$this->var2str($this->editable).",
               ".$this->var2str($this->nogenerarasiento).",".$this->var2str($this->hora).");";
         }
         return $this->db->exec($sql);
      }
      else
         return FALSE;
   }
   
   public function delete()
   {
      $this->clean_cache();
      
      /// eliminamos el asiento asociado
      $asiento = $this->get_asiento();
      if($asiento)
         $asiento->delete();
      
      /// desvinculamos el/los albaranes asociados
      $this->db->exec("UPDATE albaranescli SET idfactura = NULL, ptefactura = TRUE
         WHERE idfactura = ".$this->var2str($this->idfactura).";");
      
      /// eliminamos
      return $this->db->exec("DELETE FROM ".$this->table_name." WHERE idfactura = ".$this->var2str($this->idfactura).";");
   }
   
   private function clean_cache()
   {
      $this->cache->delete('factura_cliente_huecos');
   }
   
   public function all($offset=0, $limit=FS_ITEM_LIMIT)
   {
      $faclist = array();
      $facturas = $this->db->select_limit("SELECT * FROM ".$this->table_name.
         " ORDER BY fecha DESC, codigo DESC", $limit, $offset);
      if($facturas)
      {
         foreach($facturas as $f)
            $faclist[] = new factura_cliente($f);
      }
      return $faclist;
   }
   
   public function all_from_cliente($codcliente, $offset=0)
   {
      $faclist = array();
      $facturas = $this->db->select_limit("SELECT * FROM ".$this->table_name.
         " WHERE codcliente = ".$this->var2str($codcliente).
         " ORDER BY fecha DESC, codigo DESC", FS_ITEM_LIMIT, $offset);
      if($facturas)
      {
         foreach($facturas as $f)
            $faclist[] = new factura_cliente($f);
      }
      return $faclist;
   }
   
   public function all_from_mes($mes)
   {
      $faclist = array();
      $facturas = $this->db->select("SELECT * FROM ".$this->table_name.
         " WHERE to_char(fecha,'yyyy-mm') = ".$this->var2str($mes).
         " ORDER BY codigo ASC;");
      if($facturas)
      {
         foreach($facturas as $f)
            $faclist[] = new factura_cliente($f);
      }
      return $faclist;
   }
   
   public function all_desde($desde, $hasta)
   {
      $faclist = array();
      $facturas = $this->db->select("SELECT * FROM ".$this->table_name.
         " WHERE fecha >= ".$this->var2str($desde)." AND fecha <= ".$this->var2str($hasta).
         " ORDER BY codigo ASC;");
      if($facturas)
      {
         foreach($facturas as $f)
            $faclist[] = new factura_cliente($f);
      }
      return $faclist;
   }
   
   public function search($query, $offset=0)
   {
      $faclist = array();
      $query = strtolower( $this->no_html($query) );
      
      $consulta = "SELECT * FROM ".$this->table_name." WHERE ";
      if( is_numeric($query) )
         $consulta .= "codigo ~~ '%".$query."%' OR observaciones ~~ '%".$query."%'
            OR total BETWEEN ".($query-.01)." AND ".($query+.01);
      else if( preg_match('/^([0-9]{1,2})-([0-9]{1,2})-([0-9]{4})$/i', $query) )
         $consulta .= "fecha = '".$query."' OR observaciones ~~ '%".$query."%'";
      else
         $consulta .= "lower(codigo) ~~ '%".$query."%' OR lower(observaciones) ~~ '%".str_replace(' ', '%', $query)."%'";
      $consulta .= " ORDER BY fecha DESC, codigo DESC";
      
      $facturas = $this->db->select_limit($consulta, FS_ITEM_LIMIT, $offset);
      if($facturas)
      {
         foreach($facturas as $f)
            $faclist[] = new factura_cliente($f);
      }
      return $faclist;
   }
   
   public function huecos()
   {
      $error = TRUE;
      $huecolist = $this->cache->get_array2('factura_cliente_huecos', $error);
      if( $error )
      {
         $ejercicio = new ejercicio();
         foreach($ejercicio->all_abiertos() as $eje)
         {
            $codserie = '';
            $num = 1;
            $numeros = $this->db->select("SELECT codserie,numero::integer,fecha FROM ".$this->table_name.
               " WHERE codejercicio = ".$this->var2str($eje->codejercicio).
               " ORDER BY codserie ASC, numero ASC;");
            if( $numeros )
            {
               foreach($numeros as $n)
               {
                  if( $n['codserie'] != $codserie )
                  {
                     $codserie = $n['codserie'];
                     $num = 1;
                  }
                  
                  if( intval($n['numero']) != $num )
                  {
                     $huecolist[] = array(
                         'codigo' => $eje->codejercicio . sprintf('%02s', $codserie) . sprintf('%06s', $num),
                         'fecha' => Date('d-m-Y', strtotime($n['fecha']))
                     );
                  }
                  $num++;
               }
            }
         }
         $this->cache->set('factura_cliente_huecos', $huecolist, 86400);
      }
      return $huecolist;
   }
   
   public function meses()
   {
      $listam = array();
      $meses = $this->db->select("SELECT DISTINCT to_char(fecha,'yyyy-mm') as mes
         FROM ".$this->table_name." ORDER BY mes DESC;");
      if($meses)
      {
         foreach($meses as $m)
            $listam[] = $m['mes'];
      }
      return $listam;
   }
}

?>