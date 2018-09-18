<?php
require_once("Point.php");
class QuadNode{
    private $locationCode;
    private $bottomLeft;
    private $topRight;
    private $width;
    private $height;
    private $type;
    private $parent;
    private $children;
    private $cardinalNeibors;
    private static $resolution = 0.001;

    public function __construct($obstacles, $locationCode, $bottomLeft, $topRight, $parent = null, $neibors = null ){
        $this->locationCode = $locationCode;
        $this->bottomLeft = $bottomLeft;
        $this->topRight = $topRight;
        $this->width = $this->topRight->getLong() - $this->bottomLeft->getLong();
        $this->height = $this->topRight->getLat() - $this->bottomLeft->getLat();
        $this->type = "MIXED";
        $this->parent = $parent;
        $this->children = array("NW"=>null,"NE"=>null,"SW"=>null,"SE"=>null);
        $this->cardinalNeibors = array();

        if ($neibors == null /*|| get_class($neibors) == "stdClass" it case warning for some cases*/){
            $this->cardinalNeibors = array_fill(0, 4, null);
        }
        else{
            for ($i = 0; $i < 4; $i++){
                $this->cardinalNeibors[$i] = $neibors[$i];
            }
        }

        if($parent == null || get_class($parent) == "stdClass")
            $this->checkAndDivide($obstacles);
        
    }

    //This method will be used to check if the corrent node has on obstacles or not. If a node has an obstacle it will call subdivide to divided. 
    // The parameter for this method is the array of obstacles
    private function checkAndDivide($obstacles){
        echo "node: ".$this->getLocationCode()."<br/>";
        $hasObstacles = false; //we may delete it because we use return
        foreach($obstacles as $obstacle){
            $obstacleBottomLeft = Point::getCoordinates($obstacle["bottomLeft"]);
            $obstacleTopRight = Point::getCoordinates($obstacle["topRight"]);
            $obstacleHeight = $obstacle["height"];

            if(Point::intersect($obstacleBottomLeft, $obstacleTopRight,$this->bottomLeft, $this->topRight)){
                $hasObstacles = true;
                //echo "node: ".$this->getLocationCode()."<br/>";
                if(Point::obstruct($obstacleBottomLeft, $obstacleTopRight,$this->bottomLeft, $this->topRight) || $this->width() <= QuadNode::$resolution || $this->height() <= QuadNode::$resolution){
                    $this->type = "OBSTACLE";
                }
                else{
                    $this->subdivide($obstacles);
                }
                return;
            }
        }
        if(!$hasObstacles || sizeof($obstacles) < 1){
            $this->type = "FREE";
        }
    }


    private function subdivide($obstacles){
        $long0 = $this->bottomLeft->getLong();                        //  y2 .----.-------.
        $long1 = $this->bottomLeft->getLong() + $this->width() / 2;   //     |    |       |
        $long2 = $this->topRight->getLong();                          //     | NW |  NE   |
                                                                      //     |    |       |
        $lat0 = $this->bottomLeft->getLat();                          //  y1 '----'-------'
        $lat1 = $this->bottomLeft->getLat() + $this->height() / 2;    //     | SW |  SE   |
        $lat2 = $this->topRight->getLat();                            //  y0 '----'-------'
                                                                      //     x0   x1     x2

        //create the children of the node, the children neighbors will be the same as their parent
        $this->children['NW'] = new QuadNode($obstacles, $this->locationCode."00", new Point($long0, $lat1), new Point($long1, $lat2), $this, $this->cardinalNeibors);
        $this->children['NE'] = new QuadNode($obstacles, $this->locationCode."01", new Point($long1, $lat1), new Point($long2, $lat2), $this, $this->cardinalNeibors);
        $this->children['SW'] = new QuadNode($obstacles, $this->locationCode."10", new Point($long0, $lat0), new Point($long1, $lat1), $this, $this->cardinalNeibors);
        $this->children['SE'] = new QuadNode($obstacles, $this->locationCode."11", new Point($long1, $lat0), new Point($long2, $lat1), $this, $this->cardinalNeibors);

        //update the North West child' neighbor
        $this->children['NW']->cardinalNeibors[2] = $this->children['NE'];
        $this->children['NW']->cardinalNeibors[3] = $this->children['SW'];
        

        //update the North East child' neighbor
        //echo (get_class($this->cardinalNeibors[1]) != "stdClass")."<br/>";
        $this->children['NE']->cardinalNeibors[0] = $this->children['NW'];
        $this->children['NE']->cardinalNeibors[3] = $this->children['SE'];
        //echo get_class($this->cardinalNeibors[1])."<br/>";
        if($this->cardinalNeibors[1] != null && get_class($this->cardinalNeibors[1]) != "stdClass" && $this->cardinalNeibors[1]->width < $this->width)
            $this->children['NE']->updateNorthNeighbor(); 
        else if($this->cardinalNeibors[1] != null && get_class($this->cardinalNeibors[1]) != "stdClass")
            $this->children['NE']->changeNorthNeighbor(); 

        if($this->cardinalNeibors[2] != null && get_class($this->cardinalNeibors[2]) != "stdClass" && $this->cardinalNeibors[2]->height() < $this->height())
            $this->children['NE']->updateEastNeighbor(); 
        else if($this->cardinalNeibors[2] != null && get_class($this->cardinalNeibors[2]) != "stdClass")
            $this->children['NE']->changeEastNeighbor(); 
        
        
        //update the South West child' neighbor
        $this->children['SW']->cardinalNeibors[1] = $this->children['NW'];
        $this->children['SW']->cardinalNeibors[2] = $this->children['SE'];
        if($this->cardinalNeibors[3] != null && get_class($this->cardinalNeibors[3]) != "stdClass" && $this->cardinalNeibors[3]->width() < $this->width())
            $this->children['SW']->updateSouthNeighbor(); 
        else if($this->cardinalNeibors[3] != null && get_class($this->cardinalNeibors[3]) != "stdClass")
            $this->children['SW']->changeSouthNeighbor();  

        if($this->cardinalNeibors[0] != null && get_class($this->cardinalNeibors[0]) != "stdClass" && $this->cardinalNeibors[0]->height() < $this->height())
            $this->children['SW']->updateWestNeighbor();
        else if($this->cardinalNeibors[0] != null && get_class($this->cardinalNeibors[0]) != "stdClass")
            $this->children['SW']->changeWestNeighbor();
        

        //update the South West child' neighbor
        $this->children['SE']->cardinalNeibors[0] = $this->children['SW'];
        $this->children['SE']->cardinalNeibors[1] = $this->children['NE'];
        
        
        //check for obstacles and divide
        $this->children['NW']->checkAndDivide($obstacles);
        $this->children['NE']->checkAndDivide($obstacles);
        $this->children['SW']->checkAndDivide($obstacles);
        $this->children['SE']->checkAndDivide($obstacles);
    }

    private function changeNorthNeighbor(){
        $this->cardinalNeibors[1]->cardinalNeibors[3] = $this;

    }

    private function updateNorthNeighbor(){
        $current = $this->cardinalNeibors[1];
        $width = 0;
        while (/*$width != $this->width()*/ $current != null && get_class($current) != "stdClass" && $current->cardinalNeibors[3] != null && $current->cardinalNeibors[3]->equals($this->parent)){
            if($width != $this->width()){
                $width += $this->cardinalNeibors[1]->width();
                $this->cardinalNeibors[1] = $current->getEastNeighbor();
                $current->cardinalNeibors[3] = $this->cardinalNeibors[0];
            }
            else{
                $current->cardinalNeibors[3] = $this;
            }
            $current =  $current->getEastNeighbor();
        }
    }

    private function changeEastNeighbor(){
        $this->cardinalNeibors[2]->cardinalNeibors[0] = $this;
    }

    private function updateEastNeighbor(){
        $current = $this->cardinalNeibors[2];
        $height = 0;
        while (/*$height != $this->height()*/ $current != null && get_class($current) != "stdClass" && $current->cardinalNeibors[0] != null && $current->cardinalNeibors[0]->equals($this->parent)){
            if($height != $this->height()){
                $height += $this->cardinalNeibors[2]->height();
                $this->cardinalNeibors[2] = $current->getNorthNeighbor();
                $current->cardinalNeibors[0] = $this->cardinalNeibors[3];
            }
            else{
                $current->cardinalNeibors[0] = $this;
            }
            $current = $current->getNorthNeighbor();
        }
    }

    private function changeSouthNeighbor(){
        $this->cardinalNeibors[3]->cardinalNeibors[1] = $this;
        //echo get_class($this)."<br/>";
        //echo get_class($this->cardinalNeibors[1])."<br/>";
    }

    private function updateSouthNeighbor(){
        $current = $this->cardinalNeibors[3];
        $width = 0;
        while (/*$width != $this->width()*/ $current != null && get_class($current) != "stdClass" && $current->cardinalNeibors[1] != null &&$current->cardinalNeibors[1]->equals($this->parent)){
            if($width != $this->width()){
                $width += $this->cardinalNeibors[3]->width();
                $this->cardinalNeibors[3] = $current->getWestNeighbor();
                $current->cardinalNeibors[1] = $this->cardinalNeibors[2];
            }
            else{
                $current->cardinalNeibors[1] = $this;
            }
            $current = $current->getWestNeighbor();
        }
    }

    private function changeWestNeighbor(){
        $this->cardinalNeibors[0]->cardinalNeibors[2] = $this;
    }

    private function updateWestNeighbor(){
        $current = $this->cardinalNeibors[0];
        $height = 0;
        while (/*$height != $this->height()*/$current != null && get_class($current) != "stdClass" && $current->cardinalNeibors[2] && $current->cardinalNeibors[2]->equals($this->parent)){
            if($height != $this->height()){
                $height += $this->cardinalNeibors[0]->height();
                $this->cardinalNeibors[0] = $current->getSouthNeighbor();
                $current->cardinalNeibors[2] = $this->cardinalNeibors[1];
            }
            else{
                $current->cardinalNeibors[2] = $this;
            }
            $current = $current->getSouthNeighbor();
        }
    }

    public static function printTree($node){
        echo "<b>Node: ".$node->locationCode."</b><br/>";
        echo "Bottom Left Point: ".$node->bottomLeft->getLong()."|".$node->bottomLeft->getLat()."<br/>";
        echo "Top Right Point: ".$node->topRight->getLong()."|".$node->topRight->getLat()."<br/>";
        echo "Type: ".$node->type."<br/>";
        echo "Parent: ";
        if($node->parent == null || get_class($node->parent) == "stdClass")
            echo "null";
        else
            echo $node->parent->getLocationCode();
        echo "<br/>";
        echo "neighbors: ";
        for($i = 0; $i < 4; $i++){
            $tmp = $node->cardinalNeibors[$i];
            if($tmp == null || get_class($tmp) == "stdClass")
                echo "null, ";
            else
                echo $tmp->getLocationCode().", ";
        }
        echo "<br/>";
        echo "**************************************************<br/>";

        foreach($node->children as $value){
            if($value != null && get_class($value) != "stdClass"){
                QuadNode::printTree($value);
            }
        }
    }

    public function equals($node){
        return $this->locationCode == $node->locationCode;
    }

    public function adjacent($node){
        for($i = 0 ; $i < 4; $i++){
            if($this->cardinalNeibors[$i]->equals($node))
                return $i;
        }
        return null;
    }

    public function isFree(){
        return $this->type == "FREE";
    }

    public function isLeaf(){
        return $this->type != "MIXED";
    }

    public function inBound($point){
        return ($this->bottomLeft->getLong() <= $point->getLong() && $point->getLong() <= $this->topRight->getLong())
                && ($this->bottomLeft->getLat() <= $point->getLat() && $point->getLat() <= $this->topRight->getLat());
    }

    public function width(){
        return $this->topRight->getLong() - $this->bottomLeft->getLong();
    }

    public function height(){
        return $this->topRight->getLat() - $this->bottomLeft->getLat();
    }

    public function middlePoint(){
        $middleLong = ($this->bottomLeft->getLong() + $this->topRight->getLong()) / 2;
        $middleLat = ($this->bottomLeft->getLat() + $this->topRight->getLat()) / 2;

        return new Point($middleLong, $middleLat);
    }

    public function getLocationCode(){
        return $this->locationCode;
    }

    public function getType(){
        return $this->type;
    }

    public function getParent(){
        return $this->parent;
    }

    public function getNorthWestChildren(){
        return $this->children["NW"];
    }

    public function getNorthEastChildren(){
        return $this->children["NE"];
    }

    public function getSouthWestChildren(){
        return $this->children["SW"];
    }
    public function getSouthEastChildren(){
        return $this->children["SE"];
    }

    public function getWestNeighbor(){
        return $this->cardinalNeibors[0];
    }

    public function getNorthNeighbor(){
        return $this->cardinalNeibors[1];
    }

    public function getEastNeighbor(){
        return $this->cardinalNeibors[2];
    }

    public function getSouthNeighbor(){
        return $this->cardinalNeibors[3];
    }

    public function getNeighbor($i,$j){
        $current = null;
        switch($j){
            case 0:
                $current = $this->getWestNeighbor();
                break;
            case 1:
                $current = $this->getNorthNeighbor();
                break;
            case 2:
                $current = $this->getEastNeighbor();
                break;
            case 3:
                $current = $this->getSouthNeighbor();
                break;
            default:
                return null;
        }
        switch($i){
            case 0:
                $current = $current->getWestNeighbor();
                break;
            case 1:
                $current = $current->getNorthNeighbor();
                break;
            case 2:
                $current = $current->getEastNeighbor();
                break;
            case 3:
                $current = $current->getSouthNeighbor();
                break;
            default:
               return $current;
        }
        return $current;
    }

    public function getBottomLeft(){
        return $this->bottomLeft;
    }

    public function getTopRight(){
        return $this->topRight;
    }

}
?>