<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>


<script type="text/javascript">
   function comprobar_url()
   {
      $("#panel_generales").hide();
      $("#panel_email").hide();
      $("#panel_email2").hide();
      $("#panel_facturacion").hide();
      $("#panel_cuentas").hide();
      $("#panel_impresion").hide();
      $("#b_generales").removeClass('active');
      $("#b_email").removeClass('active');
      $("#b_facturacion").removeClass('active');
      $("#b_impresion").removeClass('active');
      
      if(window.location.hash.substring(1) == 'email')
      {
         $("#panel_email").show();
         $("#panel_email2").show();
         $("#b_email").addClass('active');
         document.f_empresa.email.focus();
      }
      else if(window.location.hash.substring(1) == 'facturacion')
      {
         $("#panel_facturacion").show();
         $("#panel_cuentas").show();
         $("#b_facturacion").addClass('active');
         document.f_empresa.coddivisa.focus();
      }
      else if(window.location.hash.substring(1) == 'impresion')
      {
         $("#panel_impresion").show();
         $("#b_impresion").addClass('active');
         document.f_empresa.pie_factura.focus();
      }
      else
      {
         $("#panel_generales").show();
         $("#b_generales").addClass('active');
         document.f_empresa.nombre.focus();
      }
   }
   $(document).ready(function() {
      comprobar_url();
      window.onpopstate = function() {
         comprobar_url();
      };
      $("#b_nueva_cuenta").click(function(event) {
         event.preventDefault();
         $("#modal_nueva_cuenta").modal('show');
         document.f_nueva_cuenta.descripcion.focus();
      });
      $("#b_add_logo").click(function(event) {
         event.preventDefault();
         $("#modal_logo").modal('show');
      });
   });
</script>

<div class="container-fluid">
   <div class="row">
      <div class="col-lg-2 col-md-2 col-sm-2">
         <div class="list-group">
            <a id="b_generales" href="#generales" class="list-group-item active">
               <span class="glyphicon glyphicon-dashboard"></span>
               &nbsp; Datos generales
            </a>
            <a id="b_email" href="#email" class="list-group-item">
               <span class="glyphicon glyphicon-inbox"></span>
               &nbsp; Email
            </a>
            <a id="b_facturacion" href="#facturacion" class="list-group-item">
               <span class="glyphicon glyphicon-euro"></span>
               &nbsp; Facturación
            </a>
            <a id="b_impresion" href="#impresion" class="list-group-item">
               <span class="glyphicon glyphicon-print"></span>
               &nbsp; Impresión
            </a>
         </div>
      </div>
      
      <div class="col-lg-10 col-md-10 col-sm-10">
         <form name="f_empresa" action="<?php echo $fsc->page->url();?>" method="post" class="form" role="form">
            <div class="panel panel-primary" id="panel_generales">
               <div class="panel-heading">
                  <h3 class="panel-title">Datos generales</h3>
               </div>
               <div class="panel-body">
                  <div class="form-group col-lg-6 col-md-6 col-sm-6">
                     Nombre:
                     <input class="form-control" type="text" name="nombre" value="<?php echo $fsc->empresa->nombre;?>" autocomplete="off" autofocus />
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-6">
                     Nombre Corto:
                     <input class="form-control" type="text" name="nombrecorto" value="<?php echo $fsc->empresa->nombrecorto;?>" autocomplete="off"/>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-6">
                     <?php  echo FS_CIFNIF;?>:
                     <input class="form-control" type="text" name="cifnif" value="<?php echo $fsc->empresa->cifnif;?>" autocomplete="off"/>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-6">
                     Administrador:
                     <input class="form-control" type="text" name="administrador" value="<?php echo $fsc->empresa->administrador;?>" autocomplete="off"/>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-6">
                     <a href="<?php echo $fsc->pais->url();?>">País</a>:
                     <select name="codpais" class="form-control">
                        <?php $loop_var1=$fsc->pais->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <option value="<?php echo $value1->codpais;?>"<?php if( $fsc->empresa->codpais == $value1->codpais ){ ?> selected="selected"<?php } ?>><?php echo $value1->nombre;?></option>
                        <?php } ?>

                     </select>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-6">
                     Provincia:
                     <input class="form-control" type="text" name="provincia" value="<?php echo $fsc->empresa->provincia;?>" autocomplete="off"/>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-6">
                     Ciudad:
                     <input class="form-control" type="text" name="ciudad" value="<?php echo $fsc->empresa->ciudad;?>" autocomplete="off"/>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-6">
                     Dirección:
                     <input class="form-control" type="text" name="direccion" value="<?php echo $fsc->empresa->direccion;?>" autocomplete="off"/>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-6">
                     Código Postal:
                     <input class="form-control" type="text" name="codpostal" value="<?php echo $fsc->empresa->codpostal;?>" autocomplete="off"/>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-6">
                     Teléfono:
                     <input class="form-control" type="text" name="telefono" value="<?php echo $fsc->empresa->telefono;?>" autocomplete="off"/>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-6">
                     Fax:
                     <input class="form-control" type="text" name="fax" value="<?php echo $fsc->empresa->fax;?>" autocomplete="off"/>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-6">
                     Web:
                     <input class="form-control" type="text" name="web" value="<?php echo $fsc->empresa->web;?>" autocomplete="off"/>
                  </div>
               </div>
               <div class="panel-footer text-right">
                  <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.submit();" title="Guardar" value="Guardar">
                     <span class="glyphicon glyphicon-floppy-disk"></span>
                     &nbsp; Guardar
                  </button>
               </div>
            </div>
            
            <div class="panel panel-primary" id="panel_email">
               <div class="panel-heading">
                  <h3 class="panel-title">Email</h3>
               </div>
               <div class="panel-body">
                  <div class="form-group col-lg-6 col-md-6 col-sm-6">
                     <label>Email:</label>
                     <input class="form-control" type="email" name="email" value="<?php echo $fsc->empresa->email;?>" autocomplete="off" autofocus />
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-6">
                     <label>Contraseña:</label>
                     <input class="form-control" type="password" name="email_password" value="<?php echo $fsc->empresa->email_password;?>" placeholder="Contraseña"/>
                  </div>
                  <div class="form-group">
                     <label>Firma:</label>
                     <textarea class="form-control" name="email_firma"><?php echo $fsc->empresa->email_firma;?></textarea>
                  </div>
               </div>
               <div class="panel-footer text-right">
                  <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.action='<?php echo $fsc->url();?>#email';this.form.submit();">
                     <span class="glyphicon glyphicon-floppy-disk"></span>
                     &nbsp; Guardar
                  </button>
               </div>
            </div>
            
            <div class="panel panel-warning" id="panel_email2">
               <div class="panel-heading">
                  <h3 class="panel-title">Si no usas Gmail o Google Apps, rellena también estos datos</h3>
               </div>
               <div class="panel-body">
                  <div class="form-group col-lg-6 col-md-6 col-sm-6">
                     <label>Host:</label>
                     <input class="form-control" type="text" name="mail_host" value="<?php echo $fsc->mail['mail_host'];?>" autocomplete="off" autofocus />
                  </div>
                  <div class="form-group col-lg-2 col-md-2 col-sm-2">
                     <label>Puerto:</label>
                     <input class="form-control" type="number" name="mail_port" value="<?php echo $fsc->mail['mail_port'];?>" autocomplete="off"/>
                  </div>
                  <div class="form-group col-lg-4 col-md-4 col-sm-4">
                     <label>Encriptación:</label>
                     <select name="mail_enc" class="form-control">
                        <option value="ssl">SSL</option>
                        <option value="tls"<?php if( $fsc->mail['mail_enc']=='tls' ){ ?> selected="selected"<?php } ?>>TLS</option>
                     </select>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-6">
                     <label>Usuario:</label>
                     <input class="form-control" type="text" name="mail_user" value="<?php echo $fsc->mail['mail_user'];?>" autocomplete="off"/>
                  </div>
                  <div class="col-lg-6 col-md-6 col-sm-6">
                     <br/>
                     <a href="http://www.facturascripts.com/community/item/como-configurar-tu-cuenta-de-hotmail-en-facturascripts-el-email-que-vas-a-886.html" target="_blank">¿Necesitas ayuda?</a>
                  </div>
               </div>
               <div class="panel-footer text-right">
                  <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.action='<?php echo $fsc->url();?>#email';this.form.submit();">
                     <span class="glyphicon glyphicon-floppy-disk"></span>
                     &nbsp; Guardar
                  </button>
               </div>
            </div>
            
            <div class="panel panel-primary" id="panel_facturacion">
               <div class="panel-heading">
                  <h3 class="panel-title">Facturación</h3>
               </div>
               <div class="panel-body">
                  <div class="form-group col-lg-4 col-md-4 col-sm-4">
                     <a href="<?php echo $fsc->divisa->url();?>">Divisa</a>:
                     <select name="coddivisa" class="form-control">
                     <?php $loop_var1=$fsc->divisa->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <option value="<?php echo $value1->coddivisa;?>"<?php if( $fsc->empresa->coddivisa == $value1->coddivisa ){ ?> selected="selected"<?php } ?>><?php echo $value1->descripcion;?></option>
                     <?php } ?>

                     </select>
                  </div>
                  <div class="form-group col-lg-4 col-md-4 col-sm-4">
                     <a href="<?php echo $fsc->ejercicio->url();?>">Ejercicio</a>:
                     <select name="codejercicio" class="form-control" autofocus >
                     <?php $loop_var1=$fsc->ejercicio->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <option value="<?php echo $value1->codejercicio;?>"<?php if( $fsc->empresa->codejercicio == $value1->codejercicio ){ ?> selected="selected"<?php } ?>><?php echo $value1->nombre;?></option>
                     <?php } ?>

                     </select>
                     <p class="help-block">Sólo sirve para inicializar algunos campos.</p>
                  </div>
                  <div class="form-group col-lg-4 col-md-4 col-sm-4">
                     <a href="<?php echo $fsc->serie->url();?>">Serie</a>:
                     <select name="codserie" class="form-control">
                     <?php $loop_var1=$fsc->serie->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <option value="<?php echo $value1->codserie;?>"<?php if( $fsc->empresa->codserie == $value1->codserie ){ ?> selected="selected"<?php } ?>><?php echo $value1->descripcion;?></option>
                     <?php } ?>

                     </select>
                     <p class="help-block">El IRPF se define en la serie.</p>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-6">
                     <label>
                        <input type="checkbox" name="recequivalencia" value="TRUE"<?php if( $fsc->empresa->recequivalencia ){ ?> checked="checked"<?php } ?>/>
                        Aplicar recargo de equivalencia
                     </label>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-6">
                     <label>
                        <input type="checkbox" name="contintegrada" value="TRUE"<?php if( $fsc->empresa->contintegrada ){ ?> checked="checked"<?php } ?>/>
                        Contabilidad integrada
                     </label>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-6">
                     <a href="<?php echo $fsc->forma_pago->url();?>">Forma de pago</a>:
                     <select name="codpago" class="form-control">
                     <?php $loop_var1=$fsc->forma_pago->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <option value="<?php echo $value1->codpago;?>"<?php if( $fsc->empresa->codpago == $value1->codpago ){ ?> selected="selected"<?php } ?>><?php echo $value1->descripcion;?></option>
                     <?php } ?>

                     </select>
                  </div>
                  <div class="form-group col-lg-6 col-md-6 col-sm-6">
                     <a href="<?php echo $fsc->almacen->url();?>">Almacén</a>:
                     <select name="codalmacen" class="form-control">
                     <?php $loop_var1=$fsc->almacen->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <option value="<?php echo $value1->codalmacen;?>"<?php if( $fsc->empresa->codalmacen == $value1->codalmacen ){ ?> selected="selected"<?php } ?>><?php echo $value1->nombre;?></option>
                     <?php } ?>

                     </select>
                  </div>
               </div>
               <div class="panel-footer" style="text-align: right;">
              <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.action='<?php echo $fsc->url();?>#facturacion';this.form.submit();">
                 <span class="glyphicon glyphicon-floppy-disk"></span>
                 &nbsp; Guardar
              </button>
               </div>
            </div>
            
            <div class="panel panel-primary" id="panel_impresion">
               <div class="panel-heading">
                  <h3 class="panel-title">Impresión</h3>
               </div>
               <div class="panel-body">
                  <div class="form-group">
                     <button class="btn btn-sm btn-default" id="b_add_logo">
                        <span class="glyphicon glyphicon-picture"></span>
                        Añadir / Cambiar logotipo a las facturas
                     </button>

                  <div style="max-width: 33%;" class="thumbnail">
                     <?php if( $fsc->logo ){ ?>

                     <img src="tmp/<?php  echo FS_TMP_NAME;?>logo.png" alt="logotipo"/>
                     <?php }else{ ?>

                     <?php } ?>

                  </div>

                  </div>
                  <div class="form-group">
                     Pie de página de la factura:
                     <input class="form-control" type="text" name="pie_factura" value="<?php echo $fsc->empresa->pie_factura;?>" autocomplete="off"/>
                  </div>
                  <div class="form-group">
                     Lema:
                     <input class="form-control" type="text" name="lema" value="<?php echo $fsc->empresa->lema;?>" autocomplete="off" autofocus />
                  </div>
                  <div class="form-group">
                     Horario:
                     <input class="form-control" type="text" name="horario" value="<?php echo $fsc->empresa->horario;?>" autocomplete="off"/>
                  </div>
               </div>
               <div class="panel-footer" style="text-align: right;">
              <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.action='<?php echo $fsc->url();?>#impresion';this.form.submit();">
                 <span class="glyphicon glyphicon-floppy-disk"></span>
                 &nbsp; Guardar
              </button>
               </div>
            </div>
         </form>


         
         <div class="panel-group" id="panel_cuentas">
            <?php $loop_var1=$fsc->cuenta_banco->all_from_empresa(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

            <form action="<?php echo $fsc->url();?>#facturacion" method="post" class="form">
               <input type="hidden" name="codcuenta" value="<?php echo $value1->codcuenta;?>"/>
               <div class="panel panel-info">
                  <div class="panel-heading">
                     <h3 class="panel-title">Cuenta bancaria #<?php echo $value1->codcuenta;?></h3>
                  </div>
                  <div class="panel-body">
                     <div class="form-group">
                        Descripción:
                        <input class="form-control" type="text" name="descripcion" value="<?php echo $value1->descripcion;?>" placeholder="Cuenta principal" autocomplete="off"/>
                     </div>
                     <div class="form-group col-lg-6">
                        <a target="_blank" href="http://es.wikipedia.org/wiki/International_Bank_Account_Number">IBAN</a>:
                        <input class="form-control" type="text" name="iban" value="<?php echo $value1->iban;?>" maxlength="28" placeholder="ES12345678901234567890123456" autocomplete="off"/>
                     </div>
                     <div class="form-group col-lg-6">
                        Calcular IBAN:
                        <input class="form-control" type="text" name="ciban" maxlength="20" placeholder="ENTIDAD SUCURSAL DC CUENTA" autocomplete="off"/>
                     </div>
                  </div>
                  <div class="panel-footer text-right">
                     <a class="btn btn-sm btn-danger pull-left" type="button" onclick="return confirm('¿Realmente desea eliminar esta Cuenta bancaria?');" href="<?php echo $fsc->url();?>&delete_cuenta=<?php echo $value1->codcuenta;?>#facturacion">
                         <span class="glyphicon glyphicon-trash"></span>
                         &nbsp; Eliminar
                     </a>
                     <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.action='<?php echo $fsc->url();?>#facturacion;this.form.submit();">
                         <span class="glyphicon glyphicon-floppy-disk"></span>
                         &nbsp; Guardar
                     </button>
                  </div>
               </div>
            </form>
            <?php } ?>

            <div class="panel panel-success">
               <div class="panel-heading">
                  <h3 class="panel-title">
                     <a id="b_nueva_cuenta" href="#">Nueva cuenta bancaria...</a>
                  </h3>
               </div>
            </div>
         </div>


      </div>
   </div>
</div>

<form enctype='multipart/form-data' action="<?php echo $fsc->url();?>#impresion" method="post">
   <input type='hidden' name='logo' value='TRUE'/>
   <div class="modal fade" id="modal_logo">
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
               <h4 class="modal-title">Logotipo para las facturas</h4>
            </div>
            <div class="modal-body">
               <div style="text-align: center;">
                  <div class="thumbnail">
                     <?php if( $fsc->logo ){ ?>

                     <img src="tmp/<?php  echo FS_TMP_NAME;?>logo.png" alt="logotipo"/>
                     <?php }else{ ?>

                     <div class="caption">
                        La imagen debe estar en formato PNG.
                     </div>
                     <?php } ?>

                  </div>
               </div>
               <div class="form-group">
                  <input class="form-control" name='fimagen' type='file' autofocus />
               </div>
            </div>
            <div class="modal-footer">
               <?php if( $fsc->logo ){ ?>

               <a class="btn btn-sm btn-danger" type="button" href="<?php echo $fsc->url();?>&delete_logo=TRUE#facturacion">
                  <span class="glyphicon glyphicon-trash"></span>
                  &nbsp; Eliminar
               </a>
               <?php } ?>

               <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.submit();">
                  <span class="glyphicon glyphicon-floppy-disk"></span>
                  &nbsp; Guardar
               </button>
            </div>
         </div>
      </div>
   </div>
</form>





<form name="f_nueva_cuenta" action="<?php echo $fsc->url();?>#facturacion" method="post" class="form">
   <div class="modal" id="modal_nueva_cuenta">
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
               <h4 class="modal-title">Nueva cuenta bancaria</h4>
            </div>
            <div class="modal-body">
               <div class="form-group">
                  Descripción:
                  <input class="form-control" type="text" name="descripcion" placeholder="Cuenta principal" autocomplete="off"/>
               </div>
               <div class="form-group">
                  <a target="_blank" href="http://es.wikipedia.org/wiki/International_Bank_Account_Number">IBAN</a>:
                  <input class="form-control" type="text" name="iban" maxlength="28" placeholder="ES12345678901234567890123456" autocomplete="off"/>
               </div>
               <div class="form-group">
                  Calcular IBAN:
                  <input class="form-control" type="text" name="ciban" maxlength="20" placeholder="ENTIDAD SUCURSAL DC CUENTA" autocomplete="off"/>
               </div>
            </div>
            <div class="modal-footer">
               <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.submit();">
                  <span class="glyphicon glyphicon-floppy-disk"></span>
                  &nbsp; Guardar
               </button>
            </div>
         </div>
      </div>
   </div>
</form>


<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>