<?php
require_once('webcamConnectionClass.php');
class webcamClass extends connectionClass{
    private $imageFolder = "/var/www/maequeacolhe.com.br/httpdocs/sistema/public/uploads/acolhidos/fotos/";
    // private $imageFolder = "../../public/uploads/acolhidos/fotos/";
    
    //This function will create a new name for every image captured using the current data and time.
    private function getNameWithPath($acolhido){
        // $name = $this->imageFolder.date('YmdHis').".jpg";
        $name = date('YmdHis').".jpg";
        return $name;
    }
    
    //function will get the image data and save it to the provided path with the name and save it to the database
    public function showImage($acolhido){
        $file = file_put_contents( $this->imageFolder . $this->getNameWithPath($acolhido), file_get_contents('php://input') );
        if(!$file){
            return "ERROR: Failed to write data to ".$this->getNameWithPath($acolhido).", check permissions\n";
        }
        else
        {
            $this->saveImageToDatabase($acolhido, $this->getNameWithPath($acolhido));         // this line is for saveing image to database
            // return $this->insert_id;
            echo $this->insert_id . '-' . $this->getNameWithPath($acolhido);
        }
        
    }
    
    //function for changing the image to base64
    public function changeImagetoBase64($image){
        $path = $image;
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }
    
    public function saveImageToDatabase($acolhido, $imageurl){
        $image=$imageurl;
        $id_acolhido=$acolhido;
        // $image=  $this->changeImagetoBase64($image);          //if you want to go for base64 encode than enable this line
        if($image){
            $query="INSERT INTO snapshots (acolhido, imagem) VALUES ('$id_acolhido', '$image')";
            $result= $this->query($query);
            if($result){
                return "Image saved to database";
            }
            else{
                return "Image not saved to database";
            }
        }
    }
    
    
}