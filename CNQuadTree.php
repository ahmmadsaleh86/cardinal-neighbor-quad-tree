<?php
    require_once("QuadNode.php");
    require_once("Point.php");
    class CNQuadTree{
        public $root;

        public function __construct($obstacles, $bottomLeft, $topRight, $resolution){
            /*if($size < $resolution*2 || $resolution < 1){
                echo "error";
                die();
            }
            else{    
                $this->root = new QuadNode($obstacles, "#",$bottomLeft, $topRight);
            }*/
            $this->root = new QuadNode($obstacles, "#",$bottomLeft, $topRight);
        }

        public function printTree(){
            QuadNode::printTree($this->root);
        }

        public function findQuad($point){
            if(!$this->root->inBound($point)){
                return null;
            }
            $node = $this->root;
            while(! $node->isLeaf()){
                $xMean = $node->getBottomLeft()->getLong() + $node->width()/2; 
                $yMean = $node->getBottomLeft()->getLat() + $node->height()/2;
                
                if($point->getLong() < $xMean){
                    if($point->getLat() < $yMean){
                        $node = $node->getSouthWestChildren();
                    }else{
                        $node = $node->getNorthWestChildren();
                    }
                }else{
                    if($point->getLat() < $yMean){
                        $node = $node->getSouthEastChildren();
                    }else{
                        $node = $node->getNorthEastChildren();
                    }
                }
            }
            return $node;
        }

        public function findQuadNode($locationCode){
            $locationCode = substr($locationCode, 1);
            if(strlen($locationCode)%2 == 1)
                return null;
            $current = $this->root;
            while($current != null && strlen($locationCode) != 0){
                $child = substr($locationCode,0,2);
                $locationCode = substr($locationCode, 2);
                switch($child){
                    case "00":
                    $current = $current->getNorthWestChildren();
                    break;
                    case "01":
                    $current = $current->getNorthEastChildren();
                    break;
                    case "10":
                    $current = $current->getSouthWestChildren();
                    break;
                    case "11":
                    $current = $current->getSouthEastChildren();
                    break;
                }
            }
            return $current;
        }
    }

    $obstacles = array(
        array("bottomLeft"=>"8|0", "topRight"=>"16|8", "height"=>"3.0000"),
        array("bottomLeft"=>"16|16", "topRight"=>"32|32", "height"=>"3.0000"),
        array("bottomLeft"=>"32|40", "topRight"=>"40|48", "height"=>"3.0000"),
        array("bottomLeft"=>"48|56", "topRight"=>"56|64", "height"=>"3.0000")
    );

    $newTree = new CNQuadTree($obstacles, new Point(0, 0), new Point(64,64) , 2);
    $newTree->printTree();
    $point = new Point(5,10);
    //echo $newTree->findQuad($point)->getLocationCode()."<br/>";
    //echo $newTree->findQuadNode("#101010")->getLocationCode();
    $point1 = new Point(46.6416346, 24.7233694);
    $point2 = new Point(46.6206081, 24.7241111);

    //echo "The distance: ". $point1->distance($point2);*/

?>