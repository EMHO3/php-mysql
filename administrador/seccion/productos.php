<?php include("../template/cabecera.php");?>

<?php 
$txtID=(isset($_POST['txtID']))? $_POST['txtID']:"";
$txtName=(isset($_POST['txtName']))? $_POST['txtName']:"";
$txtImagen=(isset($_FILES['txtImagen']['name']))? $_FILES['txtImagen']['name']:"";
$accion=(isset($_POST['accion']))? $_POST['accion']:"";

include("../config/bd.php");

switch ($accion) {
    case "Agregar":
        $sentenciaSQL=$conexion->prepare("INSERT INTO libros ( nombre, imagen) VALUES ( :nombre, :imagen);");
        $sentenciaSQL->bindParam(':nombre',$txtName);

        //agregar la  imagen del libro  a la carpeta imagenes del proyecto 
        $fecha=new DateTime();
        $nombreArchivo=($txtImagen!="")? $fecha->getTimestamp()."_".$_FILES['txtImagen']['name']:"imagen.jpg";
        $tmpImagen=$_FILES["txtImagen"]["tmp_name"];
        if ($tmpImagen!="") {
            move_uploaded_file($tmpImagen,"../../imagenes/".$nombreArchivo);
        }

        $sentenciaSQL->bindParam(':imagen',$nombreArchivo);
        $sentenciaSQL->execute();
        header("Location:productos.php");

        //INSERT INTO `libros` (`id`, `nombre`, `imagen`) VALUES (NULL, 'Libro basico sobre PHP', 'imagennn.jpg');
        break;

    case "Editar":
        $sentenciaSQL=$conexion->prepare("UPDATE  libros SET nombre=:nombre where id=:id");
        $sentenciaSQL->bindParam(':nombre',$txtName);
        $sentenciaSQL->bindParam(':id',$txtID);
        $sentenciaSQL->execute();

        if($txtImagen!=""){
            $fecha=new DateTime();
            $nombreArchivo=($txtImagen!="")? $fecha->getTimestamp()."_".$_FILES['txtImagen']['name']:"imagen.jpg";
            $tmpImagen=$_FILES["txtImagen"]["tmp_name"];
            move_uploaded_file($tmpImagen,"../../imagenes/".$nombreArchivo);

            $sentenciaSQL=$conexion->prepare("SELECT imagen FROM libros where id=:id");
            $sentenciaSQL->bindParam(':id',$txtID);
            $sentenciaSQL->execute();
            $libro=$sentenciaSQL->fetch(PDO::FETCH_LAZY);

            if (isset($libro["imagen"])&&($libro["imagen"]!="imagen.jpg") ) {
                if (file_exists("../../imagenes/".$libro["imagen"])) {
                    unlink("../../imagenes/".$libro["imagen"]);
                }
            }

            $sentenciaSQL=$conexion->prepare("UPDATE  libros SET imagen=:imagen where id=:id");
            $sentenciaSQL->bindParam(':imagen',$nombreArchivo);
            $sentenciaSQL->bindParam(':id',$txtID);
            $sentenciaSQL->execute();
            header("Location:productos.php");

        }
        break;  

    case "Cancelar":
        header("Location:productos.php");
        break;

    case "Seleccionar":
        $sentenciaSQL=$conexion->prepare("SELECT * FROM libros where id=:id");
        $sentenciaSQL->bindParam(':id',$txtID);
        $sentenciaSQL->execute();
        $libro=$sentenciaSQL->fetch(PDO::FETCH_LAZY);

        $txtName=$libro['nombre'];
        $txtImagen=$libro['imagen'];
        break;

    case "Borrar":
        //borrando la imagen de la carpeta del proyecto
        $sentenciaSQL=$conexion->prepare("SELECT imagen FROM libros where id=:id");
        $sentenciaSQL->bindParam(':id',$txtID);
        $sentenciaSQL->execute();
        $libro=$sentenciaSQL->fetch(PDO::FETCH_LAZY);
        if (isset($libro["imagen"])&&($libro["imagen"]!="imagen.jpg") ) {
            if (file_exists("../../imagenes/".$libro["imagen"])) {
                unlink("../../imagenes/".$libro["imagen"]);
            }
        }
        //borrando todos los datos 
        $sentenciaSQL=$conexion->prepare("DELETE  FROM libros WHERE id=:id");
        $sentenciaSQL->bindParam(':id',$txtID);
        $sentenciaSQL->execute();
        header("Location:productos.php");
 
        break;    

    default:
        # code...
        break;
}
$sentenciaSQL=$conexion->prepare("SELECT * FROM libros");
$sentenciaSQL->execute();
$listaLibros=$sentenciaSQL->fetchAll(PDO::FETCH_ASSOC);
?>


<div class="col-md-5">
    <div class="card">
        <div class="card-header">
            Datos de libro
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
            <div class = "form-group">
                <label for="txtID">ID:</label>
                <input type="text" required readonly  class="form-control" value="<?php echo $txtID;?>" name="txtID" id="txtID"  placeholder="ID">
            </div>
        
            <div class = "form-group">
                <label for="txtName">Nombre:</label>
                <input type="text" required class="form-control" value="<?php echo $txtName;?>" name="txtName" id="txtName"  placeholder="Nombre del libro">
            </div>
        
            <div class="form-group">
            <label for="txtImagen">Imagen</label>
            <br/>
            <?php
            if ($txtImagen!="") {?>
                <img class="img-thumbnail rounded" src="../../imagenes/<?php echo $txtImagen;?>" width="50px" height="50px">
            <?php } ?> 
            
            <input type="file" required class="form-control" name="txtImagen" id="txtImagen" placeholder="Imagen del libro">
            </div>
            
            <div class="btn-group" role="group" aria-label="">
                <button type="submit" name="accion" <?php echo ($accion=="Seleccionar") ? "disabled" : ""; ?> value="Agregar" class="btn btn-success">Agregar</button>
                <button type="submit" name="accion" <?php echo ($accion!=="Seleccionar") ? "disabled" : ""; ?> value="Editar" class="btn btn-warning">Editar</button>
                <button type="submit" name="accion" <?php echo ($accion!=="Seleccionar") ? "disabled" : ""; ?> value="Cancelar" class="btn btn-info">Cancelar</button>
            </div>
            </form>    
        </div>
    </div> 
</div>
<div class="col-md-7">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Imagen</th> 
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($listaLibros as $libro){ ?>
            <tr>
                <td><?php echo $libro['id'];?></td>
                <td><?php echo $libro['nombre'];?></td>
                <td>
                    <img src="../../imagenes/<?php echo $libro['imagen'];?>" width="50px" height="50px">
                </td>
                <td>
                    <form method="post">
                        <input type="hidden" name="txtID" id="txtID" value="<?php echo $libro['id']; ?>"/>
                        <input type="submit" name="accion" value="Borrar" class="btn btn-danger"/>
                        <input type="submit" name="accion" value="Seleccionar" class="btn btn-primary"/>

                    </form>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
<?php include("../template/pie.php");?>
